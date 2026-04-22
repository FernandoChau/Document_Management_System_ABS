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

    protected $appends = ['metadata'];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Validate: documents must always have a folder_id (cannot be in root)
            if (is_null($model->folder_id)) {
                throw new \InvalidArgumentException('Documents must belong to a folder. Root uploads are not allowed.');
            }

            // Validate: folder must exist
            $folder = Folder::find($model->folder_id);
            if (!$folder) {
                throw new \InvalidArgumentException('Parent folder does not exist.');
            }

            // Validate: parent folder should not be in root (optional design decision)
            if ($folder->is_root) {
                throw new \InvalidArgumentException('Cannot upload documents directly to root folders.');
            }
        });

        static::updating(function ($model) {
            // Validate: documents cannot be moved to null folder
            if ($model->isDirty('folder_id') && is_null($model->folder_id)) {
                throw new \InvalidArgumentException('Documents must belong to a folder.');
            }
        });
    }

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

    /**
     * Scope: Get only metadata columns (for list views, etc.)
     */
    public function scopeWithMetadata($query)
    {
        return $query->select([
            'id',
            'folder_id',
            'name',
            'reference_code',
            'mime_type',
            'size',
            'created_at',
            'user_id',
        ]);
    }

    /**
     * Scope: Load with folder relationship to avoid N+1
     */
    public function scopeWithFolder($query)
    {
        return $query->with('folder');
    }

    /**
     * Scope: Load with uploader relationship to avoid N+1
     */
    public function scopeWithUploader($query)
    {
        return $query->with('uploader');
    }

    /**
     * Get document metadata as array (readonly via API)
     * Includes: name, size, mime_type, created_at, user_id, reference_code, folder_id
     */
    public function getMetadataAttribute(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'size' => $this->size,
            'mime_type' => $this->mime_type,
            'created_at' => $this->created_at?->toIso8601String(),
            'user_id' => $this->user_id,
            'reference_code' => $this->reference_code,
            'folder_id' => $this->folder_id,
            'uploader_name' => $this->uploader?->name ?? 'Unknown',
        ];
    }

    /**
     * Get human-readable file size
     */
    public function getHumanReadableSizeAttribute(): string
    {
        $bytes = $this->size ?? 0;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Validate that folder_id is always set
     */
    public function hasValidFolder(): bool
    {
        return !is_null($this->folder_id) && $this->folder()->exists();
    }
}
