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
        'is_root',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'is_root' => 'boolean',
    ];

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
        });

        static::updating(function ($model) {
            if ($model->isDirty('name')) {
                $model->slug = Str::slug($model->name);
            }
        });
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
        return $this->is_root === true;
    }

    /**
     * Get all descendants (children recursively)
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }
}
