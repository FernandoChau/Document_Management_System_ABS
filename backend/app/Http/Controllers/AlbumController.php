<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Http\Requests\StoreAlbumRequest;
use App\Http\Requests\UpdateAlbumRequest;
use App\Http\Requests\UpdateAlbumCoverRequest;
use App\Http\Resources\AlbumResource;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

class AlbumController extends BaseController
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Album::class);
        $albums = Album::with('cover_image')->latest()->cursorPaginate(30);
        return AlbumResource::collection($albums);
    }

    public function store(StoreAlbumRequest $request)
    {
        $this->authorize('create', Album::class);
        $album = Album::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);
        return new AlbumResource($album);
    }

    public function show(Album $album)
    {
        $this->authorize('view', $album);
        $album->load('cover_image');
        return new AlbumResource($album);
    }

    public function update(UpdateAlbumRequest $request, Album $album)
    {
        $this->authorize('update', $album);
        $album->update($request->validated());
        return new AlbumResource($album);
    }

    public function destroy(Album $album)
    {
        $this->authorize('delete', $album);
        $album->delete();
        return response()->noContent();
    }

    public function updateCover(UpdateAlbumCoverRequest $request, Album $album)
    {
        $this->authorize('changeCover', $album);
        $album->update(['cover_image_id' => $request->cover_image_id]);
        return new AlbumResource($album);
    }
}
