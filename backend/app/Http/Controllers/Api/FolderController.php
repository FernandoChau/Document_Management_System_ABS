<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\Department;
use App\Models\FolderResponsible;
use App\Services\FolderService;
use App\Services\FolderZipService;
use App\Services\AuditLogger;
use App\Services\AuthorizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FolderController extends Controller
{
    protected $folderService;
    protected $folderZipService;
    protected $authorizationService;

    public function __construct(FolderService $folderService, FolderZipService $folderZipService, AuthorizationService $authorizationService)
    {
        $this->folderService = $folderService;
        $this->folderZipService = $folderZipService;
        $this->authorizationService = $authorizationService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $parentId = $request->query('parent_id');

        if ($parentId) {
            $folder = Folder::with('children', 'documents')->findOrFail($parentId);

            // Check permission
            if (!$this->authorizationService->canViewFolder($user, $folder)) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            AuditLogger::log($user, 'VIEW', $folder);
            return response()->json($folder);
        }

        // Get roots user has access to
        $roots = $this->authorizationService->getViewableFolders($user)
            ->where('is_root', true)
            ->values();

        return response()->json(['data' => $roots]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'parent_id' => 'nullable|exists:folders,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $parent = $request->parent_id ? Folder::findOrFail($request->parent_id) : null;
        $department = $request->department_id ? Department::findOrFail($request->department_id) : null;

        // Check upload permission se tiver parent
        if ($parent && !$this->authorizationService->canUploadToFolder($user, $parent)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Se for raiz (sem parent) apenas admin pode criar
        if (!$parent && !$user->isAdmin()) {
            return response()->json(['error' => 'Only admin can create root folders'], 403);
        }

        $folder = $this->folderService->createFolder(
            $request->name,
            $user,
            $parent,
            $department
        );

        // Create folder responsible for creator
        FolderResponsible::create([
            'folder_id' => $folder->id,
            'user_id' => $user->id,
            'is_owner' => true,
        ]);

        // Create permissions for creator
        $folder->permissions()->create([
            'user_id' => $user->id,
            'can_view' => true,
            'can_update_metadata' => true,
            'can_delete' => true,
            'can_upload' => true,
            'can_share' => true,
            'can_download' => true,
        ]);

        AuditLogger::log($user, 'CREATE', $folder);

        return response()->json($folder, 201);
    }

    public function download(Folder $folder)
    {
        $user = auth()->user();

        if (!$this->authorizationService->canViewFolder($user, $folder)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        try {
            // Criar arquivo ZIP
            $zipPath = $this->folderZipService->createZip($folder);
            $zipFileName = $this->folderZipService->getZipDownloadName($folder);

            // Verificar se arquivo foi criado
            if (!file_exists($zipPath)) {
                return response()->json(['error' => 'Arquivo ZIP não foi criado'], 500);
            }

            AuditLogger::log($user, 'DOWNLOAD', $folder, ['type' => 'zip']);

            // Retornar o arquivo para download
            $response = response()->download($zipPath, $zipFileName, [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename="' . $zipFileName . '"',
            ]);

            // Registrar para limpeza posterior (não deletar imediatamente)
            return $response;
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível criar o arquivo ZIP: ' . $e->getMessage()], 500);
        }
    }

    public function show(Folder $folder)
    {
        $user = auth()->user();

        if (!$this->authorizationService->canViewFolder($user, $folder)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $folder->load(['children', 'documents', 'parent']);
        AuditLogger::log($user, 'VIEW', $folder);
        return response()->json($folder);
    }

    public function update(Request $request, Folder $folder)
    {
        $user = $request->user();

        if (!$this->authorizationService->canManageFolderPermissions($user, $folder)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
        ]);

        $folder->update($request->only('name'));
        AuditLogger::log($user, 'UPDATE_METADATA', $folder);

        return response()->json($folder);
    }

    public function destroy(Folder $folder)
    {
        $user = auth()->user();

        if (!$this->authorizationService->canDeleteFolder($user, $folder)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $folder->delete();
        AuditLogger::log($user, 'SOFT_DELETE', $folder);
        return response()->noContent();
    }

    public function restore($id)
    {
        $user = auth()->user();

        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $folder = Folder::withTrashed()->findOrFail($id);
        $folder->restore();
        AuditLogger::log($user, 'RESTORE', $folder);
        return response()->json($folder);
    }

    public function stats(Folder $folder)
    {
        $user = auth()->user();

        if (!$this->authorizationService->canViewFolder($user, $folder)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $stats = [
            'total_documents' => $folder->documents()->count(),
            'total_subfolders' => $folder->children()->count(),
            'total_size' => $folder->documents()->sum('size'),
            'recent_uploads' => $folder->documents()
                ->latest('created_at')
                ->limit(5)
                ->get(['id', 'name', 'created_at', 'user_id']),
        ];

        return response()->json($stats);
    }
}
