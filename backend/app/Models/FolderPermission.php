<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FolderPermission extends Model
{
    use SoftDeletes, HasUuids;

    protected $fillable = [
        'folder_id',
        'group_id',
        'user_id',
        'can_view',
        'can_update_metadata',
        'can_delete',
        'can_upload',
        'can_share',
        'can_download',
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_update_metadata' => 'boolean',
        'can_delete' => 'boolean',
        'can_upload' => 'boolean',
        'can_share' => 'boolean',
        'can_download' => 'boolean',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Relationship: Permission belongs to a Folder
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Relationship: Permission belongs to a Group (if group-based)
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class)->withTrashed();
    }

    /**
     * Relationship: Permission belongs to a User (if user-based)
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
            'can_upload' => $this->can_upload,
            'can_share' => $this->can_share,
            'can_download' => $this->can_download,
        ]);
    }
}
