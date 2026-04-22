<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Document;
use App\Services\AuthorizationService;
use App\Services\DocumentValidator;
use App\Services\FolderValidator;

class DocumentPolicy
{
    protected AuthorizationService $authorizationService;
    protected DocumentValidator $documentValidator;
    protected FolderValidator $folderValidator;

    public function __construct(
        AuthorizationService $authorizationService,
        DocumentValidator $documentValidator,
        FolderValidator $folderValidator
    ) {
        $this->authorizationService = $authorizationService;
        $this->documentValidator = $documentValidator;
        $this->folderValidator = $folderValidator;
    }

    /**
     * Determine if user can view the document
     */
    public function view(User $user, Document $document): bool
    {
        // Guard: Check document is not deleted
        if ($this->isDeleted($document)) {
            return false;
        }

        // Guard: Check parent folder exists and is not deleted
        if (!$document->folder || $document->folder->trashed()) {
            return false;
        }

        // Guard: Check ancestor chain integrity
        try {
            $this->folderValidator->validateAncestorChainIntegrity($document->folder);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        return $this->authorizationService->canViewDocument($user, $document);
    }

    /**
     * Determine if user can download the document
     */
    public function download(User $user, Document $document): bool
    {
        // Guard: Check document is not deleted
        if ($this->isDeleted($document)) {
            return false;
        }

        // Guard: Check parent folder exists and is not deleted
        if (!$document->folder || $document->folder->trashed()) {
            return false;
        }

        // Guard: Check ancestor chain integrity
        try {
            $this->folderValidator->validateAncestorChainIntegrity($document->folder);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        return $this->authorizationService->canDownloadDocument($user, $document);
    }

    /**
     * Determine if user can delete the document
     */
    public function delete(User $user, Document $document): bool
    {
        // Guard: Check document is not already deleted
        if ($this->isDeleted($document)) {
            return false;
        }

        // Guard: Check parent folder exists
        if (!$document->folder) {
            return false;
        }

        return $this->authorizationService->canDeleteDocument($user, $document);
    }

    /**
     * Determine if user can restore the document
     */
    public function restore(User $user, Document $document): bool
    {
        // Guard: Only admins can restore
        if (!$user->isAdmin()) {
            return false;
        }

        // Guard: Document must be deleted
        if (!$this->isDeleted($document)) {
            return false;
        }

        // Guard: Parent folder must exist and not be deleted
        if (!$document->folder || $document->folder->trashed()) {
            return false;
        }

        return true;
    }

    /**
     * Determine if user can permanently delete the document
     */
    public function forceDelete(User $user, Document $document): bool
    {
        // Guard: Only admins can force delete
        if (!$user->isAdmin()) {
            return false;
        }

        // Guard: Document must be deleted
        if (!$this->isDeleted($document)) {
            return false;
        }

        return true;
    }

    /**
     * Determine if user can share the document
     */
    public function share(User $user, Document $document): bool
    {
        // Guard: Check document is not deleted
        if ($this->isDeleted($document)) {
            return false;
        }

        // Guard: Check parent folder exists and is not deleted
        if (!$document->folder || $document->folder->trashed()) {
            return false;
        }

        return $this->authorizationService->canShareDocument($user, $document);
    }

    /**
     * Determine if user can update document metadata
     */
    public function updateMetadata(User $user, Document $document): bool
    {
        // Guard: Check document is not deleted
        if ($this->isDeleted($document)) {
            return false;
        }

        // Guard: Check parent folder exists
        if (!$document->folder) {
            return false;
        }

        return $this->authorizationService->canUpdateDocumentMetadata($user, $document);
    }

    /**
     * Check if document is deleted (soft delete)
     */
    protected function isDeleted(Document $document): bool
    {
        return $document->trashed();
    }
}
