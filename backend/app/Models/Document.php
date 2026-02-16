<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Document extends Model
{
    use SoftDeletes, HasUuids;

    protected $fillable = [
        'folder_id',
        'name',
        'file_path',
        'reference_code',
        'mime_type',
        'size',
        'year',
        'sequence_number',
        'user_id',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Relationship: Document belongs to a Folder
     */
    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Relationship: Document belongs to a User (Uploader)
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: Document has one DocumentContent (Extracted text)
     */
    public function content()
    {
        return $this->hasOne(DocumentContent::class);
    }

    /**
     * Relationship: Polymorphic - Document can be shared via ShareLinks
     */
    public function shareLinks()
    {
        return $this->morphMany(ShareLink::class, 'shareable');
    }

    /**
     * Relationship: Document has many permissions
     */
    public function permissions()
    {
        return $this->hasMany(DocumentPermission::class);
    }

    /**
     * Get folder-level permissions (inherited from parent folder)
     */
    public function folderPermissions()
    {
        return $this->folder->permissions();
    }

    /**
     * Get effective permissions combining folder and document-level
     */
    public function effectivePermissions()
    {
        return $this->permissions()->where('can_view', true);
    }
}
