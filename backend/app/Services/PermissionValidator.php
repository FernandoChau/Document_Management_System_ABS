<?php

namespace App\Services;

use App\Models\User;
use App\Models\Folder;
use App\Models\Document;
use App\Exceptions\PermissionDeniedException;

class PermissionValidator
{
    private PermissionResolver $permissionResolver;

    public function __construct(PermissionResolver $permissionResolver)
    {
        $this->permissionResolver = $permissionResolver;
    }

    /**
     * Validate if user can manage permissions for a folder
     * 
     * @param User $user
     * @param Folder $folder
     * @return bool
     */
    public function canManagePermissions(User $user, Folder $folder): bool
    {
        // Admin can always manage permissions
        if ($user->isAdmin()) {
            return true;
        }

        // Owner of folder can manage permissions
        $isOwner = $folder->responsibles()
            ->where('user_id', $user->id)
            ->where('is_owner', true)
            ->exists();

        if ($isOwner) {
            return true;
        }

        // Check if user has can_manage_permissions on folder
        $folderPerm = $folder->permissions()
            ->where('user_id', $user->id)
            ->where('group_id', null)
            ->first();

        if ($folderPerm && $folderPerm->can_manage_permissions) {
            return true;
        }

        // Check group permissions for can_manage_permissions
        $userGroupIds = $user->groups()->pluck('groups.id')->toArray();
        if (!empty($userGroupIds)) {
            $groupPerm = $folder->permissions()
                ->whereIn('group_id', $userGroupIds)
                ->where('user_id', null)
                ->where('can_manage_permissions', true)
                ->first();

            if ($groupPerm) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate if user can manage permissions for a document
     * 
     * @param User $user
     * @param Document $document
     * @return bool
     */
    public function canManageDocumentPermissions(User $user, Document $document): bool
    {
        // Admin can always manage permissions
        if ($user->isAdmin()) {
            return true;
        }

        // Check if user has can_manage_permissions on document
        $docPerm = $document->permissions()
            ->where('user_id', $user->id)
            ->where('group_id', null)
            ->first();

        if ($docPerm && $docPerm->can_manage_permissions) {
            return true;
        }

        // Check group permissions for can_manage_permissions
        $userGroupIds = $user->groups()->pluck('groups.id')->toArray();
        if (!empty($userGroupIds)) {
            $groupPerm = $document->permissions()
                ->whereIn('group_id', $userGroupIds)
                ->where('user_id', null)
                ->where('can_manage_permissions', true)
                ->first();

            if ($groupPerm) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate action requirements for a folder
     * Returns an array with validation result or throws exception
     * 
     * @param User $user
     * @param Folder $folder
     * @param string $action (view, create, update, delete, upload, share, download, manage_permissions)
     * @return array
     * @throws PermissionDeniedException
     */
    public function validateFolderAction(User $user, Folder $folder, string $action): array
    {
        // Resolve effective permissions
        $permissions = $this->permissionResolver->resolveFolderPermissions($user, $folder);

        // Check if user has can_view (prerequisite)
        if (!$permissions['can_view'] && $action !== 'none') {
            throw new PermissionDeniedException("User does not have view permission for this folder.");
        }

        // Action-specific validation
        switch ($action) {
            case 'view':
                if (!$permissions['can_view']) {
                    throw new PermissionDeniedException("User cannot view this folder.");
                }
                break;

            case 'create':
                if (!$permissions['can_upload']) {
                    throw new PermissionDeniedException("User cannot create items in this folder.");
                }
                break;

            case 'update':
                if (!$permissions['can_update_metadata']) {
                    throw new PermissionDeniedException("User cannot update this folder.");
                }
                break;

            case 'delete':
                if (!$permissions['can_delete']) {
                    throw new PermissionDeniedException("User cannot delete this folder.");
                }
                break;

            case 'download':
                if (!$permissions['can_download']) {
                    throw new PermissionDeniedException("User cannot download this folder.");
                }
                break;

            case 'share':
                if (!$permissions['can_share']) {
                    throw new PermissionDeniedException("User cannot share this folder.");
                }
                break;

            case 'manage_permissions':
                if (!$this->canManagePermissions($user, $folder)) {
                    throw new PermissionDeniedException("User cannot manage permissions for this folder.");
                }
                break;
        }

        return [
            'is_valid' => true,
            'permissions' => $permissions,
        ];
    }

    /**
     * Validate action requirements for a document
     * Returns an array with validation result or throws exception
     * 
     * @param User $user
     * @param Document $document
     * @param string $action (view, update, delete, share, download, manage_permissions)
     * @return array
     * @throws PermissionDeniedException
     */
    public function validateDocumentAction(User $user, Document $document, string $action): array
    {
        // Resolve effective permissions
        $permissions = $this->permissionResolver->resolveDocumentPermissions($user, $document);

        // Check if user has can_view (prerequisite)
        if (!$permissions['can_view'] && $action !== 'none') {
            throw new PermissionDeniedException("User does not have view permission for this document.");
        }

        // Action-specific validation
        switch ($action) {
            case 'view':
                if (!$permissions['can_view']) {
                    throw new PermissionDeniedException("User cannot view this document.");
                }
                break;

            case 'update':
                if (!$permissions['can_update_metadata']) {
                    throw new PermissionDeniedException("User cannot update this document.");
                }
                break;

            case 'delete':
                if (!$permissions['can_delete']) {
                    throw new PermissionDeniedException("User cannot delete this document.");
                }
                break;

            case 'download':
                if (!$permissions['can_download']) {
                    throw new PermissionDeniedException("User cannot download this document.");
                }
                break;

            case 'share':
                if (!$permissions['can_share']) {
                    throw new PermissionDeniedException("User cannot share this document.");
                }
                break;

            case 'manage_permissions':
                if (!$this->canManageDocumentPermissions($user, $document)) {
                    throw new PermissionDeniedException("User cannot manage permissions for this document.");
                }
                break;
        }

        return [
            'is_valid' => true,
            'permissions' => $permissions,
        ];
    }

    /**
     * Resolve effective permissions for a folder
     * 
     * @param User $user
     * @param Folder $folder
     * @return array
     */
    public function resolveEffectivePermissions(User $user, Folder $folder): array
    {
        return $this->permissionResolver->resolveFolderPermissions($user, $folder);
    }

    /**
     * Resolve effective permissions for a document
     * 
     * @param User $user
     * @param Document $document
     * @return array
     */
    public function resolveEffectiveDocumentPermissions(User $user, Document $document): array
    {
        return $this->permissionResolver->resolveDocumentPermissions($user, $document);
    }
}
