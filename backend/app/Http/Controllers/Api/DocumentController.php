<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\Document;
use App\Models\AuditLog;
use App\Models\ShareLink;
use App\Services\DocumentService;
use App\Services\FileOperationService;
use App\Services\AuditService;
use App\Services\AuditLogger;
use App\Services\AuthorizationService;
use App\Services\PermissionValidator;
use App\Services\StructureValidator;
use App\Services\DocumentValidator;
use App\Services\FolderValidator;
use App\Exceptions\PermissionDeniedException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DocumentController extends Controller
{
    protected $documentService;
    protected $fileOperationService;
    protected $auditService;
    protected $authorizationService;
    protected $permissionValidator;
    protected $structureValidator;
    protected $documentValidator;
    protected $folderValidator;

    public function __construct(
        DocumentService $documentService,
        FileOperationService $fileOperationService,
        AuditService $auditService,
        AuthorizationService $authorizationService,
        PermissionValidator $permissionValidator,
        StructureValidator $structureValidator,
        DocumentValidator $documentValidator,
        FolderValidator $folderValidator
    ) {
        $this->documentService = $documentService;
        $this->fileOperationService = $fileOperationService;
        $this->auditService = $auditService;
        $this->authorizationService = $authorizationService;
        $this->permissionValidator = $permissionValidator;
        $this->structureValidator = $structureValidator;
        $this->documentValidator = $documentValidator;
        $this->folderValidator = $folderValidator;
    }

    public function store(Request $request, Folder $folder)
    {
        $user = $request->user();

        // Batch upload support
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:51200', // 50MB max per file
        ]);

        // Validate: folder is not root (documents cannot be uploaded to root)
        if ($folder->is_root) {
            return response()->json([
                'error' => 'Não é permitido fazer upload de ficheiros em pastas raiz. Por favor, selecione uma subpasta.'
            ], 422);
        }

        // Validate: folder not deleted
        if ($folder->trashed()) {
            return response()->json(['error' => 'Esse ficheiro foi removido em cascata'], 422);
        }

        // Validate: folder ancestor chain integrity
        try {
            $this->folderValidator->validateAncestorChainIntegrity($folder);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        // Check upload permission on folder
        try {
            $this->permissionValidator->validateFolderAction($user, $folder, 'create');
        } catch (PermissionDeniedException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }

        $documents = [];

        foreach ($request->file('files') as $file) {
            try {
                // Validate document data before upload
                $this->structureValidator->validateDocumentData([
                    'folder_id' => $folder->id,
                    'name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                ]);

                // Additional validation: MIME type and file size
                $this->documentValidator->validateMimeType($file->getMimeType());
                $this->documentValidator->validateFileSize($file->getSize());

                $doc = $this->documentService->uploadFile($folder, $file, $user);
                $this->auditService->logUpload($user, $doc);
                $documents[] = $doc;
            } catch (\InvalidArgumentException $e) {
                return response()->json(['error' => 'Erro de validação: ' . $e->getMessage()], 422);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Erro ao submeter: ' . $e->getMessage()], 500);
            }
        }

        return response()->json(['message' => 'Submissão bem sucedida.', 'documents' => $documents], 201);
    }


    /**
     * Upload de ficheiros para raiz - BLOQUEADO
     * Documents cannot be uploaded to root
     */
    public function storeRoot(Request $request)
    {
        return response()->json([
            'error' => 'Documents cannot be uploaded to root folder. Please select a subfolder.'
        ], 422);
    }

    public function show(Document $document)
    {
        $user = auth()->user();

        try {
            $this->permissionValidator->validateDocumentAction($user, $document, 'view');
        } catch (PermissionDeniedException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }

        AuditLogger::log($user, 'VIEW', $document);
        
        // Return only metadata (not full content)
        return response()->json($document->getMetadata());
    }

    public function download(Document $document)
    {
        $user = auth()->user();

        try {
            return $this->fileOperationService->downloadDocument($user, $document);
        } catch (PermissionDeniedException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function destroy(Document $document)
    {
        $user = auth()->user();

        try {
            $this->fileOperationService->deleteDocument($user, $document);
            return response()->noContent();
        } catch (PermissionDeniedException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Permanently delete document (only admin, only if in trash)
     */
    public function forceDelete($id)
    {
        $user = auth()->user();

        try {
            $document = Document::withTrashed()->findOrFail($id);
            $this->fileOperationService->permanentlyDeleteDocument($user, $document);
            return response()->noContent();
        } catch (PermissionDeniedException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Share document via ShareLink
     */
    public function share(Request $request, Document $document)
    {
        $user = $request->user();

        $request->validate([
            'expires_at' => 'nullable|date|after:now',
            'password' => 'nullable|string|min:6|max:255',
        ]);

        // FIRST STEP: Validate permissions before creating share link
        try {
            $this->permissionValidator->validateDocumentAction($user, $document, 'share');
        } catch (PermissionDeniedException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }

        // Create share link
        $shareLink = ShareLink::create([
            'token' => Str::random(60),
            'shareable_type' => 'Document',
            'shareable_id' => $document->id,
            'expires_at' => $request->expires_at,
            'password' => $request->password ? hash('sha256', $request->password) : null,
            'created_by' => $user->id,
        ]);

        // Use AuditService for logging
        $this->auditService->logShare($user, $document, [
            'share_link_id' => $shareLink->id,
            'token' => $shareLink->token,
        ]);

        return response()->json([
            'token' => $shareLink->token,
            'url' => url("/api/compartilhamentos/{$shareLink->token}"),
            'expires_at' => $shareLink->expires_at,
        ], 201);
    }

    /**
     * Get audit logs for document
     */
    public function logs(Request $request, Document $document)
    {
        $user = $request->user();

        // Validate: user must have can_view
        try {
            $this->permissionValidator->validateDocumentAction($user, $document, 'view');
        } catch (PermissionDeniedException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }

        $perPage = $request->input('per_page', 15);
        
        $logs = AuditLog::where('resource_type', 'Document')
            ->where('resource_id', $document->id)
            ->with('user')
            ->latest('created_at')
            ->paginate($perPage);

        return response()->json($logs);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'folder_id' => 'nullable|uuid|exists:folders,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Document::query();

        if ($request->filled('folder_id')) {
            $folder = Folder::findOrFail($request->folder_id);
            
            // Validate: user must have can_view on folder
            try {
                $this->permissionValidator->validateFolderAction($user, $folder, 'view');
            } catch (PermissionDeniedException $e) {
                return response()->json(['error' => $e->getMessage()], 403);
            }

            $userGroupIds = $user->groups()->pluck('groups.id')->toArray();

            $query->where('folder_id', $request->folder_id)
                ->where(function ($query) use ($user, $userGroupIds) {
                    $query->where('user_id', $user->id)
                        ->orWhereHas('permissions', function ($permissionQuery) use ($user, $userGroupIds) {
                            $permissionQuery->where('can_view', true)
                                ->where(function ($innerQuery) use ($user, $userGroupIds) {
                                    $innerQuery->where('user_id', $user->id);

                                    if (!empty($userGroupIds)) {
                                        $innerQuery->orWhereIn('group_id', $userGroupIds);
                                    }
                                });
                        });
                });
        } else {
            // If no folder specified, return documents user has access to
            // This requires checking permissions on all documents
            // For now, just return empty for safety
            return response()->json(['data' => []]);
        }

        $perPage = $request->input('per_page', 15);
        $documents = $query->withMetadata()
            ->withRelations()
            ->paginate($perPage);

        // Attach permissions to each document
        foreach ($documents as $doc) {
            $doc->permissions = $this->authorizationService->resolveDocumentPermissions($user, $doc);
        }

        return response()->json($documents);
    }

    public function update(Request $request, Document $document)
    {
        $user = $request->user();

        if (!$this->authorizationService->canUpdateDocumentMetadata($user, $document)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
        ]);

        $document->update($request->only('name'));
        AuditLogger::log($user, 'UPDATE_METADATA', $document);

        return response()->json($document);
    }

    public function restore($id)
    {
        $user = auth()->user();

        try {
            $document = Document::withTrashed()->findOrFail($id);
            $this->fileOperationService->restoreDocument($user, $document);
            return response()->json($document);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function stats(Document $document)
    {
        $user = auth()->user();

        if (!$this->authorizationService->canViewDocument($user, $document)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $stats = [
            'downloads' => AuditLog::where('action', 'DOWNLOAD')
                ->where('resource_id', $document->id)
                ->count(),
            'views' => AuditLog::where('action', 'VIEW')
                ->where('resource_id', $document->id)
                ->count(),
            'shares' => AuditLog::where('action', 'SHARE')
                ->where('resource_id', $document->id)
                ->count(),
        ];

        return response()->json($stats);
    }
}
