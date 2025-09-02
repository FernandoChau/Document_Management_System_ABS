<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SharedLink extends Model
{
    protected $fillable = ['token', 'expires_at', 'created_by'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function shareable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isExpired(): bool
    {
        return $this->expires_at?->isPast() ?? true;
    }

    public function scopeActive($q)
    {
        return $q->where('expires_at', '>', now());
    }
}
