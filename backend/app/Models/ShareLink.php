<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\DB;

class ShareLink extends Model
{
    use SoftDeletes, HasUuids;

    protected $hidden = [
        'password',
    ];

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

    public function checkPassword(string $password): bool
    {
        $storedPassword = $this->getRawOriginal('password');

        if (!$storedPassword) {
            return true;
        }

        if (password_get_info($storedPassword)['algo'] !== null) {
            return \Illuminate\Support\Facades\Hash::check($password, $storedPassword);
        }

        if (!hash_equals($storedPassword, hash('sha256', $password))) {
            return false;
        }

        $this->password = $password;

        if ($this->exists) {
            $this->save();
        }

        return true;
    }

    public function reserveDownload(): bool
    {
        $query = DB::table($this->getTable())
            ->where($this->getKeyName(), $this->getKey())
            ->whereNull('deleted_at')
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where(function ($query): void {
                $query->whereNull('max_downloads')
                    ->orWhereColumn('downloads_count', '<', 'max_downloads');
            });

        return $query->increment('downloads_count') === 1;
    }
}
