<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'album_id' => 'required|uuid|exists:albums,id',
            'photo' => 'required|image|mimes:jpeg,png,webp,gif|max:10240',
        ];
    }
}
