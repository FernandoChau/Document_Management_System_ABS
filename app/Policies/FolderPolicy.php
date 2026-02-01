<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Folder;
use App\Services\AuthorizationService;

class FolderPolicy
{
    protected AuthorizationService $authorizationService;

    public function __construct(AuthorizationService $authorizationService)
    {
        $this->authorizationService = $authorizationService;
    }

    /**
     * Determine if user can view the folder
     */
    public function view(User $user, Folder $folder): bool
    {
        return $this->authorizationService->canViewFolder($user, $folder);
    }

    /**
     * Determine if user can create folders within this folder
     */
    public function create(User $user, Folder $folder): bool
    {
        return $this->authorizationService->canUploadToFolder($user, $folder);
    }

    /**
     * Determine if user can update the folder
     */
    public function update(User $user, Folder $folder): bool
    {
        return $this->authorizationService->canManageFolderPermissions($user, $folder);
    }

    /**
     * Determine if user can delete the folder
     */
    public function delete(User $user, Folder $folder): bool
    {
        return $this->authorizationService->canDeleteFolder($user, $folder);
    }

    /**
     * Determine if user can restore the folder
     */
    public function restore(User $user, Folder $folder): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if user can permanently delete the folder
     */
    public function forceDelete(User $user, Folder $folder): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if user can manage permissions for the folder
     */
    public function managePermissions(User $user, Folder $folder): bool
    {
        return $this->authorizationService->canManageFolderPermissions($user, $folder);
    }

    /**
     * Determine if user can upload to the folder
     */
    public function upload(User $user, Folder $folder): bool
    {
        return $this->authorizationService->canUploadToFolder($user, $folder);
    }
}
