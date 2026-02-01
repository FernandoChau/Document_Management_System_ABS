<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
    use \Illuminate\Database\Eloquent\Concerns\HasUuids;
    
    protected $fillable = [
        'name',
        'description',
        'slug',
    ];
    
    protected $keyType = 'string';
    public $incrementing = false;
    
    /**
     * Relationship: Department has many root folders
     */
    public function rootFolders()
    {
        return $this->hasMany(Folder::class)->where('is_root', true);
    }
    
    /**
     * Relationship: Department has many folders (root and nested)
     */
    public function folders()
    {
        return $this->hasMany(Folder::class);
    }
}
