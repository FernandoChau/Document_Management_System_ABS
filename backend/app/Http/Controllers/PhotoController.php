<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Album;
use App\Http\Requests\StorePhotoRequest;
use App\Http\Resources\PhotoResource;
use App\Services\ImageProcessingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

class PhotoController extends BaseController
{
    use AuthorizesRequests;

    public function index($albumId)
    {
        $album = Album::findOrFail($albumId);
        $this->authorize('view', $album);
        
        $photos = Photo::where('album_id', $albumId)->latest()->cursorPaginate(30);
        return PhotoResource::collection($photos);
    }

    public function store(StorePhotoRequest $request)
    {
        $this->authorize('create', Photo::class);
        
        $album = Album::findOrFail($request->album_id);
        $this->authorize('view', $album); // Ensure user has access to album

        $file = $request->file('photo');
        $extension = $file->getClientOriginalExtension();
        $uuid = Str::uuid()->toString();
        $generatedFilename = $uuid . '.' . $extension;
        
        $path = $file->storeAs('images/original/' . $album->id, $generatedFilename, 'local');

        $photo = Photo::create([
            'album_id' => $album->id,
            'original_filename' => $file->getClientOriginalName(),
            'generated_filename' => $generatedFilename,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'status' => 'processing',
            'uploaded_by' => $request->user()->id,
        ]);

        // Process image synchronously
        ImageProcessingService::processImage($photo);

        return response()->json(new PhotoResource($photo), 201);
    }

    public function destroy(Photo $photo)
    {
        $this->authorize('delete', $photo);
        $album = $photo->album;
        
        $photo->delete();

        // Cover fallback logic
        if ($album->cover_image_id === $photo->id) {
            $nextPhoto = Photo::where('album_id', $album->id)->oldest()->first();
            $album->update(['cover_image_id' => $nextPhoto ? $nextPhoto->id : null]);
        }

        return response()->noContent();
    }

    public function download(Photo $photo)
    {
        $this->authorize('view', $photo);
        $path = 'images/original/' . $photo->album_id . '/' . $photo->generated_filename;
        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }
        return Storage::disk('local')->download($path, $photo->original_filename);
    }

    public function thumbnail(Photo $photo)
    {
        $this->authorize('view', $photo);
        $ext = pathinfo($photo->generated_filename, PATHINFO_EXTENSION);
        $filename = pathinfo($photo->generated_filename, PATHINFO_FILENAME) . '_thumb.' . $ext;
        $path = 'images/thumbnails/' . $photo->album_id . '/' . $filename;
        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }
        return Storage::disk('local')->response($path);
    }

    public function medium(Photo $photo)
    {
        $this->authorize('view', $photo);
        $ext = pathinfo($photo->generated_filename, PATHINFO_EXTENSION);
        $filename = pathinfo($photo->generated_filename, PATHINFO_FILENAME) . '_medium.' . $ext;
        $path = 'images/medium/' . $photo->album_id . '/' . $filename;
        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }
        return Storage::disk('local')->response($path);
    }
}
