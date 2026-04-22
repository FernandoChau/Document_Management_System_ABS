<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\Department;
use App\Models\FolderResponsible;
use App\Models\AuditLog;
use App\Models\ShareLink;
use App\Services\FolderService;
use App\Services\FolderZipService;
use App\Services\FileOperationService;
use App\Services\AuditService;
use App\Services\AuditLogger;
use App\Services\AuthorizationService;
use App\Services\PermissionValidator;
use App\Services\StructureValidator;
use App\Services\FolderValidator;
use App\Exceptions\PermissionDeniedException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FolderController extends Controller
{
    protected $folderService;
    protected $folderZipService;
    protected $fileOperationService;
    protected $auditService;
    protected $authorizationService;
    protected $permissionValidator;
    protected $structureValidator;
    protected $folderValidator;

    public function __construct(
        FolderService $folderService,
        FolderZipService $folderZipService,
        FileOperationService $fileOperationService,
        AuditService $auditService,
        AuthorizationService $authorizationService,
        PermissionValidator $permissionValidator,
        StructureValidator $structureValidator,
        FolderValidator $folderValidator
    ) {
        $this->folderService = $folderService;
        $this->folderZipService = $folderZipService;
        $this->fileOperationService = $fileOperationService;
        $this->auditService = $auditService;
        $this->authorizationService = $authorizationService;
        $this->permissionValidator = $permissionValidator;
        $this->structureValidator = $structureValidator;
        $this->folderValidator = $folderValidator;
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
            'parent_id' => 'nullable|uuid|exists:folders,id',
            'department_id' => 'nullable|uuid|exists:departments,id',
        ]);

        // Validate structure before proceeding
        try {
            $this->structureValidator->validateFolderData([
                'name' => $request->name,
                'parent_id' => $request->parent_id,
                'is_root' => is_null($request->parent_id),
            ]);
            
            // Additional validation with FolderValidator for edge cases
            $this->folderValidator->validateFolderData([
                'name' => $request->name,
                'parent_id' => $request->parent_id,
                'is_root' => is_null($request->parent_id),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $parent = $request->parent_id ? Folder::findOrFail($request->parent_id) : null;
        $department = $request->department_id ? Department::findOrFail($request->department_id) : null;

        // Guard: Check parent folder is not deleted
        if ($parent && $parent->trashed()) {
            return response()->json(['error' => 'Parent folder has been deleted'], 422);
        }

        // Guard: Check parent folder ancestor chain integrity
        if ($parent) {
            try {
                $this->folderValidator->validateAncestorChainIntegrity($parent);
            } catch (\InvalidArgumentException $e) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
        }

        // Se for raiz (sem parent) apenas admin pode criar
        if (!$parent && !$user->isAdmin()) {
            return response()->json(['error' => 'Only admin can create root folders'], 403);
        }

        // Check upload permission se tiver parent
        if ($parent) {
            try {
                $this->permissionValidator->validateFolderAction($user, $parent, 'create');
            } catch (PermissionDeniedException $e) {
                return response()->json(['error' => $e->getMessage()], 403);
            }
        }

        $folder = $this->folderService->createFolder(
            $request->name,
            $user,
            $parent,
            $department
        );

        // Create folder responsible for creator (owner)
        FolderResponsible::create([
            'folder_id' => $folder->id,
            'user_id' => $user->id,
            'is_owner' => true,
        ]);

        // Create permissions for creator (all permissions by default)
        $folder->permissions()->create([
            'user_id' => $user->id,
            'can_view' => true,
            'can_update_metadata' => true,
            'can_delete' => true,
            'can_upload' => true,
            'can_share' => true,
            'can_download' => true,
            'can_manage_permissions' => true,
        ]);

        AuditLogger::log($user, 'CREATE', $folder);

        return response()->json($folder, 201);
    }

    public function download(Folder $folder)
    {
        $user = auth()->user();

        try {
            return $this->fileOperationService->downloadFolder($user, $folder);
        } catch (PermissionDeniedException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Não foi possível criar o arquivo ZIP: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Share folder via ShareLink
     */
    public function share(Request $request, Folder $folder)
    {
        $user = $request->user();

        $request->validate([
            'expires_at' => 'nullable|date|after:now',
            'password' => 'nullable|string|min:6|max:255',
        ]);

        // FIRST STEP: Validate permissions before creating share link
        try {
            $this->permissionValidator->validateFolderAction($user, $folder, 'share');
        } catch (PermissionDeniedException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }

        // Create share link
        $shareLink = ShareLink::create([
            'token' => Str::random(60),
            'shareable_type' => 'Folder',
            'shareable_id' => $folder->id,
            'expires_at' => $request->expires_at,
            'password' => $request->password ? hash('sha256', $request->password) : null,
            'created_by' => $user->id,
        ]);

        // Use AuditService for logging
        $this->auditService->logShare($user, $folder, [
            'share_link_id' => $shareLink->id,
            'token' => $shareLink->token,
        ]);

        return response()->json([
            'token' => $shareLink->token,
            'url' => url("/api/compartilhamentos/{$shareLink->token}"),
            'expires_at' => $shareLink->expires_at,
        ], 201);
    }

    /**
     * Get audit logs for folder
     */
    public function logs(Request $request, Folder $folder)
    {
        $user = $request->user();

        // Validate: user must have can_view
        try {
            $this->permissionValidator->validateFolderAction($user, $folder, 'view');
        } catch (PermissionDeniedException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }

        $perPage = $request->input('per_page', 15);
        
        $logs = AuditLog::where('resource_type', 'Folder')
            ->where('resource_id', $folder->id)
            ->with('user')
            ->latest('created_at')
            ->paginate($perPage);

        return response()->json($logs);
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

        $request->validate([
            'name' => 'sometimes|string|max:255',
        ]);

        // Validate: user must have can_view + can_update_metadata
        try {
            $this->permissionValidator->validateFolderAction($user, $folder, 'update');
        } catch (PermissionDeniedException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }

        $folder->update($request->only('name'));
        AuditLogger::log($user, 'UPDATE_METADATA', $folder);

        return response()->json($folder);
    }

    public function destroy(Folder $folder)
    {
        $user = auth()->user();

        try {
            $this->fileOperationService->deleteFolderTree($user, $folder);
            return response()->noContent();
        } catch (PermissionDeniedException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Permanently delete folder (only admin, only if in trash)
     */
    public function forceDelete($id)
    {
        $user = auth()->user();

        try {
            $folder = Folder::withTrashed()->findOrFail($id);
            $this->fileOperationService->permanentlyDeleteFolder($user, $folder);
            return response()->noContent();
        } catch (PermissionDeniedException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function restore($id)
    {
        $user = auth()->user();

        try {
            $folder = Folder::withTrashed()->findOrFail($id);
            $this->fileOperationService->restoreFolder($user, $folder);
            return response()->json($folder);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
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
