<?php

namespace App\Services;

use App\Models\User;
use App\Models\Folder;
use App\Models\Document;
use App\Models\FolderPermission;
use App\Models\DocumentPermission;
use Illuminate\Support\Collection;

class PermissionResolver
{
    /**
     * Resolve effective folder permissions for a user
     * 
     * @param User $user
     * @param Folder $folder
     * @return array
     */
    public function resolveFolderPermissions(User $user, Folder $folder): array
    {
        // Admin has all permissions
        if ($user->isAdmin()) {
            return $this->getAllPermissions();
        }

        // Folder owner has all permissions
        if ($this->isFolderResponsible($user, $folder)) {
            return $this->getAllPermissions();
        }

        // Check if access is blocked by ancestors
        if ($this->isBlockedByAncestors($user, $folder)) {
            return $this->getNoPermissions();
        }

        // Merge group and user permissions
        $permissions = $this->mergePermissions(
            $this->getGroupPermissions($user, $folder),
            $this->getUserPermissions($user, $folder)
        );

        // Validate view prerequisite
        return $this->enforceViewPrerequisite($permissions);
    }

    /**
     * Resolve effective document permissions for a user
     * 
     * @param User $user
     * @param Document $document
     * @return array
     */
    public function resolveDocumentPermissions(User $user, Document $document): array
    {
        // Admin has all permissions
        if ($user->isAdmin()) {
            return $this->getAllPermissions();
        }

        // First check folder permissions (documents inherit from folder)
        $folderPermissions = $this->resolveFolderPermissions($user, $document->folder);

        if (!$folderPermissions['can_view']) {
            return $this->getNoPermissions();
        }

        // Then merge with document-specific permissions
        $permissions = $this->mergePermissions(
            $folderPermissions,
            $this->mergePermissions(
                $this->getGroupDocumentPermissions($user, $document),
                $this->getUserDocumentPermissions($user, $document)
            )
        );

        return $this->enforceViewPrerequisite($permissions);
    }

