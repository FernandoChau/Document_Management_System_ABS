<?php

namespace App\Services;

use App\Jobs\ExtractDocumentText;
use App\Models\Document;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class DocumentService
{
    /**
     * Upload a new file and create a document record.
     *
     * @param Folder $folder
     * @param UploadedFile $file
     * @param User $uploader
     * @return Document
     */
    public function uploadFile(Folder $folder, UploadedFile $file, User $uploader): Document
    {
        $path = $file->store('documents', 'private');

        if (!$path) {
            throw new \RuntimeException('Unable to store uploaded document.');
        }

        try {
            $document = DB::transaction(function () use ($folder, $file, $uploader, $path) {
                Folder::query()->whereKey($folder->id)->lockForUpdate()->firstOrFail();
                $year = now()->year;

                $lastDoc = Document::withTrashed()
                    ->where('folder_id', $folder->id)
                    ->where('year', $year)
                    ->lockForUpdate()
                    ->orderBy('sequence_number', 'desc')
                    ->first();

                $sequence = $lastDoc ? $lastDoc->sequence_number + 1 : 1;

                $shortYear = substr($year, -2);
                $paddedSequence = str_pad($sequence, 3, '0', STR_PAD_LEFT);
                $cleanFilename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

                $referenceCode = sprintf(
                    '%s.%s.%s.%s',
                    $folder->reference_code,
                    $shortYear,
                    $paddedSequence,
                    $cleanFilename
                );

                $document = Document::create([
                    'folder_id' => $folder->id,
                    'name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'reference_code' => $referenceCode,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'year' => $year,
                    'sequence_number' => $sequence,
                    'user_id' => $uploader->id,
                ]);

                $document->permissions()->create([
                    'user_id' => $uploader->id,
                    'can_view' => true,
                    'can_update_metadata' => true,
                    'can_delete' => true,
                    'can_download' => true,
                    'can_share' => true,
                    'can_manage_permissions' => true,
                ]);

                return $document;
            });
        } catch (Throwable $exception) {
            if (Storage::disk('private')->exists($path) && !Storage::disk('private')->delete($path)) {
                report(new \RuntimeException("Failed to clean up uploaded file after database failure: {$path}"));
            }

            throw $exception;
        }

        ExtractDocumentText::dispatch($document);

        return $document;
    }

    /**
     * Upload a new file to root (without folder) and create a document record.
     *
     * @param UploadedFile $file
     * @param User $uploader
     * @return Document
     */
    public function uploadFileToRoot(UploadedFile $file, User $uploader): Document
    {
        return DB::transaction(function () use ($file, $uploader) {
            $year = now()->year;
            
            // 1. Calculate Sequence Number (global sequence for root documents)
            $lastDoc = Document::whereNull('folder_id')
                ->where('year', $year)
                ->lockForUpdate()
                ->orderBy('sequence_number', 'desc')
                ->first();
                
            $sequence = $lastDoc ? $lastDoc->sequence_number + 1 : 1;
            
            // 2. Generate Reference Code (RaizY2.SEQ.filename)
            $shortYear = substr($year, -2);
            $paddedSequence = str_pad($sequence, 3, '0', STR_PAD_LEFT);
            $cleanFilename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            
            $referenceCode = sprintf(
                'Raiz.%s.%s.%s',
                $shortYear,
                $paddedSequence,
                $cleanFilename
            );
            
            // 3. Store File Physically
            $path = $file->store('documents', 'private');
            
            // 4. Create Document Record (without folder_id)
            $document = Document::create([
                'folder_id' => null, // Root document (no folder)
                'name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'reference_code' => $referenceCode,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'year' => $year,
                'sequence_number' => $sequence,
                'user_id' => $uploader->id,
            ]);
            
            return $document;
        });
    }
}
