<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentPermission extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'document_id',
        'group_id',
        'user_id',
        'can_view',
        'can_update_metadata',
        'can_delete',
        'can_download',
        'can_share',
        'can_manage_permissions',
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_update_metadata' => 'boolean',
        'can_delete' => 'boolean',
        'can_download' => 'boolean',
        'can_share' => 'boolean',
        'can_manage_permissions' => 'boolean',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Get the document
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the group (if permission is group-based)
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class)->withTrashed();
    }

    /**
     * Get the user (if permission is user-based)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Check if this is a group permission
     */
    public function isGroupPermission(): bool
    {
        return !is_null($this->group_id) && is_null($this->user_id);
    }

    /**
     * Check if this is a user permission
     */
    public function isUserPermission(): bool
    {
        return is_null($this->group_id) && !is_null($this->user_id);
    }

    /**
     * Get all enabled permissions as array
     */
    public function getEnabledPermissions(): array
    {
        return array_filter([
            'can_view' => $this->can_view,
            'can_update_metadata' => $this->can_update_metadata,
            'can_delete' => $this->can_delete,
            'can_download' => $this->can_download,
            'can_share' => $this->can_share,
            'can_manage_permissions' => $this->can_manage_permissions,
        ]);
    }

    /**
     * Check if view permission is required before other operations
     */
    public function requiresViewPermission(): bool
    {
        return !$this->can_view && (
            $this->can_update_metadata ||
            $this->can_delete ||
            $this->can_download ||
            $this->can_share ||
            $this->can_manage_permissions
        );
    }

    /**
     * Check if this permission grants management rights
     */
    public function canManagePermissions(): bool
    {
        return $this->can_manage_permissions === true;
    }
}
