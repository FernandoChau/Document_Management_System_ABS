<?php

namespace App\Services;

use App\Models\User;
use App\Models\Folder;
use App\Models\Document;
use App\Exceptions\PermissionDeniedException;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * FileOperationService - Centraliza todas operações de arquivo
 * com validação de permissões integrada
 */
class FileOperationService
{
    protected $permissionValidator;
    protected $auditService;
    protected $zipBuilder;

    public function __construct(
        PermissionValidator $permissionValidator,
        AuditService $auditService,
        ZipBuilder $zipBuilder
    ) {
        $this->permissionValidator = $permissionValidator;
        $this->auditService = $auditService;
        $this->zipBuilder = $zipBuilder;
    }

    /**
     * Download folder as ZIP file
     * 
     * @throws PermissionDeniedException if user lacks can_download permission
     */
    public function downloadFolder(User $user, Folder $folder): StreamedResponse
    {
        // Validar permissão de download
        $result = $this->permissionValidator->validateFolderAction($user, $folder, 'download');
        $permissions = $result['permissions'] ?? $result;
        
        if (!$permissions['can_download']) {
            throw new PermissionDeniedException('Você não tem permissão para fazer download desta pasta.');
        }

        // Log the download
        $this->auditService->logDownload($user, $folder, [
            'type' => 'folder_zip',
        ]);

        // Build ZIP with ancestors
        $zipPath = $this->zipBuilder->buildFolderZip($folder, true);

        return $this->streamZip($zipPath, "pasta_{$folder->name}.zip");
    }

    /**
     * Download document as individual file
     * 
     * @throws PermissionDeniedException if user lacks can_download permission
     */
    public function downloadDocument(User $user, Document $document): StreamedResponse
    {
        // Validar permissão de download
        $result = $this->permissionValidator->validateDocumentAction($user, $document, 'download');
        $permissions = $result['permissions'] ?? $result;
        
        if (!$permissions['can_download']) {
            throw new PermissionDeniedException('Você não tem permissão para fazer download deste documento.');
        }

        // Log the download
        $this->auditService->logDownload($user, $document);

        // Check if file exists
        if (!Storage::disk('private')->exists($document->file_path)) {
            throw new \Exception("Arquivo não encontrado: {$document->file_path}");
        }

        // Stream file
        return $this->streamFile(
            $document->file_path,
            $document->name,
            $document->mime_type
        );
    }

    /**
     * Soft delete folder (move to trash)
     * 
     * @throws PermissionDeniedException if user lacks can_delete permission
     */
    public function deleteFolderTree(User $user, Folder $folder): void
    {
        // Validar permissão de delete
        $permissions = $this->permissionValidator->validateFolderAction($user, $folder, 'delete');
        
        if (!$permissions['can_delete']) {
            throw new PermissionDeniedException('Você não tem permissão para deletar esta pasta.');
        }

        // Perform soft delete
        $folder->delete();

        // Log the soft delete
        $this->auditService->logSoftDelete($user, $folder, [
            'type' => 'folder_tree',
            'deleted_at' => $folder->deleted_at,
        ]);
    }

    /**
     * Soft delete document (move to trash)
     * 
     * @throws PermissionDeniedException if user lacks can_delete permission
     */
    public function deleteDocument(User $user, Document $document): void
    {
        // Validar permissão de delete
        $permissions = $this->permissionValidator->validateDocumentAction($user, $document, 'delete');
        
        if (!$permissions['can_delete']) {
            throw new PermissionDeniedException('Você não tem permissão para deletar este documento.');
        }

        // Perform soft delete
        $document->delete();

        // Log the soft delete
        $this->auditService->logSoftDelete($user, $document);
    }

