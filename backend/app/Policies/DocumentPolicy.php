<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Document;
use App\Services\AuthorizationService;

class DocumentPolicy
{
    protected AuthorizationService $authorizationService;

    public function __construct(AuthorizationService $authorizationService)
    {
        $this->authorizationService = $authorizationService;
    }

    /**
     * Determine if user can view the document
     */
    public function view(User $user, Document $document): bool
    {
        return $this->authorizationService->canViewDocument($user, $document);
    }

    /**
     * Determine if user can download the document
     */
    public function download(User $user, Document $document): bool
    {
        return $this->authorizationService->canDownloadDocument($user, $document);
    }

    /**
     * Determine if user can delete the document
     */
    public function delete(User $user, Document $document): bool
    {
        return $this->authorizationService->canDeleteDocument($user, $document);
    }

    /**
     * Determine if user can restore the document
     */
    public function restore(User $user, Document $document): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if user can permanently delete the document
     */
    public function forceDelete(User $user, Document $document): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if user can share the document
     */
    public function share(User $user, Document $document): bool
    {
        return $this->authorizationService->canShareDocument($user, $document);
    }

    /**
     * Determine if user can update document metadata
     */
    public function updateMetadata(User $user, Document $document): bool
    {
        return $this->authorizationService->canUpdateDocumentMetadata($user, $document);
    }
}
