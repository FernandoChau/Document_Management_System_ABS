<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShareLink;
use App\Models\Document;
use App\Models\Folder;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ShareLinkController extends Controller
{
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
     */
    public function store(Request $request)
    {
        $request->validate([
            'shareable_type' => 'required|in:Document,Folder',
            'shareable_id' => 'required|uuid',
            'expires_in_hours' => 'nullable|integer|min:1|max:720',
            'password' => 'nullable|string|min:6',
            'max_downloads' => 'nullable|integer|min:1',
        ]);

        // Validar que o recurso existe e pertence ao usuário
        $resourceClass = "App\\Models\\" . $request->shareable_type;
        $resource = $resourceClass::findOrFail($request->shareable_id);

        // Verificar se é documento ou pasta
        if ($request->shareable_type === 'Document') {
            // Assumir que o usuário tem permissão (implementar policy depois)
            $this->authorize('view', $resource);
            } elseif ($request->shareable_type === 'Folder') {
            // dd('here');
            // Assumir que o usuário tem permissão
            $this->authorize('view', $resource);
        }

        $expiresAt = null;
        if ($request->expires_in_hours) {
            $expiresAt = Carbon::now()->addHours($request->expires_in_hours);
        }

        $shareLink = ShareLink::create([
            'token' => Str::random(60),
            'shareable_type' => $request->shareable_type,
            'shareable_id' => $request->shareable_id,
            'expires_at' => $expiresAt,
            'password' => $request->password ? bcrypt($request->password) : null,
            'max_downloads' => $request->max_downloads,
            'downloads_count' => 0,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::log(auth()->user(), 'SHARE', $resource, [
            'share_link_id' => $shareLink->id,
            'expires_at' => $expiresAt,
        ]);

        return response()->json($shareLink, 201);
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

        if ($shareLink->shareable_type === 'Document') {
            $document = Document::findOrFail($shareLink->shareable_id);

            // Incrementar contador de downloads
            $shareLink->increment('downloads_count');

            AuditLogger::log(null, 'DOWNLOAD', $document, [
                'via_share_link' => $shareLink->id,
                'ip' => $request->ip(),
            ]);

            return response()->download(storage_path('app/' . $document->file_path), $document->name);
        }

        return response()->json(['message' => 'Folder download not yet implemented'], 501);
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
        AuditLogger::log(auth()->user(), 'UPDATE_METADATA', $shareLink);

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
        AuditLogger::log(auth()->user(), 'SOFT_DELETE', $shareLink);

        return response()->noContent();
    }
}
