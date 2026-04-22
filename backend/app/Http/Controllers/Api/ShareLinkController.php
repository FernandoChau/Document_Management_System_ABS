<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShareLink;
use App\Models\Document;
use App\Models\Folder;
use App\Services\AuditService;
use App\Services\AuditLogger;
use App\Services\FolderZipService;
use App\Services\PermissionValidator;
use App\Exceptions\PermissionDeniedException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ShareLinkController extends Controller
{
    protected $folderZipService;
    protected $permissionValidator;
    protected $auditService;

    public function __construct(
        FolderZipService $folderZipService,
        PermissionValidator $permissionValidator,
        AuditService $auditService
    ) {
        $this->folderZipService = $folderZipService;
        $this->permissionValidator = $permissionValidator;
        $this->auditService = $auditService;
    }
    /**
     * Listar links de compartilhamento do usuário
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);

        $links = ShareLink::where('created_by', auth()->id())
            ->with('resource')
            ->paginate($perPage);

        return response()->json($links);
    }

    /**
     * Criar novo link de compartilhamento
     * FIRST STEP: Validate permissions before creating share link
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'shareable_type' => 'required|in:Document,Folder',
            'shareable_id' => 'required|uuid',
            'expires_at' => 'nullable|date|after:now',
            'password' => 'nullable|string|min:6|max:255',
        ]);

        // Get resource
        $resourceClass = "App\\Models\\" . $request->shareable_type;
        $resource = $resourceClass::findOrFail($request->shareable_id);

        // FIRST STEP: Check permission to share
        try {
            if ($request->shareable_type === 'Folder') {
                $this->permissionValidator->validateFolderAction($user, $resource, 'share');
            } else {
                $this->permissionValidator->validateDocumentAction($user, $resource, 'share');
            }
        } catch (PermissionDeniedException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }

        // Create share link
        $shareLink = ShareLink::create([
            'token' => Str::random(60),
            'shareable_type' => $request->shareable_type,
            'shareable_id' => $request->shareable_id,
            'expires_at' => $request->expires_at,
            'password' => $request->password ? hash('sha256', $request->password) : null,
            'created_by' => $user->id,
        ]);

        // Use AuditService for logging
        $this->auditService->logShare($user, $resource, [
            'share_link_id' => $shareLink->id,
            'expires_at' => $request->expires_at,
        ]);

        return response()->json([
            'token' => $shareLink->token,
            'url' => url("/api/compartilhamentos/{$shareLink->token}"),
            'expires_at' => $shareLink->expires_at,
        ], 201);
    }

    /**
     * Visualizar link de compartilhamento (público)
     */
    public function show($token, Request $request)
    {
        $shareLink = ShareLink::where('token', $token)->firstOrFail();

        // Verificar expiração
        if ($shareLink->expires_at && $shareLink->expires_at->isPast()) {
            return response()->json(['message' => 'Share link expired'], 410);
        }

        // Verificar limite de downloads
        if ($shareLink->max_downloads && $shareLink->downloads_count >= $shareLink->max_downloads) {
            return response()->json(['message' => 'Download limit exceeded'], 429);
        }

        // Verificar password
        if ($shareLink->password && !$request->filled('password')) {
            return response()->json(['message' => 'Password required'], 403);
        }

        if ($shareLink->password && !password_verify($request->password, $shareLink->password)) {
            return response()->json(['message' => 'Invalid password'], 403);
        }

        $resource = $shareLink->resource;

        return response()->json([
            'id' => $shareLink->id,
            'type' => $shareLink->shareable_type,
            'resource' => $resource,
            'created_at' => $shareLink->created_at,
            'expires_at' => $shareLink->expires_at,
        ]);
    }

    /**
     * Download via link de compartilhamento (público)
     */
    public function download($token, Request $request)
    {
        $shareLink = ShareLink::where('token', $token)->firstOrFail();

        // Verificar expiração
        if ($shareLink->expires_at && $shareLink->expires_at->isPast()) {
            return response()->json(['message' => 'Share link expired'], 410);
        }

        // Verificar limite de downloads
        if ($shareLink->max_downloads && $shareLink->downloads_count >= $shareLink->max_downloads) {
            return response()->json(['message' => 'Download limit exceeded'], 429);
        }

        // Verificar password
        if ($shareLink->password && !$request->filled('password')) {
            return response()->json(['message' => 'Password required'], 403);
        }

        if ($shareLink->password && !password_verify($request->password, $shareLink->password)) {
            return response()->json(['message' => 'Invalid password'], 403);
        }

        // Download de documento
        if ($shareLink->shareable_type === 'Document') {
            $document = Document::findOrFail($shareLink->shareable_id);

            // Verificar se o arquivo existe
            if (!file_exists(storage_path('app/private/' . $document->file_path))) {
                return response()->json(['error' => 'File not found'], 404);
            }

            // Incrementar contador de downloads
            $shareLink->increment('downloads_count');

            // Use AuditService for logging (null user for public download)
            $this->auditService->logDownload(null, $document, [
                'via_share_link' => $shareLink->id,
                'ip' => $request->ip(),
            ]);

            return response()->download(
                storage_path('app/private/' . $document->file_path),
                $document->name,
                ['Content-Type' => $document->mime_type]
            );
        }

        // Download de pasta como ZIP
        if ($shareLink->shareable_type === 'Folder') {
            try {
                $folder = Folder::findOrFail($shareLink->shareable_id);

                // Criar arquivo ZIP
                $zipPath = $this->folderZipService->createZip($folder);
                $zipFileName = $this->folderZipService->getZipDownloadName($folder);

                // Verificar se arquivo foi criado
                if (!file_exists($zipPath)) {
                    return response()->json(['error' => 'Arquivo ZIP não foi criado'], 500);
                }

                // Incrementar contador de downloads
                $shareLink->increment('downloads_count');

                // Use AuditService for logging (null user for public download)
                $this->auditService->logDownload(null, $folder, [
                    'via_share_link' => $shareLink->id,
                    'ip' => $request->ip(),
                    'type' => 'zip',
                ]);

                // Retornar o arquivo para download
                return response()->download($zipPath, $zipFileName, [
                    'Content-Type' => 'application/zip',
                    'Content-Disposition' => 'attachment; filename="' . $zipFileName . '"',
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Não foi possível criar o arquivo ZIP: ' . $e->getMessage()], 500);
            }
        }

        return response()->json(['message' => 'Invalid share link type'], 400);
    }

    /**
     * Atualizar link de compartilhamento
     */
    public function update(Request $request, ShareLink $shareLink)
    {
        // Verificar se é o criador
        if ($shareLink->created_by !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'expires_in_hours' => 'nullable|integer|min:1|max:720',
            'max_downloads' => 'nullable|integer|min:1',
        ]);

        if ($request->filled('expires_in_hours')) {
            $shareLink->expires_at = Carbon::now()->addHours($request->expires_in_hours);
        }

        if ($request->filled('max_downloads')) {
            $shareLink->max_downloads = $request->max_downloads;
        }

        $shareLink->save();
        $this->auditService->logUpdateMetadata(auth()->user(), $shareLink, [], $shareLink->toArray());

        return response()->json($shareLink);
    }

    /**
     * Deletar link de compartilhamento
     */
    public function destroy(ShareLink $shareLink)
    {
        // Verificar se é o criador
        if ($shareLink->created_by !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $shareLink->delete();
        $this->auditService->logSoftDelete(auth()->user(), $shareLink);

        return response()->noContent();
    }
}
