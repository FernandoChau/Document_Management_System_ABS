<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Folder;

class DocumentValidator
{
    /**
     * Allowed MIME types for documents
     */
    private const ALLOWED_MIME_TYPES = [
        // Documents
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        'text/csv',
        
        // Images
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/tiff',
        
        // Archives
        'application/zip',
        'application/x-rar-compressed',
        'application/x-7z-compressed',
        'application/x-tar',
        'application/gzip',
        
        // Other
        'application/json',
        'text/xml',
        'application/xml',
    ];

    /**
     * Maximum file size in bytes (100MB)
     */
    private const MAX_FILE_SIZE = 100 * 1024 * 1024;

    /**
     * Dangerous MIME types that should be blocked
     */
    private const BLOCKED_MIME_TYPES = [
        'application/x-executable',
        'application/x-msdownload',
        'application/x-msdos-program',
        'application/x-dosexec',
        'application/x-elf',
        'application/x-sharedlib',
    ];

    /**
     * Validate document data before create/update
     * 
     * @param array $data
     * @param Document|null $existingDocument (for updates)
     * @return array
     * @throws \InvalidArgumentException
     */
    public function validateDocumentData(array $data, ?Document $existingDocument = null): array
    {
        // Validate: folder_id must be set and not null
        if (!isset($data['folder_id']) || is_null($data['folder_id'])) {
            throw new \InvalidArgumentException('Documents must have a parent folder (folder_id is required).');
        }

        // Validate: folder exists
        $folder = Folder::find($data['folder_id']);
        if (!$folder) {
            throw new \InvalidArgumentException('Parent folder does not exist.');
        }

        // Validate: parent folder is not soft-deleted
        if ($folder->trashed()) {
            throw new \InvalidArgumentException('Cannot add documents to a deleted folder.');
        }

        // Validate: name is not empty
        if (isset($data['name']) && empty(trim($data['name']))) {
            throw new \InvalidArgumentException('Document name cannot be empty.');
        }

        // Validate: mime type validation
        if (isset($data['mime_type'])) {
            $this->validateMimeType($data['mime_type']);
        }

        // Validate: size is positive if provided
        if (isset($data['size'])) {
            $this->validateFileSize($data['size']);
        }

        return $data;
    }

    /**
     * Validate MIME type
     * 
     * @param string $mimeType
     * @return void
     * @throws \InvalidArgumentException
     */
    public function validateMimeType(string $mimeType): void
    {
        // Check if MIME type is blocked (dangerous files)
        if (in_array($mimeType, self::BLOCKED_MIME_TYPES)) {
            throw new \InvalidArgumentException('This file type is not allowed for security reasons.');
        }

        // Check if MIME type is allowed (whitelist approach)
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            throw new \InvalidArgumentException("MIME type '{$mimeType}' is not allowed. Allowed types: " . implode(', ', self::ALLOWED_MIME_TYPES));
        }
    }

    /**
     * Validate file size
     * 
     * @param int $size (in bytes)
     * @return void
     * @throws \InvalidArgumentException
     */
    public function validateFileSize(int $size): void
    {
        if ($size < 0) {
            throw new \InvalidArgumentException('Document size must be positive.');
        }

        if ($size == 0) {
            throw new \InvalidArgumentException('Document size cannot be zero.');
        }

        if ($size > self::MAX_FILE_SIZE) {
            $maxSizeMB = self::MAX_FILE_SIZE / (1024 * 1024);
            throw new \InvalidArgumentException("Document size exceeds maximum allowed ({$maxSizeMB}MB).");
        }
    }

    /**
     * Validate that document is not in root folder
     * Documents cannot be uploaded directly to root
     * 
     * @param Folder $folder
     * @return void
     * @throws \InvalidArgumentException
     */
    public function validateNotInRoot(Folder $folder): void
    {
        if ($folder->is_root) {
            throw new \InvalidArgumentException('Documents cannot be uploaded directly to the root folder.');
        }
    }

    /**
     * Validate document exists and is not deleted
     * 
     * @param Document|null $document
     * @return void
     * @throws \InvalidArgumentException
     */
    public function validateDocumentExists(?Document $document): void
    {
        if (!$document) {
            throw new \InvalidArgumentException('Document not found.');
        }

        if ($document->trashed()) {
            throw new \InvalidArgumentException('Document has been deleted.');
        }
    }

    /**
     * Get list of allowed MIME types
     * 
     * @return array
     */
    public static function getAllowedMimeTypes(): array
    {
        return self::ALLOWED_MIME_TYPES;
    }

    /**
     * Get maximum file size in bytes
     * 
     * @return int
     */
    public static function getMaxFileSize(): int
    {
        return self::MAX_FILE_SIZE;
    }

    /**
     * Get maximum file size in MB
     * 
     * @return float
     */
    public static function getMaxFileSizeMB(): float
    {
        return self::MAX_FILE_SIZE / (1024 * 1024);
    }
}
