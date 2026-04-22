<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\Document;
use App\Models\FolderPermission;
use App\Models\DocumentPermission;
use App\Models\User;
use App\Models\Group;
use App\Services\PermissionValidator;
use App\Services\AuditLogger;
use App\Exceptions\PermissionDeniedException;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    protected $permissionValidator;

    public function __construct(PermissionValidator $permissionValidator)
    {
        $this->permissionValidator = $permissionValidator;
    }

    /**
     * List all permissions for a folder or document
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'resource_type' => 'required|in:folder,document',
            'resource_id' => 'required|uuid',
        ]);

        $resourceType = $request->resource_type;
        $resourceId = $request->resource_id;

        // Get resource
        if ($resourceType === 'folder') {
            $resource = Folder::findOrFail($resourceId);
            
            // Check manage_permissions
            if (!$this->permissionValidator->canManagePermissions($user, $resource)) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            $permissions = $resource->permissions()->with(['user', 'group'])->get();
        } else {
            $resource = Document::findOrFail($resourceId);
            
            // Check manage_permissions
            if (!$this->permissionValidator->canManageDocumentPermissions($user, $resource)) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            $permissions = $resource->permissions()->with(['user', 'group'])->get();
        }

        return response()->json($permissions);
    }

    /**
     * Grant permission to user or group
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'resource_type' => 'required|in:folder,document',
            'resource_id' => 'required|uuid',
            'target_type' => 'required|in:user,group',
            'target_id' => 'required|uuid',
            'permissions' => 'required|array',
            'permissions.*' => 'in:can_view,can_update_metadata,can_delete,can_upload,can_share,can_download,can_manage_permissions',
        ]);

        $resourceType = $request->resource_type;
        $resourceId = $request->resource_id;

        // Get resource
        if ($resourceType === 'folder') {
            $resource = Folder::findOrFail($resourceId);
            
            // Check manage_permissions
            if (!$this->permissionValidator->canManagePermissions($user, $resource)) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            // Check can_view is included if other permissions are granted
            if (!in_array('can_view', $request->permissions) && count($request->permissions) > 0) {
                return response()->json([
                    'error' => 'can_view permission is required before granting other permissions'
                ], 422);
            }

            // Create or update permission
            $permission = FolderPermission::updateOrCreate(
                [
                    'folder_id' => $resourceId,
                    'user_id' => $request->target_type === 'user' ? $request->target_id : null,
                    'group_id' => $request->target_type === 'group' ? $request->target_id : null,
                ],
                array_combine($request->permissions, array_fill(0, count($request->permissions), true))
            );
        } else {
            $resource = Document::findOrFail($resourceId);
            
            // Check manage_permissions
            if (!$this->permissionValidator->canManageDocumentPermissions($user, $resource)) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            // Check can_view is included if other permissions are granted
            if (!in_array('can_view', $request->permissions) && count($request->permissions) > 0) {
                return response()->json([
                    'error' => 'can_view permission is required before granting other permissions'
                ], 422);
            }

            // Create or update permission
            $permission = DocumentPermission::updateOrCreate(
                [
                    'document_id' => $resourceId,
                    'user_id' => $request->target_type === 'user' ? $request->target_id : null,
                    'group_id' => $request->target_type === 'group' ? $request->target_id : null,
                ],
                array_combine($request->permissions, array_fill(0, count($request->permissions), true))
            );
        }

        AuditLogger::log($user, 'MANAGE_PERMISSIONS', $resource, ['action' => 'grant']);

        return response()->json($permission, 201);
    }

    /**
     * Update permission
     */
    public function update(Request $request, $resourceType, $resourceId, $permissionId)
    {
        $user = $request->user();

        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'in:can_view,can_update_metadata,can_delete,can_upload,can_share,can_download,can_manage_permissions',
        ]);

        // Get resource
        if ($resourceType === 'folder') {
            $resource = Folder::findOrFail($resourceId);
            
            if (!$this->permissionValidator->canManagePermissions($user, $resource)) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            $permission = FolderPermission::findOrFail($permissionId);

            // Protect owner/creator
            if ($permission->user_id && !is_null($permission->user_id)) {
                $isOwner = $resource->responsibles()
                    ->where('user_id', $permission->user_id)
                    ->where('is_owner', true)
                    ->exists();

                if ($isOwner) {
                    return response()->json(['error' => 'Cannot modify permissions of folder owner'], 403);
                }
            }
        } else {
            $resource = Document::findOrFail($resourceId);
            
            if (!$this->permissionValidator->canManageDocumentPermissions($user, $resource)) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            $permission = DocumentPermission::findOrFail($permissionId);
        }

        // Update permission
        $permission->update(
            array_combine($request->permissions, array_fill(0, count($request->permissions), true))
        );

        AuditLogger::log($user, 'MANAGE_PERMISSIONS', $resource, ['action' => 'update']);

        return response()->json($permission);
    }

    /**
     * Revoke permission
     */
    public function destroy(Request $request, $resourceType, $resourceId, $permissionId)
    {
        $user = $request->user();

        // Get resource
        if ($resourceType === 'folder') {
            $resource = Folder::findOrFail($resourceId);
            
            if (!$this->permissionValidator->canManagePermissions($user, $resource)) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            $permission = FolderPermission::findOrFail($permissionId);

            // Protect owner/creator
            if ($permission->user_id && !is_null($permission->user_id)) {
                $isOwner = $resource->responsibles()
                    ->where('user_id', $permission->user_id)
                    ->where('is_owner', true)
                    ->exists();

                if ($isOwner) {
                    return response()->json(['error' => 'Cannot revoke permissions of folder owner'], 403);
                }
            }
        } else {
            $resource = Document::findOrFail($resourceId);
            
            if (!$this->permissionValidator->canManageDocumentPermissions($user, $resource)) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            $permission = DocumentPermission::findOrFail($permissionId);
        }

        $permission->delete();
        AuditLogger::log($user, 'MANAGE_PERMISSIONS', $resource, ['action' => 'revoke']);

        return response()->noContent();
    }

    /**
     * Resolve effective permissions for a user on a resource
     */
    public function resolve(Request $request, $resourceType, $resourceId)
    {
        $user = $request->user();

        $request->validate([
            'target_user_id' => 'required|uuid|exists:users,id',
        ]);

        $targetUserId = $request->target_user_id;

        if ($resourceType === 'folder') {
            $resource = Folder::findOrFail($resourceId);
            
            if (!$this->permissionValidator->canManagePermissions($user, $resource)) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            $targetUser = User::findOrFail($targetUserId);
            $permissions = $this->permissionValidator->resolveEffectivePermissions($targetUser, $resource);
        } else {
            $resource = Document::findOrFail($resourceId);
            
            if (!$this->permissionValidator->canManageDocumentPermissions($user, $resource)) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            $targetUser = User::findOrFail($targetUserId);
            $permissions = $this->permissionValidator->resolveEffectiveDocumentPermissions($targetUser, $resource);
        }

        return response()->json($permissions);
    }
}
