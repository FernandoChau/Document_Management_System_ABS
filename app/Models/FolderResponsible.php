<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FolderResponsible extends Model
{
    use HasUuids;

    protected $table = 'folder_responsibles';

    protected $fillable = [
        'folder_id',
        'user_id',
        'is_owner',
    ];

    protected $casts = [
        'is_owner' => 'boolean',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Get the folder
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
