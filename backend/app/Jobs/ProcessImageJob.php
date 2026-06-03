<?php

namespace App\Jobs;

use App\Models\Photo;
use App\Models\Album;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProcessImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60];

    protected $photo;

    /**
     * Create a new job instance.
     */
    public function __construct(Photo $photo)
    {
        $this->photo = $photo;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $originalPath = 'images/original/' . $this->photo->album_id . '/' . $this->photo->generated_filename;

        if (!Storage::disk('local')->exists($originalPath)) {
            $this->photo->update(['status' => 'failed']);
            return;
        }

        $manager = new ImageManager(new Driver());
        $imageContent = Storage::disk('local')->get($originalPath);
        $image = $manager->read($imageContent);

        // Generate thumbnail (e.g. 300px width, aspect ratio preserved)
        $thumbnailImage = clone $image;
        $thumbnailImage->scaleDown(width: 300);
        $thumbExt = pathinfo($this->photo->generated_filename, PATHINFO_EXTENSION);
        $thumbFilename = pathinfo($this->photo->generated_filename, PATHINFO_FILENAME) . '_thumb.' . $thumbExt;
        $thumbPath = 'images/thumbnails/' . $this->photo->album_id . '/' . $thumbFilename;
        Storage::disk('local')->put($thumbPath, (string) $thumbnailImage->encode());

        // Generate medium (e.g. 800px width, aspect ratio preserved)
        $mediumImage = clone $image;
        $mediumImage->scaleDown(width: 800);
        $mediumFilename = pathinfo($this->photo->generated_filename, PATHINFO_FILENAME) . '_medium.' . $thumbExt;
        $mediumPath = 'images/medium/' . $this->photo->album_id . '/' . $mediumFilename;
        Storage::disk('local')->put($mediumPath, (string) $mediumImage->encode());

        // Check cover fallback
        $album = $this->photo->album;
        if (is_null($album->cover_image_id)) {
            $album->update(['cover_image_id' => $this->photo->id]);
        }

        $this->photo->update(['status' => 'completed']);
    }

    public function failed(\Throwable $exception)
    {
        $this->photo->update(['status' => 'failed']);
    }
}
