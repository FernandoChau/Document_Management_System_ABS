<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AuditLog extends Model
{
    use SoftDeletes, HasUuids;

    // Disable updated_at since logs are immutable
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action', // VIEW, DOWNLOAD, UPLOAD, SHARE, SOFT_DELETE...
        'resource_type',
        'resource_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Relationship: Log belongs to a User (optional, can be system/guest)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
