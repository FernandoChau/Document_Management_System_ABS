<?php

namespace App\Services;

use App\Models\Folder;
use ZipArchive;

class FolderZipper
{
    public function downloadZip(Folder $rootFolder, ?string $zipBaseName = null)
    {
        $zipBaseName = $zipBaseName ?: $rootFolder->name;
        $tmpDir = storage_path('app/temp');
        if (!is_dir($tmpDir)) mkdir($tmpDir, 0775, true);
        
        $zipPath = $tmpDir . '/' . $this->slug($zipBaseName) . '.zip';
        
        $this->loadTree($rootFolder); // evita N+1
        
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Não foi possível criar o ZIP.');
        }
        
        $this->addFolderToZip($zip, $rootFolder, $rootFolder->name . '/');
        
        $zip->close();

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    private function addFolderToZip(ZipArchive $zip, Folder $folder, string $basePath)
    {
        // Garante a pasta no zip
        $zip->addEmptyDir($basePath);

        foreach ($folder->files as $file) {
            $full = storage_path('app/' . $file->path); // ajuste se usar outro disk
            if (is_file($full)) {
                $zip->addFile($full, $basePath . $file->name);
            }
        }

        foreach ($folder->children as $child) {
            $this->addFolderToZip($zip, $child, $basePath . $child->name . '/');
        }
    }

    private function loadTree(Folder $folder): void
    {
        $folder->loadMissing(['files', 'children']);
        foreach ($folder->children as $c) $this->loadTree($c);
    }

    private function slug(string $name): string
    {
        return preg_replace('~[^A-Za-z0-9_\-]+~', '-', $name);
    }
}
