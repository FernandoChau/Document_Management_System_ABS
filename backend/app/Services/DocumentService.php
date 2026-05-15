<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        return DB::transaction(function () use ($folder, $file, $uploader) {
            $year = now()->year;
            
            // 1. Calculate Sequence Number (Atomic Lock needed in high concurrency, using pessimistic lock here)
            // We lock the folder row or use a separate counter table. 
            // For simplicity in this tailored solution, we will query with lockForUpdate on the latest document of this folder/year.
            // Note: In high traffic, a dedicated unique sequence generator usually better, but this suffices for typical DMS.
            
            $lastDoc = Document::where('folder_id', $folder->id)
                ->where('year', $year)
                ->lockForUpdate()
                ->orderBy('sequence_number', 'desc')
                ->first();
                
            $sequence = $lastDoc ? $lastDoc->sequence_number + 1 : 1;
            
            // 2. Generate Reference Code
            // Format: folder_ref . YY . SEQ . filename
            // Example: "dt.t" . "." . "26" . "." . "001" . "." . "file.txt" => "dt.t.26.001.file.txt"
            
            // Short year: 2026 -> 26
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
            
            // 3. Store File Physically
            // Storing securely in 'documents' folder within 'private' disk (storage/app/documents)
            // Using hash name to avoid storage conflicts, but we assume file_path stores valid retrieval path
            $path = $file->store('documents', 'local'); 
            
            // 4. Create Document Record
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

            // 5. Create explicit document permissions for the uploader.
            // This makes the uploader the explicit owner of the document,
            // similar to Linux file ownership.
            $document->permissions()->create([
                'user_id' => $uploader->id,
                'can_view' => true,
                'can_update_metadata' => true,
                'can_delete' => true,
                'can_download' => true,
                'can_share' => true,
                'can_manage_permissions' => true,
            ]);
            
            // 6. Trigger Async Extraction (Dispatch Job)
            // ExtractDocumentTextJob::dispatch($document); // To be implemented
            
            return $document;
        });
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
            $path = $file->store('documents', 'local');
            
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