    /**
     * Check if user is blocked by ancestor folders
     * 
     * @param User $user
     * @param Folder $folder
     * @return bool
     */
    private function isBlockedByAncestors(User $user, Folder $folder): bool
    {
        foreach ($folder->ancestors() as $ancestor) {
            $ancestorPermissions = $this->resolveDirectFolderPermissions($user, $ancestor);
            if (!$ancestorPermissions['can_view']) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get direct permissions for a folder (without cascade check)
     * 
     * @param User $user
     * @param Folder $folder
     * @return array
     */
    private function resolveDirectFolderPermissions(User $user, Folder $folder): array
    {
        // Folder owner has all permissions
        if ($this->isFolderResponsible($user, $folder)) {
            return $this->getAllPermissions();
        }

        // Check user-specific permission (highest priority)
        $userPerm = $folder->permissions()
            ->where('user_id', $user->id)
            ->where('group_id', null)
            ->first();

        if ($userPerm) {
            return [
                'can_view' => $userPerm->can_view,
                'can_update_metadata' => $userPerm->can_update_metadata,
                'can_delete' => $userPerm->can_delete,
                'can_upload' => $userPerm->can_upload,
                'can_share' => $userPerm->can_share,
                'can_download' => $userPerm->can_download,
                'can_manage_permissions' => $userPerm->can_manage_permissions,
            ];
        }

        // Check group permissions (via user's groups)
        $groupPermissions = $this->getGroupPermissions($user, $folder);
        if (!empty($groupPermissions['can_view'])) {
            return $groupPermissions;
        }

        return $this->getNoPermissions();
    }

    /**
     * Check if user is a responsible (owner) for the folder
     * 
     * @param User $user
     * @param Folder $folder
     * @return bool
     */
    private function isFolderResponsible(User $user, Folder $folder): bool
    {
        return \DB::table('folder_responsibles')
            ->where('folder_id', $folder->id)
            ->where('user_id', $user->id)
            ->where('is_owner', true)
            ->exists();
    }

    /**
     * Get group-based permissions
     * 
     * @param User $user
     * @param Folder|Document $resource
     * @return array
     */
    private function getGroupPermissions($user, $resource): array
    {
        $userGroupIds = $user->groups()->pluck('groups.id')->toArray();

        if (empty($userGroupIds)) {
            return $this->getNoPermissions();
        }

        // Get the appropriate table name
        $tableName = $resource instanceof Folder ? 'folder_permissions' : 'document_permissions';

        $resourceColumn = $resource instanceof Folder ? 'folder_id' : 'document_id';

        $groupPerm = \DB::table($tableName)
            ->where($resourceColumn, $resource->id)
            ->whereIn('group_id', $userGroupIds)
            ->where('user_id', null)
            ->latest('created_at')
            ->first();

        if ($groupPerm) {
            return [
                'can_view' => (bool) $groupPerm->can_view,
                'can_update_metadata' => (bool) $groupPerm->can_update_metadata,
                'can_delete' => (bool) $groupPerm->can_delete,
                'can_upload' => isset($groupPerm->can_upload) ? (bool) $groupPerm->can_upload : false,
                'can_share' => (bool) $groupPerm->can_share,
                'can_download' => (bool) $groupPerm->can_download,
                'can_manage_permissions' => isset($groupPerm->can_manage_permissions) ? (bool) $groupPerm->can_manage_permissions : false,
            ];
        }

        return $this->getNoPermissions();
    }

    /**
     * Get user-specific permissions
     * 
     * @param User $user
     * @param Folder $folder
     * @return array
     */
    private function getUserPermissions(User $user, Folder $folder): array
    {
        $userPerm = $folder->permissions()
            ->where('user_id', $user->id)
            ->where('group_id', null)
            ->first();

        if ($userPerm) {
            return [
                'can_view' => $userPerm->can_view,
                'can_update_metadata' => $userPerm->can_update_metadata,
                'can_delete' => $userPerm->can_delete,
                'can_upload' => $userPerm->can_upload,
                'can_share' => $userPerm->can_share,
                'can_download' => $userPerm->can_download,
                'can_manage_permissions' => $userPerm->can_manage_permissions,
            ];
        }

        return $this->getNoPermissions();
    }

    /**
     * Get group document permissions
     * 
     * @param User $user
     * @param Document $document
     * @return array
     */
    private function getGroupDocumentPermissions(User $user, Document $document): array
    {
        return $this->getGroupPermissions($user, $document);
    }

    /**
     * Get user document permissions
     * 
     * @param User $user
     * @param Document $document
     * @return array
     */
    private function getUserDocumentPermissions(User $user, Document $document): array
    {
        $userPerm = $document->permissions()
            ->where('user_id', $user->id)
            ->where('group_id', null)
            ->first();

        if ($userPerm) {
            return [
                'can_view' => $userPerm->can_view,
                'can_update_metadata' => $userPerm->can_update_metadata,
                'can_delete' => $userPerm->can_delete,
                'can_download' => $userPerm->can_download,
                'can_share' => $userPerm->can_share,
                'can_manage_permissions' => $userPerm->can_manage_permissions,
            ];
        }

        return $this->getNoPermissions();
    }

    /**
     * Merge two permission arrays (second overrides first)
     * 
     * @param array $base
     * @param array $override
     * @return array
     */
    private function mergePermissions(array $base, array $override): array
    {
        return array_merge($base, array_filter($override, fn($v) => !is_null($v)));
    }

    /**
     * Enforce view prerequisite: other permissions require can_view=true
     * 
     * @param array $permissions
     * @return array
     */
    private function enforceViewPrerequisite(array $permissions): array
    {
        if (!$permissions['can_view']) {
            return $this->getNoPermissions();
        }

        return $permissions;
    }

    /**
     * Get all permissions enabled
     * 
     * @return array
     */
    private function getAllPermissions(): array
    {
        return [
            'can_view' => true,
            'can_update_metadata' => true,
            'can_delete' => true,
            'can_upload' => true,
            'can_share' => true,
            'can_download' => true,
            'can_manage_permissions' => true,
        ];
    }

    /**
     * Get no permissions
     * 
     * @return array
     */
    private function getNoPermissions(): array
    {
        return [
            'can_view' => false,
            'can_update_metadata' => false,
            'can_delete' => false,
            'can_upload' => false,
            'can_share' => false,
            'can_download' => false,
            'can_manage_permissions' => false,
        ];
    }
}
