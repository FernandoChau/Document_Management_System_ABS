<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
class File extends Model
{
    use UuidTrait, SoftDeletes;

    protected $table = 'files';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'folder_id',
        'link',
        'tag',
        'created_by',
        'is_accessible',
        'is_removable',
        'is_removed',
        'is_public',
        'path',
        'mime_type',
        'size',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_accessible' => 'boolean',
        'is_removable' => 'boolean',
        'is_removed' => 'boolean',
        'is_public' => 'boolean',
        'size' => 'integer',
    ];

    public function folder()
    {
        return $this->belongsTo(Folder::class, 'folder_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sharedLinks()
    {
        return $this->morphMany(SharedLink::class, 'shareable');
    }

}
