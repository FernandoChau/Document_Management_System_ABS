<?php

namespace App\Services;

use App\Models\Photo;
use Illuminate\Support\Facades\Storage;
use Exception;

class ImageProcessingService
{
    /**
     * Process image synchronously - copy original to thumbnail and medium versions
     * Uses simple copying instead of complex resizing to avoid GD/Imagick dependency
     */
    public static function processImage(Photo $photo): bool
    {
        try {
            $originalPath = 'images/original/' . $photo->album_id . '/' . $photo->generated_filename;

            if (!Storage::disk('local')->exists($originalPath)) {
                $photo->update(['status' => 'failed', 'error_message' => 'Original file not found']);
                return false;
            }

            // Read original image
            $imageContent = Storage::disk('local')->get($originalPath);

            // Create thumbnail (same content, same file)
            $thumbExt = pathinfo($photo->generated_filename, PATHINFO_EXTENSION);
            $thumbFilename = pathinfo($photo->generated_filename, PATHINFO_FILENAME) . '_thumb.' . $thumbExt;
            $thumbPath = 'images/thumbnails/' . $photo->album_id . '/' . $thumbFilename;
            Storage::disk('local')->put($thumbPath, $imageContent);

            // Create medium (same content, same file)
            $mediumFilename = pathinfo($photo->generated_filename, PATHINFO_FILENAME) . '_medium.' . $thumbExt;
            $mediumPath = 'images/medium/' . $photo->album_id . '/' . $mediumFilename;
            Storage::disk('local')->put($mediumPath, $imageContent);

            // Update album cover if it doesn't have one
            $album = $photo->album;
            if (is_null($album->cover_image_id)) {
                $album->update(['cover_image_id' => $photo->id]);
            }

            // Mark as completed
            $photo->update(['status' => 'completed', 'error_message' => null]);

            return true;
        } catch (Exception $e) {
            $photo->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            return false;
        }
    }
}
