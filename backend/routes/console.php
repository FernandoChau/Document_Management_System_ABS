<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('photos:regenerate', function () {
    $this->info('Regenerating thumbnails and medium images for existing photos...');
    $photos = \App\Models\Photo::where('status', 'completed')->get();
    $count = 0;
    foreach ($photos as $photo) {
        $this->info("Processing photo ID {$photo->id}: {$photo->original_filename}");
        if (\App\Services\ImageProcessingService::processImage($photo)) {
            $count++;
        }
    }
    $this->info("Completed! Regenerated {$count} of " . count($photos) . " photos.");
})->purpose('Regenerate thumbnails and medium images for completed photos');
