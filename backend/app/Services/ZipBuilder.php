<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\Document;
use ZipArchive;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

/**
 * ZipBuilder - Constrói arquivos ZIP preservando estrutura de pastas e hierarquia de ancestors
 * 
 * Estrutura de exemplo:
 * ancestor1/
 * ├── ancestor2/
 * │   └── parent_folder/
 * │       ├── sub_folder/
 * │       │   └── document.pdf
 * │       └── another_doc.docx
 */
class ZipBuilder
{
    /**
     * Build ZIP file for a folder with optional ancestor hierarchy
     */
    public function buildFolderZip(Folder $folder, bool $includeAncestors = true): string
    {
        $zipPath = $this->createTempZip();
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Erro ao criar arquivo ZIP");
        }

        try {
            // Get ancestor hierarchy
            $ancestors = $this->getAncestors($folder);
            $basePath = '';

            // Build ancestor path in ZIP
            if ($includeAncestors && !empty($ancestors)) {
                foreach ($ancestors as $ancestor) {
                    $basePath .= $ancestor->name . '/';
                    $zip->addEmptyDir($basePath);
                }
            }

            // Add folder and its contents
            $this->addFolderToZip($zip, $folder, $basePath);

            $zip->close();

            // Validate ZIP
            if (filesize($zipPath) === 0) {
                unlink($zipPath);
                throw new \Exception("ZIP arquivo criado está vazio");
            }

            return $zipPath;
        } catch (\Exception $e) {
            $zip->close();
            if (file_exists($zipPath)) {
                @unlink($zipPath);
            }
            throw $e;
        }
    }

    /**
     * Build ZIP file for a document with optional ancestor hierarchy
     */
    public function buildDocumentZip(Document $document, bool $includeAncestors = true): string
    {
        $zipPath = $this->createTempZip();
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Erro ao criar arquivo ZIP");
        }

        try {
            // Get folder and its ancestors
            $folder = $document->folder;
            $ancestors = $this->getAncestors($folder);
            $basePath = '';

            // Build ancestor path in ZIP
            if ($includeAncestors && !empty($ancestors)) {
                foreach ($ancestors as $ancestor) {
                    $basePath .= $ancestor->name . '/';
                    $zip->addEmptyDir($basePath);
                }
            }

            // Add folder path
            $basePath .= $folder->name . '/';
            $zip->addEmptyDir($basePath);

            // Add the document file
            $this->addDocumentToZip($zip, $document, $basePath);

            $zip->close();

            // Validate ZIP
            if (filesize($zipPath) === 0) {
                unlink($zipPath);
                throw new \Exception("ZIP arquivo criado está vazio");
            }

            return $zipPath;
        } catch (\Exception $e) {
            $zip->close();
            if (file_exists($zipPath)) {
                @unlink($zipPath);
            }
            throw $e;
        }
    }

    /**
     * Get temporary ZIP file path
     */
    public function getZipPath(): string
    {
        return storage_path('app/temp/zips');
    }

    /**
     * Helper: Create temporary ZIP file
     */
    private function createTempZip(): string
    {
        $tempDir = $this->getZipPath();

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipFileName = 'zip_' . Str::random(16) . '.zip';
        return $tempDir . '/' . $zipFileName;
    }

    /**
     * Helper: Add folder and contents recursively to ZIP
     */
    private function addFolderToZip(ZipArchive $zip, Folder $folder, string $basePath): void
    {
        // Create folder entry
        $folderPath = $basePath . $folder->name;
        $zip->addEmptyDir($folderPath);

        // Add all documents in this folder
        foreach ($folder->documents()->get() as $document) {
            $this->addDocumentToZip($zip, $document, $folderPath . '/');
        }

        // Recursively add child folders
        foreach ($folder->children()->get() as $childFolder) {
            $this->addFolderToZip($zip, $childFolder, $folderPath . '/');
        }
    }

    /**
     * Helper: Add document file to ZIP
     */
    private function addDocumentToZip(ZipArchive $zip, Document $document, string $basePath): void
    {
        try {
            $filePath = Storage::disk('private')->path($document->file_path);

            if (file_exists($filePath)) {
                $zip->addFile($filePath, $basePath . $document->name);
            }
        } catch (\Exception $e) {
            // Log error but continue with other files
            // \Log::warning("Failed to add document to ZIP: {$document->name}", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Helper: Get ancestor folders from child to root
     * Returns array: [parent, grandparent, great-grandparent, ..., root]
     */
    private function getAncestors(Folder $folder): array
    {
        $ancestors = [];
        $current = $folder;

        // Traverse up to root
        while ($current->parent_id) {
            $current = $current->parent;
            $ancestors[] = $current;
        }

        // Reverse to get root-first order
        return array_reverse($ancestors);
    }
}
