<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\FolderPermission;
use App\Models\User;
use App\Models\Group;
use App\Services\AuditLogger;
use App\Services\AuthorizationService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class FolderPermissionController extends Controller
{
    protected $authorizationService;

    public function __construct(AuthorizationService $authorizationService)
    {
        $this->authorizationService = $authorizationService;
    }

    /**
     * Listar permissões de uma pasta
     */
    public function index(Folder $folder)
    {
        $user = auth()->user();

        // Only folder responsibles and admin can view permissions
        if (!$this->authorizationService->canManageFolderPermissions($user, $folder)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $permissions = FolderPermission::where('folder_id', $folder->id)
            ->with('user', 'group')
            ->get();

        return response()->json($permissions);
    }

    /**
     * Conceder permissão a um usuário ou grupo
     */
    public function store(Request $request, Folder $folder)
    {
        $user = auth()->user();

        // Check if user can manage permissions
        if (!$this->authorizationService->canManageFolderPermissions($user, $folder)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $request->validate([
            'user_id' => 'nullable|uuid|exists:users,id',
            'group_id' => 'nullable|uuid|exists:groups,id',
            'can_view' => 'required|boolean',
            'can_update_metadata' => 'required|boolean',
            'can_delete' => 'required|boolean',
            'can_upload' => 'required|boolean',
            'can_share' => 'required|boolean',
            'can_download' => 'required|boolean',
            'can_manage_permissions' => 'nullable|boolean',
        ]);

        // XOR constraint: either user_id OR group_id, but not both
        if (($request->user_id && $request->group_id) || (!$request->user_id && !$request->group_id)) {
            return response()->json(['error' => 'Provide either user_id or group_id, but not both'], 422);
        }

        // Check if already exists
        $existing = FolderPermission::where('folder_id', $folder->id)
            ->where(function ($q) use ($request) {
                if ($request->user_id) {
                    $q->where('user_id', $request->user_id);
                } else {
                    $q->where('group_id', $request->group_id);
                }
            })
            ->first();

        if ($existing) {
            $existing->update($request->only([
                'can_view',
                'can_update_metadata',
                'can_delete',
                'can_upload',
                'can_share',
                'can_download',
                'can_manage_permissions'
            ]));
            AuditLogger::log($user, 'UPDATE_METADATA', $existing);
            return response()->json($existing);
        }

        $permission = FolderPermission::create([
            'folder_id' => $folder->id,
            'user_id' => $request->user_id,
            'group_id' => $request->group_id,
            'can_view' => $request->can_view,
            'can_update_metadata' => $request->can_update_metadata,
            'can_delete' => $request->can_delete,
            'can_upload' => $request->can_upload,
            'can_share' => $request->can_share,
            'can_download' => $request->can_download,
            'can_manage_permissions' => $request->can_manage_permissions ?? false,
        ]);

        AuditLogger::log($user, 'CREATE', $permission, [
            'granted_to_user' => $request->user_id,
            'granted_to_group' => $request->group_id,
            'permissions' => $permission->getEnabledPermissions(),
        ]);

        return response()->json($permission, 201);
    }

    /**
     * Atualizar permissão
     */
    public function update(Request $request, Folder $folder, FolderPermission $permission)
    {
        $user = auth()->user();

        if ($permission->folder_id !== $folder->id) {
            return response()->json(['error' => 'Permission not found'], 404);
        }

        // Check if user can manage permissions
        if (!$this->authorizationService->canManageFolderPermissions($user, $folder)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $request->validate([
            'can_view' => 'sometimes|boolean',
            'can_update_metadata' => 'sometimes|boolean',
            'can_delete' => 'sometimes|boolean',
            'can_upload' => 'sometimes|boolean',
            'can_share' => 'sometimes|boolean',
            'can_download' => 'sometimes|boolean',
            'can_manage_permissions' => 'sometimes|boolean',
        ]);

        $permission->update($request->only([
            'can_view',
            'can_update_metadata',
            'can_delete',
            'can_upload',
            'can_share',
            'can_download',
            'can_manage_permissions'
        ]));

        AuditLogger::log($user, 'UPDATE_METADATA', $permission);

        return response()->json($permission);
    }

    /**
     * Remover permissão
     */
    public function destroy(Folder $folder, FolderPermission $permission)
    {
        $user = auth()->user();

        if ($permission->folder_id !== $folder->id) {
            return response()->json(['error' => 'Permission not found'], 404);
        }

        // Check if user can manage permissions
        if (!$this->authorizationService->canManageFolderPermissions($user, $folder)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $permission->delete();
        AuditLogger::log($user, 'SOFT_DELETE', $permission);

        return response()->noContent();
    }

    /**
     * Get user's effective permissions on a folder
     */
    public function check(Folder $folder, $userId)
    {
        $grantedUser = User::find($userId);
        if (!$grantedUser) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $permissions = $this->authorizationService->resolveFolderPermissions($grantedUser, $folder);

        return response()->json($permissions);
    }

    /**
     * Listar pastas que um usuário tem acesso
     */
    public function userFolders($userId, Request $request)
    {
        $grantedUser = User::find($userId);
        if (!$grantedUser) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $request->validate([
            'permission' => 'nullable|in:can_view,can_upload,can_delete,can_share,can_download,can_update_metadata',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        // Get all accessible folders for this user
        $folders = $this->authorizationService->getViewableFolders($grantedUser);

        $perPage = $request->input('per_page', 15);

        // If specific permission filter requested
        if ($request->filled('permission')) {
            $permission = $request->permission;
            $folders = $folders->filter(function ($folder) use ($grantedUser, $permission) {
                $permissions = $this->authorizationService->resolveFolderPermissions($grantedUser, $folder);
                return $permissions[$permission] ?? false;
            });
        }

        // Paginate manually since we filtered in memory
        $page = max((int) $request->input('page', 1), 1);
        $items = $folders->values();
        $total = $items->count();
        $slice = $items->slice(($page - 1) * $perPage, $perPage)->values();
        $paginated = new LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return response()->json($paginated);
    }
}
