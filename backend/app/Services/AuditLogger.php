<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    /**
     * Log a user action.
     *
     * @param User|null $user The actor (null for system)
     * @param string $action The action verb (VIEW, DOWNLOAD, UPLOAD, SHARE, etc)
     * @param Model $resource The target resource (Document or Folder)
     * @param array $metadata Additional context
     * @return AuditLog
     */
    public static function log(?User $user, string $action, Model $resource, array $metadata = []): AuditLog
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
