<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentPermission;
use App\Models\FolderPermission;

class FolderPermissionCascadeService
{
    private const PERMISSION_FIELDS = [
        'can_view',
        'can_update_metadata',
        'can_delete',
        'can_download',
        'can_share',
        'can_manage_permissions',
    ];

    public function synchronize(FolderPermission $folderPermission): void
    {
        if (! $folderPermission->user_id) {
            return;
        }

        Document::where('folder_id', $folderPermission->folder_id)
            ->whereNull('deleted_at')
            ->each(function (Document $document) use ($folderPermission) {
                $this->synchronizeDocumentPermission($document, $folderPermission);
            });
    }

    public function synchronizeDocument(Document $document): void
    {
        $folderPermissions = FolderPermission::where('folder_id', $document->folder_id)->get();

        foreach ($folderPermissions as $folderPermission) {
            $this->synchronizeDocumentPermission($document, $folderPermission);
        }
    }

    public function removeInheritedPermissions(FolderPermission $folderPermission): void
    {
        if (! $folderPermission->user_id) {
            return;
        }

        DocumentPermission::where('is_inherited', true)
            ->whereHas('document', function ($query) use ($folderPermission) {
                $query->where('folder_id', $folderPermission->folder_id);
            })
            ->when($folderPermission->user_id, fn ($query) => $query->where('user_id', $folderPermission->user_id))
            ->when($folderPermission->group_id, fn ($query) => $query->where('group_id', $folderPermission->group_id))
            ->delete();
    }

    private function synchronizeDocumentPermission(Document $document, FolderPermission $folderPermission): void
    {
        $permissionQuery = DocumentPermission::withTrashed()
            ->where('document_id', $document->id)
            ->where('user_id', $folderPermission->user_id)
            ->where('group_id', $folderPermission->group_id);

        $permission = $permissionQuery->first();

        if ($permission?->is_revoked || ($permission && ! $permission->is_inherited)) {
            return;
        }

        $values = $folderPermission->only(self::PERMISSION_FIELDS) + [
            'is_inherited' => true,
            'is_revoked' => false,
        ];

        if ($permission) {
            $permission->fill($values);
            if ($permission->trashed()) {
                $permission->restore();
            }
            $permission->save();

            return;
        }

        $document->permissions()->create([
            'user_id' => $folderPermission->user_id,
            'group_id' => $folderPermission->group_id,
            ...$values,
        ]);
    }
}
