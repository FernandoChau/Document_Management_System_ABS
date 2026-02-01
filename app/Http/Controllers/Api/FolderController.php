<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\Department;
use App\Models\FolderResponsible;
use App\Services\FolderService;
use App\Services\AuditLogger;
use App\Services\AuthorizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FolderController extends Controller
{
    protected $folderService;
    protected $authorizationService;

    public function __construct(FolderService $folderService, AuthorizationService $authorizationService)
    {
        $this->folderService = $folderService;
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
            ->where('is_root', true);

        return response()->json($roots);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id',
            'department_id' => 'required_if:parent_id,null|exists:departments,id',
        ]);

        $parent = $request->parent_id ? Folder::findOrFail($request->parent_id) : null;
        $department = $request->department_id ? Department::findOrFail($request->department_id) : null;

        // Check upload permission
        if ($parent && !$this->authorizationService->canUploadToFolder($user, $parent)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        if ($department && $parent === null && !$user->isAdmin()) {
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

        return response()->json(['message' => 'Zip download not implemented yet in this iteration'], 501);
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
