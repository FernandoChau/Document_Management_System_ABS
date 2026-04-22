<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Folder;
use App\Models\Document;
use Illuminate\Database\Eloquent\Model;

/**
 * AuditService - Centraliza logging de todas operações do sistema
 * Ações suportadas: VIEW, UPLOAD, DOWNLOAD, CREATE, UPDATE_METADATA, 
 *                   SOFT_DELETE, PERMANENT_DELETE, RESTORE, SHARE, 
 *                   PERMISSION_GRANT, PERMISSION_REVOKE
 */
class AuditService
{
    /**
     * Log uma visualização de recurso
     */
    public function logView(?User $user, Model $resource, array $metadata = []): AuditLog
    {
        return $this->log($user, 'VIEW', $resource, $metadata);
    }

    /**
     * Log upload de documento
     */
    public function logUpload(?User $user, Document $document, array $metadata = []): AuditLog
    {
        $defaultMetadata = [
            'size' => $document->size,
            'mime_type' => $document->mime_type,
            'folder_id' => $document->folder_id,
        ];
        
        return $this->log($user, 'UPLOAD', $document, array_merge($defaultMetadata, $metadata));
    }

    /**
     * Log download de documento ou pasta (ZIP)
     */
    public function logDownload(?User $user, Model $resource, array $metadata = []): AuditLog
    {
        $defaultMetadata = [];
        
        if ($resource instanceof Document) {
            $defaultMetadata = ['size' => $resource->size];
        } elseif ($resource instanceof Folder) {
            $defaultMetadata = ['folder_id' => $resource->id];
        }
        
        return $this->log($user, 'DOWNLOAD', $resource, array_merge($defaultMetadata, $metadata));
    }

    /**
     * Log criação de pasta
     */
    public function logCreateFolder(?User $user, Folder $folder, array $metadata = []): AuditLog
    {
        $defaultMetadata = [
            'name' => $folder->name,
            'parent_id' => $folder->parent_id,
        ];
        
        return $this->log($user, 'CREATE', $folder, array_merge($defaultMetadata, $metadata));
    }

    /**
     * Log atualização de metadados
     */
    public function logUpdateMetadata(?User $user, Model $resource, array $oldValues = [], array $newValues = []): AuditLog
    {
        $metadata = [
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ];
        
        return $this->log($user, 'UPDATE_METADATA', $resource, $metadata);
    }

    /**
     * Log soft delete (move to trash)
     */
    public function logSoftDelete(?User $user, Model $resource, array $metadata = []): AuditLog
    {
        return $this->log($user, 'SOFT_DELETE', $resource, $metadata);
    }

    /**
     * Log permanent delete (remove from trash)
     */
    public function logPermanentDelete(?User $user, Model $resource, array $metadata = []): AuditLog
    {
        return $this->log($user, 'PERMANENT_DELETE', $resource, $metadata);
    }

    /**
     * Log restore from trash
     */
    public function logRestore(?User $user, Model $resource, array $metadata = []): AuditLog
    {
        return $this->log($user, 'RESTORE', $resource, $metadata);
    }

    /**
     * Log share link creation
     */
    public function logShare(?User $user, Model $resource, array $metadata = []): AuditLog
    {
        $defaultMetadata = [
            'resource_type' => class_basename($resource),
        ];
        
        return $this->log($user, 'SHARE', $resource, array_merge($defaultMetadata, $metadata));
    }

    /**
     * Log permission grant
     */
    public function logPermissionGrant(?User $user, Model $resource, $targetUser, array $permissions = []): AuditLog
    {
        $metadata = [
            'target_user_id' => $targetUser->id ?? null,
            'target_user_name' => $targetUser->name ?? null,
            'permissions' => $permissions,
        ];
        
        return $this->log($user, 'PERMISSION_GRANT', $resource, $metadata);
    }

    /**
     * Log permission revoke
     */
    public function logPermissionRevoke(?User $user, Model $resource, $targetUser, array $permissions = []): AuditLog
    {
        $metadata = [
            'target_user_id' => $targetUser->id ?? null,
            'target_user_name' => $targetUser->name ?? null,
            'permissions' => $permissions,
        ];
        
        return $this->log($user, 'PERMISSION_REVOKE', $resource, $metadata);
    }

    /**
     * Generic log method
     */
    public function log(?User $user, string $action, Model $resource, array $metadata = []): AuditLog
    {
        return AuditLog::create([
            'user_id' => $user?->id,
            'action' => strtoupper($action),
            'resource_type' => class_basename($resource),
            'resource_id' => $resource->id,
            'metadata' => $metadata,
        ]);
    }
}
