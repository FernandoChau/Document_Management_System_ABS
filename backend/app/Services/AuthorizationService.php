<?php

namespace App\Services;

use App\Models\User;
use App\Models\Folder;
use App\Models\Document;
use Illuminate\Support\Collection;

class AuthorizationService
{
    protected PermissionResolver $permissionResolver;

    public function __construct(PermissionResolver $permissionResolver)
    {
        $this->permissionResolver = $permissionResolver;
    }

    /**
     * Check if user can view a folder
     */
    public function canViewFolder(User $user, Folder $folder): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $permissions = $this->permissionResolver->resolveFolderPermissions($user, $folder);
        return $permissions['can_view'] ?? false;
    }

    /**
     * Check if user can upload to a folder
     */
    public function canUploadToFolder(User $user, Folder $folder): bool
    {
        // Root folder: admin only
        if ($folder->isRoot() && !$user->isAdmin()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $permissions = $this->permissionResolver->resolveFolderPermissions($user, $folder);
        return $permissions['can_view'] && $permissions['can_upload'];
    }

    /**
     * Check if user can manage folder permissions
     */
    public function canManageFolderPermissions(User $user, Folder $folder): bool
    {
        // Root folder: admin only
        if ($folder->isRoot() && !$user->isAdmin()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        // Check if user is folder responsible (owner)
        $isResponsible = $folder->responsibles()
            ->where('user_id', $user->id)
            ->where('is_owner', true)
            ->exists();

        return $isResponsible;
    }

    /**
     * Check if user can delete a folder
     */
    public function canDeleteFolder(User $user, Folder $folder): bool
    {
        // Root folder: admin only
        if ($folder->isRoot() && !$user->isAdmin()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        // Check if user is folder responsible (owner)
        $isResponsible = $folder->responsibles()
            ->where('user_id', $user->id)
            ->where('is_owner', true)
            ->exists();

        if (!$isResponsible) {
            return false;
        }

        // Also check folder-level delete permission
        $permissions = $this->permissionResolver->resolveFolderPermissions($user, $folder);
        return $permissions['can_view'] && $permissions['can_delete'];
    }

    /**
     * Check if user can view a document
     */
    public function canViewDocument(User $user, Document $document): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $permissions = $this->permissionResolver->resolveDocumentPermissions($user, $document);
        return $permissions['can_view'] ?? false;
    }

    /**
     * Check if user can download a document
     */
    public function canDownloadDocument(User $user, Document $document): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $permissions = $this->permissionResolver->resolveDocumentPermissions($user, $document);
        return $permissions['can_view'] && $permissions['can_download'];
    }

    /**
     * Check if user can delete a document
     */
    public function canDeleteDocument(User $user, Document $document): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $permissions = $this->permissionResolver->resolveDocumentPermissions($user, $document);
        return $permissions['can_view'] && $permissions['can_delete'];
    }

    /**
     * Check if user can share a document
     */
    public function canShareDocument(User $user, Document $document): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $permissions = $this->permissionResolver->resolveDocumentPermissions($user, $document);
        return $permissions['can_view'] && $permissions['can_share'];
    }

    /**
     * Check if user can update document metadata
     */
    public function canUpdateDocumentMetadata(User $user, Document $document): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $permissions = $this->permissionResolver->resolveDocumentPermissions($user, $document);
        return $permissions['can_view'] && $permissions['can_update_metadata'];
    }

    /**
     * Get all folders user can view
     */
    public function getViewableFolders(User $user): Collection
    {
        if ($user->isAdmin()) {
            return Folder::all();
        }

        // Get folders where user has explicit permissions or through groups
        $folderIds = \DB::table('folder_permissions')
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereIn('group_id', $user->groups()->pluck('id'));
            })
            ->where('can_view', true)
            ->distinct()
            ->pluck('folder_id');

        return Folder::whereIn('id', $folderIds)->get();
    }

    /**
     * Get all documents user can view
     */
    public function getViewableDocuments(User $user): Collection
    {
        if ($user->isAdmin()) {
            return Document::all();
        }

        // Get documents via folder permissions
        $folderIds = \DB::table('folder_permissions')
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereIn('group_id', $user->groups()->pluck('id'));
            })
            ->where('can_view', true)
            ->distinct()
            ->pluck('folder_id');

        $documents = Document::whereIn('folder_id', $folderIds)->get();

        // Add document-specific permissions
        $docIds = \DB::table('document_permissions')
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereIn('group_id', $user->groups()->pluck('id'));
            })
            ->where('can_view', true)
            ->distinct()
            ->pluck('document_id');

        $documents = $documents->merge(Document::whereIn('id', $docIds)->get());

        return $documents->unique();
    }
}
