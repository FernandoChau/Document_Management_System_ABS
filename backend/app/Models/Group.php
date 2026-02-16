<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Get the members of this group
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_members', 'group_id', 'user_id')
            ->withTimestamps()
            ->withPivot('joined_at');
    }

    /**
     * Get folder permissions for this group
     */
    public function folderPermissions(): HasMany
    {
        return $this->hasMany(FolderPermission::class);
    }

    /**
     * Get document permissions for this group
     */
    public function documentPermissions(): HasMany
    {
        return $this->hasMany(DocumentPermission::class);
    }

    /**
     * Get the user who created this group
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Add a member to the group
     */
    public function addMember(User $user): void
    {
        $this->members()->attach($user->id, ['joined_at' => now()]);
    }

    /**
     * Remove a member from the group
     */
    public function removeMember(User $user): void
    {
        $this->members()->detach($user->id);
    }

    /**
     * Check if user is member of this group
     */
    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }
}
