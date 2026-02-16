<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ExtractDocumentText implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected $document;

    /**
     * Create a new job instance.
     */
    public function __construct(\App\Models\Document $document)
    {
        $this->document = $document;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $path = storage_path('app/' . $this->document->file_path);
        $mime = $this->document->mime_type;
        $text = '';
        $status = 'failed';

        try {
            if (!file_exists($path)) {
                throw new \Exception("File not found at $path");
            }

            // Simple extraction logic (Expandable)
            if (str_contains($mime, 'text/plain')) {
                $text = file_get_contents($path);
                $status = 'completed';
            } elseif (str_contains($mime, 'pdf')) {
                // Placeholder for PDF parser (e.g. Spatie\PdfToText)
                // $text = (new Pdf())->setPdf($path)->text();
                $text = "[PDF Content Placeholder - Install spatie/pdf-to-text]";
                $status = 'completed';
            } else {
                $text = "[Binary or Unsupported Content]";
                $status = 'completed'; // Marked complete but empty
            }

            // Save to DB
            $this->document->content()->updateOrCreate(
                ['document_id' => $this->document->id],
                [
                    'extracted_text' => $text,
                    'extraction_status' => $status,
                ]
            );
            
        } catch (\Exception $e) {
             $this->document->content()->updateOrCreate(
                ['document_id' => $this->document->id],
                [
                    'extraction_status' => 'failed',
                    'extracted_text' => "Error: " . $e->getMessage()
                ]
            );
        }
    }
}
