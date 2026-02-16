<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ShareLink extends Model
{
    use SoftDeletes, HasUuids;

    protected $fillable = [
        'token',
        'shareable_type',
        'shareable_id',
        'created_by',
        'expires_at',
        'password',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Relationship: Polymorphic - Link belongs to a Folder or Document
     */
    public function shareable()
    {
        return $this->morphTo();
    }

    /**
     * Relationship: Link was created by a User
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if link is expired
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
