<?php

namespace App\Services;

use App\Models\Folder;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class FolderZipper
{
    public function downloadZip(Folder $rootFolder, ?string $zipBaseName = null)
    {
        $zipBaseName = $zipBaseName ?: $rootFolder->name;
        $tmpDir = storage_path('app/temp');
        if (!is_dir($tmpDir))
            mkdir($tmpDir, 0775, true);

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
        $zip->addEmptyDir($basePath);

        foreach ($folder->files as $file) {

            // 📥 1. Busca o conteúdo do ficheiro no Wasabi
            $content = Storage::disk('wasabi')->get($file->path);

            // 📁 2. Cria caminho temporário local
            $tempDir = storage_path('app/temp_zip');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0777, true);
            }

            // 📝 3. Salva o ficheiro temporariamente
            $tempFilePath = $tempDir . '/' . $file->name;
            file_put_contents($tempFilePath, $content);

            // 📦 4. Adiciona ao ZIP a partir do caminho local
            $zip->addFile($tempFilePath, $basePath . $file->name);
        }

        // 📁 5. Repete o processo para subpastas
        foreach ($folder->children as $child) {
            $this->addFolderToZip($zip, $child, $basePath . $child->name . '/');
        }
    }


    private function loadTree(Folder $folder): void
    {
        $folder->loadMissing(['files', 'children']);
        foreach ($folder->children as $c)
            $this->loadTree($c);
    }

    private function slug(string $name): string
    {
        return preg_replace('~[^A-Za-z0-9_\-]+~', '-', $name);
    }
}
