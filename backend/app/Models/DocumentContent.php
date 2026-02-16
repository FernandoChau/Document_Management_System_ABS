<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DocumentContent extends Model
{
    use SoftDeletes, HasUuids;

    protected $fillable = [
        'document_id',
        'extracted_text',
        'extraction_status', // pending, completed, failed
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Relationship: Content belongs to a Document
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
