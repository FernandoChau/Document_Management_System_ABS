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

            $thumbExt = pathinfo($photo->generated_filename, PATHINFO_EXTENSION);
            $thumbFilename = pathinfo($photo->generated_filename, PATHINFO_FILENAME) . '_thumb.' . $thumbExt;
            $thumbPath = 'images/thumbnails/' . $photo->album_id . '/' . $thumbFilename;

            $mediumFilename = pathinfo($photo->generated_filename, PATHINFO_FILENAME) . '_medium.' . $thumbExt;
            $mediumPath = 'images/medium/' . $photo->album_id . '/' . $mediumFilename;

            $processed = false;

            if (extension_loaded('gd')) {
                try {
                    $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                    $image = $manager->decode($imageContent);

                    // Create thumbnail (300px width, scaleDown)
                    $thumbnailImage = clone $image;
                    $thumbnailImage->scaleDown(width: 300);
                    $thumbEncoded = $thumbnailImage->encodeUsingFileExtension($thumbExt, quality: 75);
                    Storage::disk('local')->put($thumbPath, (string) $thumbEncoded);

                    // Create medium (1000px width, scaleDown)
                    $mediumImage = clone $image;
                    $mediumImage->scaleDown(width: 1000);
                    $mediumEncoded = $mediumImage->encodeUsingFileExtension($thumbExt, quality: 80);
                    Storage::disk('local')->put($mediumPath, (string) $mediumEncoded);

                    $processed = true;
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Failed to resize image using GD (falling back to copying): " . $e->getMessage());
                }
            }

            if (!$processed) {
                // Create thumbnail (fallback: same content, same file)
                Storage::disk('local')->put($thumbPath, $imageContent);

                // Create medium (fallback: same content, same file)
                Storage::disk('local')->put($mediumPath, $imageContent);
            }

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
