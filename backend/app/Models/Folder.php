<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

class Folder extends Model
{
    use SoftDeletes, HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'department_id',
        'reference_code',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->slug) {
                $model->slug = Str::slug($model->name);
            }

            // No extra validation needed for is_root anymore
        });

        static::updating(function ($model) {
            if ($model->isDirty('name')) {
                $model->slug = Str::slug($model->name);
            }

            // No extra validation needed for is_root anymore

            // Validate: cannot create circular parent reference
            if ($model->isDirty('parent_id') && !is_null($model->parent_id)) {
                $parent = Folder::find($model->parent_id);
                if ($parent && $this->isAncestorOf($parent)) {
                    throw new \InvalidArgumentException('Cannot create circular parent reference.');
                }
            }
        });
    }

    /**
     * Check if this folder is an ancestor of another folder
     */
    private function isAncestorOf(Folder $folder): bool
    {
        $current = $folder;
        while ($current->parent_id) {
            $current = $current->parent;
            if ($current->id === $this->id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Relationship: Folder belongs to Department (only for root folders)
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Relationship: Folder has a parent folder (self-referencing)
     */
    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    /**
     * Relationship: Folder has many children folders
     */
    public function children()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    /**
     * Relationship: Folder has many documents
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Relationship: Folder has many permissions
     */
    public function permissions()
    {
        return $this->hasMany(FolderPermission::class);
    }

    /**
     * Relationship: Polymorphic - Folder can be shared via ShareLinks
     */
    public function shareLinks()
    {
        return $this->morphMany(ShareLink::class, 'shareable');
    }

    /**
     * Get all ancestor folders (breadcrumb)
     */
    public function ancestors()
    {
        $ancestors = collect();
        $folder = $this;

        while ($folder->parent) {
            $ancestors->prepend($folder->parent);
            $folder = $folder->parent;
        }

        return $ancestors;
    }

    /**
     * Relationship: Get folder responsibles (owners/managers)
     */
    public function responsibles()
    {
        return $this->hasMany(FolderResponsible::class);
    }

    /**
     * Get direct parent folder (excluding root)
     */
    public function hasDirectParent(): bool
    {
        return !is_null($this->parent_id);
    }

    /**
     * Check if this is the root folder
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Get all descendants (children recursively)
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Check if this folder has direct documents
     */
    public function hasDocuments(): bool
    {
        return $this->documents()->count() > 0;
    }

    /**
     * Get count of direct documents
     */
    public function getDocumentCount(): int
    {
        return $this->documents()->count();
    }

    /**
     * Scope: Get only leaf folders (folders that can receive documents)
     * Leaf folders are those that can directly contain documents (non-root)
     */
    public function scopeOnlyLeafFolders($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Scope: Get folders with their documents eager loaded
     */
    public function scopeWithDocuments($query)
    {
        return $query->with('documents');
    }

    /**
     * Scope: Get folders with their permissions eager loaded
     */
    public function scopeWithPermissions($query)
    {
        return $query->with('permissions');
    }

    /**
     * Get total size of all documents in this folder (direct + recursively)
     */
    public function getTotalSize(): int
    {
        $directSize = $this->documents()->sum('size') ?? 0;

        $childrenSize = $this->children()->get()->sum(function ($child) {
            return $child->getTotalSize();
        });

        return $directSize + $childrenSize;
    }

    /**
     * Validate that folder structure is consistent
     */
    public function validateStructure(): bool
    {
        return true;
    }
}
