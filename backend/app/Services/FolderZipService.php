<?php

namespace App\Services;

use App\Models\Folder;
use ZipArchive;
use Illuminate\Support\Str;

class FolderZipService
{
    /**
     * Criar um arquivo ZIP com toda a estrutura de pastas e ficheiros baseada no banco de dados
     * A estrutura do ZIP é determinada pela tabela Folders (hierarquia)
     * Os ficheiros são adicionados da tabela Documents usando o file_path armazenado
     *
     * @param Folder $folder Pasta raiz a compactar
     * @return string Caminho do arquivo ZIP criado
     */
    public function createZip(Folder $folder): string
    {
        $zipFileName = 'folder_' . $folder->name . '_' . Str::random(8) . '.zip';
        $tempDir = $this->getTempDir();
        $zipPath = $tempDir . DIRECTORY_SEPARATOR . $zipFileName;

        // Validar e criar diretório temp
        if (!is_dir($tempDir)) {
            if (!@mkdir($tempDir, 0755, true)) {
                throw new \Exception("Não foi possível criar diretório temp: {$tempDir}");
            }
        }

        if (!is_writable($tempDir)) {
            throw new \Exception("Diretório não é gravável: {$tempDir}");
        }

        // Abrir o arquivo ZIP
        $zip = new ZipArchive();
        $openResult = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($openResult !== true) {
            $errorMsg = $this->getZipError($openResult);
            throw new \Exception("Erro ao criar ZIP: {$errorMsg}");
        }

        try {
            // Adicionar conteúdo: pastas do banco + ficheiros de cada pasta
            $this->addFolderContentsToZip($zip, $folder, '');

            // Fechar ZIP
            if (!$zip->close()) {
                throw new \Exception('Erro ao fechar arquivo ZIP');
            }

            // Aguardar escrita completa no disco
            usleep(300000); // 0.3 segundos

            // Validar que arquivo foi criado e não está vazio
            if (!file_exists($zipPath)) {
                throw new \Exception("Arquivo ZIP não foi criado no caminho: {$zipPath}");
            }

            if (filesize($zipPath) == 0) {
                unlink($zipPath);
                throw new \Exception("Arquivo ZIP está vazio");
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
     * Adiciona conteúdo de uma pasta ao ZIP
     * Processa: DocumentosDiretos + SubPastas + SeuDocumentos
     *
     * @param ZipArchive $zip
     * @param Folder $folder
     * @param string $parentPath Caminho da pasta pai no ZIP
     */
    private function addFolderContentsToZip(ZipArchive $zip, Folder $folder, string $parentPath): void
    {
        // Construir caminho desta pasta no ZIP
        $folderPath = $parentPath ? $parentPath . '/' . $folder->name : $folder->name;

        // Criar entrada de diretório no ZIP
        if (!$zip->addEmptyDir($folderPath)) {
            // Se falhar a criação do dir, continua (pode já existir)
        }

        // PASSO 1: Adicionar todos os documentos DIRETOS desta pasta
        $documents = $folder->documents()->get();
        foreach ($documents as $document) {
            try {
                $filePath = storage_path('app' . DIRECTORY_SEPARATOR . $document->file_path);

                // Caminho do ficheiro dentro do ZIP
                $fileInZip = $folderPath . '/' . $document->name;

                if (file_exists($filePath)) {
                    // Adicionar o ficheiro ao ZIP
                    if (!@$zip->addFile($filePath, $fileInZip)) {
                        // Se falhar, tenta ler conteúdo e adicionar como string
                        @$zip->addFromString($fileInZip, file_get_contents($filePath));
                    }
                }
            } catch (\Exception $e) {
                // Continua com próximo documento se um falhar
                continue;
            }
        }

        // PASSO 2: Recursivamente processar sub-pastas
        $children = $folder->children()->get();
        foreach ($children as $subfolder) {
            $this->addFolderContentsToZip($zip, $subfolder, $folderPath);
        }
    }

    /**
     * Obter caminho do diretório temporário (sempre com DIRECTORY_SEPARATOR)
     */
    private function getTempDir(): string
    {
        return storage_path('app') . DIRECTORY_SEPARATOR . 'temp';
    }

    /**
     * Traduzir código de erro do ZipArchive
     */
    private function getZipError(int $code): string
    {
        $errors = [
            ZipArchive::ER_OK => 'Sem erro',
            ZipArchive::ER_MULTIDISK => 'Multi-disk não suportado',
            ZipArchive::ER_RENAME => 'Erro ao renomear arquivo temporário',
            ZipArchive::ER_CLOSE => 'Erro ao fechar arquivo',
            ZipArchive::ER_SEEK => 'Erro de seek',
            ZipArchive::ER_READ => 'Erro de read',
            ZipArchive::ER_WRITE => 'Erro de write',
            ZipArchive::ER_CRC => 'Erro de CRC',
            ZipArchive::ER_ZIPCLOSED => 'ZIP já foi fechado',
            ZipArchive::ER_NOENT => 'Arquivo não encontrado',
            ZipArchive::ER_EXISTS => 'Arquivo já existe',
            ZipArchive::ER_OPEN => 'Não foi possível abrir arquivo',
            ZipArchive::ER_TMPOPEN => 'Não foi possível criar arquivo temporário',
            ZipArchive::ER_ZLIB => 'Erro de compressão',
            ZipArchive::ER_MEMORY => 'Erro de alocação de memória',
        ];

        return $errors[$code] ?? "Código de erro desconhecido: {$code}";
    }

    /**
     * Obter nome do arquivo ZIP para download
     */
    public function getZipDownloadName(Folder $folder): string
    {
        return $folder->name . '.zip';
    }

    /**
     * Limpar ZIPs temporários com mais de N horas
     */
    public function cleanOldZips(int $hours = 1): int
    {
        $tempDir = $this->getTempDir();

        if (!is_dir($tempDir)) {
            return 0;
        }

        $pattern = $tempDir . DIRECTORY_SEPARATOR . 'folder_*.zip';
        $files = @glob($pattern);
        $timeLimit = time() - ($hours * 60 * 60);
        $deleted = 0;

        foreach ($files ?? [] as $file) {
            try {
                if (@filemtime($file) < $timeLimit && is_file($file)) {
                    if (@unlink($file)) {
                        $deleted++;
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $deleted;
    }
}