    /**
     * Permanent delete (from trash) - admin only on trashed items
     * 
     * @throws PermissionDeniedException if user is not admin or item is not trashed
     */
    public function permanentlyDeleteFolder(User $user, Folder $folder): void
    {
        // Check admin status
        if ($user->role !== 'admin') {
            throw new PermissionDeniedException('Apenas administradores podem deletar permanentemente.');
        }

        // Check if folder is trashed
        if (!$folder->trashed()) {
            throw new \Exception('Pasta deve estar na lixeira antes de deletar permanentemente.');
        }

        // Delete all documents in this folder tree permanently
        $this->deleteDocumentsInFolderTree($folder);

        // Force delete the folder
        $folder->forceDelete();

        // Log the permanent delete
        $this->auditService->logPermanentDelete($user, $folder, [
            'type' => 'folder_tree',
        ]);
    }

    /**
     * Permanent delete document (from trash) - admin only on trashed items
     * 
     * @throws PermissionDeniedException if user is not admin or document is not trashed
     */
    public function permanentlyDeleteDocument(User $user, Document $document): void
    {
        // Check admin status
        if ($user->role !== 'admin') {
            throw new PermissionDeniedException('Apenas administradores podem deletar permanentemente.');
        }

        // Check if document is trashed
        if (!$document->trashed()) {
            throw new \Exception('Documento deve estar na lixeira antes de deletar permanentemente.');
        }

        // Delete the file from storage
        if (Storage::disk('private')->exists($document->file_path)) {
            Storage::disk('private')->delete($document->file_path);
        }

        // Force delete the document
        $document->forceDelete();

        // Log the permanent delete
        $this->auditService->logPermanentDelete($user, $document, [
            'file_path' => $document->file_path,
        ]);
    }

    /**
     * Restore folder from trash
     * 
     * @throws PermissionDeniedException if folder is not trashed
     */
    public function restoreFolder(User $user, Folder $folder): void
    {
        // Check if folder is trashed
        if (!$folder->trashed()) {
            throw new \Exception('Pasta não está na lixeira.');
        }

        // Restore the folder
        $folder->restore();

        // Log the restore
        $this->auditService->logRestore($user, $folder, [
            'type' => 'folder_tree',
        ]);
    }

    /**
     * Restore document from trash
     * 
     * @throws PermissionDeniedException if document is not trashed
     */
    public function restoreDocument(User $user, Document $document): void
    {
        // Check if document is trashed
        if (!$document->trashed()) {
            throw new \Exception('Documento não está na lixeira.');
        }

        // Restore the document
        $document->restore();

        // Log the restore
        $this->auditService->logRestore($user, $document);
    }

    /**
     * Helper: Recursively delete all documents in a folder tree
     */
    private function deleteDocumentsInFolderTree(Folder $folder): void
    {
        // Delete direct documents
        $folder->documents()->forceDelete();

        // Recursively delete documents in child folders
        foreach ($folder->children()->get() as $childFolder) {
            $this->deleteDocumentsInFolderTree($childFolder);
        }
    }

    /**
     * Helper: Stream ZIP file to client
     */
    private function streamZip(string $zipPath, string $filename): StreamedResponse
    {
        $response = response()->streamDownload(function () use ($zipPath) {
            readfile($zipPath);
            @unlink($zipPath); // Clean up after streaming
        }, $filename, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => "attachment; filename=\"{{$filename}}\"",
        ]);

        // Add CORS headers for browser downloads
        $origin = request()->header('Origin');
        if ($origin) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Expose-Headers', 'Content-Length, Content-Disposition');
        }

        return $response;
    }

    /**
     * Helper: Stream file to client
     */
    private function streamFile(string $filePath, string $filename, string $mimeType): StreamedResponse
    {
        $response = response()->streamDownload(function () use ($filePath) {
            echo Storage::disk('private')->get($filePath);
        }, $filename, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => "attachment; filename=\"{{$filename}}\"",
        ]);

        // Add CORS headers for browser downloads
        $origin = request()->header('Origin');
        if ($origin) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Expose-Headers', 'Content-Length, Content-Disposition');
        }

        return $response;
    }
}
