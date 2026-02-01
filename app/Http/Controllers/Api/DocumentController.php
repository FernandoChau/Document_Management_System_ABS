<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\Document;
use App\Models\AuditLog;
use App\Services\DocumentService;
use App\Services\AuditLogger;
use App\Services\AuthorizationService;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    protected $documentService;
    protected $authorizationService;

    public function __construct(DocumentService $documentService, AuthorizationService $authorizationService)
    {
        $this->documentService = $documentService;
        $this->authorizationService = $authorizationService;
    }

    public function store(Request $request, Folder $folder)
    {
        $user = $request->user();

        // Check upload permission
        if (!$this->authorizationService->canUploadToFolder($user, $folder)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Batch upload support
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:51200', // 50MB max per file
        ]);

        $documents = [];

        foreach ($request->file('files') as $file) {
            $doc = $this->documentService->uploadFile($folder, $file, $user);
            AuditLogger::log($user, 'UPLOAD', $doc);
            $documents[] = $doc;
        }

        return response()->json(['message' => 'Upload successful', 'documents' => $documents], 201);
    }

    public function show(Document $document)
    {
        $user = auth()->user();

        if (!$this->authorizationService->canViewDocument($user, $document)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        AuditLogger::log($user, 'VIEW', $document);
        return response()->json($document->load('content')); // Eager load extracted text
    }

    public function download(Document $document)
    {
        $user = auth()->user();

        if (!$this->authorizationService->canDownloadDocument($user, $document)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        AuditLogger::log($user, 'DOWNLOAD', $document);
        return response()->download(storage_path('app/' . $document->file_path), $document->name);
    }

    public function destroy(Document $document)
    {
        $user = auth()->user();

        if (!$this->authorizationService->canDeleteDocument($user, $document)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Soft delete
        $document->delete();
        AuditLogger::log($user, 'SOFT_DELETE', $document);
        return response()->noContent();
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
            if (!$this->authorizationService->canViewFolder($user, $folder)) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
            $query->where('folder_id', $request->folder_id);
        }

        $perPage = $request->input('per_page', 15);
        $documents = $query->with('folder', 'uploader', 'content')
            ->paginate($perPage);

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

        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $document = Document::withTrashed()->findOrFail($id);
        $document->restore();
        AuditLogger::log($user, 'RESTORE', $document);
        return response()->json($document);
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
