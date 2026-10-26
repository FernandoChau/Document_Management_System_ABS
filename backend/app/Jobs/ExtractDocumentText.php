<?php

namespace App\Jobs;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ExtractDocumentText implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public Document $document)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (!Storage::disk('private')->exists($this->document->file_path)) {
                throw new \RuntimeException("Document file not found: {$this->document->id}");
            }

            if ($this->document->mime_type !== 'text/plain') {
                $this->document->content()->updateOrCreate(
                    ['document_id' => $this->document->id],
                    [
                        'extracted_text' => null,
                        'extraction_status' => 'failed',
                    ]
                );

                return;
            }

            $stream = Storage::disk('private')->readStream($this->document->file_path);
            if ($stream === false) {
                throw new \RuntimeException("Unable to read document: {$this->document->id}");
            }

            try {
                $text = stream_get_contents($stream);
            } finally {
                fclose($stream);
            }

            if ($text === false) {
                throw new \RuntimeException("Unable to extract document text: {$this->document->id}");
            }

            $this->document->content()->updateOrCreate(
                ['document_id' => $this->document->id],
                [
                    'extracted_text' => $text,
                    'extraction_status' => 'completed',
                ]
            );
        } catch (Throwable $exception) {
            Log::error('Document text extraction failed.', [
                'document_id' => $this->document->id,
                'exception' => $exception,
            ]);

            $this->document->content()->updateOrCreate(
                ['document_id' => $this->document->id],
                [
                    'extraction_status' => 'failed',
                    'extracted_text' => null,
                ]
            );
        }
    }
}
