<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhotoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'album_id' => $this->album_id,
            'original_filename' => $this->original_filename,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'status' => $this->status,
            'uploaded_by' => $this->uploaded_by,
            'urls' => [
                'thumbnail' => $this->status === 'completed' ? route('photos.thumbnail', $this->id) : null,
                'medium' => $this->status === 'completed' ? route('photos.medium', $this->id) : null,
                'original' => route('photos.download', $this->id),
            ],
            'created_at' => $this->created_at,
        ];
    }
}
