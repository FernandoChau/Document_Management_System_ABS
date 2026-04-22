<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Folder;
use App\Services\AuthorizationService;
use App\Services\FolderValidator;

class FolderPolicy
{
    protected AuthorizationService $authorizationService;
    protected FolderValidator $folderValidator;

    public function __construct(AuthorizationService $authorizationService, FolderValidator $folderValidator)
    {
        $this->authorizationService = $authorizationService;
        $this->folderValidator = $folderValidator;
    }

    /**
     * Determine if user can view the folder
     */
    public function view(User $user, Folder $folder): bool
    {
        // Guard: Check folder is not deleted
        if ($this->isDeleted($folder)) {
            return false;
        }

        // Guard: Check ancestor chain integrity
        try {
            $this->folderValidator->validateAncestorChainIntegrity($folder);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        return $this->authorizationService->canViewFolder($user, $folder);
    }

    /**
     * Determine if user can create folders within this folder
     */
    public function create(User $user, Folder $folder): bool
    {
        // Guard: Check folder is not deleted
        if ($this->isDeleted($folder)) {
            return false;
        }

        // Guard: Cannot create in root for non-admins
        if ($folder->is_root && !$user->isAdmin()) {
            return false;
        }

        // Guard: Check ancestor chain integrity
        try {
            $this->folderValidator->validateAncestorChainIntegrity($folder);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        return $this->authorizationService->canUploadToFolder($user, $folder);
    }

    /**
     * Determine if user can update the folder
     */
    public function update(User $user, Folder $folder): bool
    {
        // Guard: Check folder is not deleted
        if ($this->isDeleted($folder)) {
            return false;
        }

        // Guard: Cannot update root folder metadata
        if ($folder->is_root && !$user->isAdmin()) {
            return false;
        }

        // Guard: Check hierarchy validity
        if (!$this->folderValidator->validateFolderHierarchy($folder)) {
            return false;
        }

        return $this->authorizationService->canManageFolderPermissions($user, $folder);
    }

    /**
     * Determine if user can delete the folder
     */
    public function delete(User $user, Folder $folder): bool
    {
        // Guard: Check folder is not already deleted
        if ($this->isDeleted($folder)) {
            return false;
        }

        // Guard: Cannot delete root folder
        if ($folder->is_root) {
            return false;
        }

        // Guard: Check hierarchy validity
        if (!$this->folderValidator->validateFolderHierarchy($folder)) {
            return false;
        }

        return $this->authorizationService->canDeleteFolder($user, $folder);
    }

    /**
     * Determine if user can restore the folder
     */
    public function restore(User $user, Folder $folder): bool
    {
        // Guard: Only admins can restore
        if (!$user->isAdmin()) {
            return false;
        }

        // Guard: Folder must be deleted
        if (!$this->isDeleted($folder)) {
            return false;
        }

        // Guard: Cannot restore if parent is deleted
        if ($folder->parent_id && $folder->parent?->trashed()) {
            return false;
        }

        return true;
    }

    /**
     * Determine if user can permanently delete the folder
     */
    public function forceDelete(User $user, Folder $folder): bool
    {
        // Guard: Only admins can force delete
        if (!$user->isAdmin()) {
            return false;
        }

        // Guard: Folder must be deleted
        if (!$this->isDeleted($folder)) {
            return false;
        }

        return true;
    }

    /**
     * Determine if user can manage permissions for the folder
     */
    public function managePermissions(User $user, Folder $folder): bool
    {
        // Guard: Check folder is not deleted
        if ($this->isDeleted($folder)) {
            return false;
        }

        // Guard: Cannot manage permissions on root for non-admins
        if ($folder->is_root && !$user->isAdmin()) {
            return false;
        }

        return $this->authorizationService->canManageFolderPermissions($user, $folder);
    }

    /**
     * Determine if user can upload to the folder
     */
    public function upload(User $user, Folder $folder): bool
    {
        // Guard: Check folder is not deleted
        if ($this->isDeleted($folder)) {
            return false;
        }

        // Guard: Check ancestor chain integrity
        try {
            $this->folderValidator->validateAncestorChainIntegrity($folder);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        return $this->authorizationService->canUploadToFolder($user, $folder);
    }

    /**
     * Check if folder is deleted (soft delete)
     */
    protected function isDeleted(Folder $folder): bool
    {
        return $folder->trashed();
    }
}
