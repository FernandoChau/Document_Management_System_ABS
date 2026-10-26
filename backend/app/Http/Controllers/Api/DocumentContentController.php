<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exceptions\PermissionDeniedException;
use App\Services\AuditLogger;
use App\Services\PermissionValidator;
use App\Models\Document;
use App\Models\DocumentContent;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class DocumentContentController extends Controller
{
    public function __construct(
        private PermissionValidator $permissionValidator,
    ) {
    }

    /**
     * Obter conteúdo extraído de um documento
     */
    public function show(Request $request, Document $document)
    {
        if (!$this->canView($request, $document)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $content = $document->content;

        if (!$content) {
            return response()->json(['message' => 'No extracted content found'], 404);
        }

        AuditLogger::log($request->user(), 'VIEW', $content);

        return response()->json($content);
    }

    /**
     * Atualizar conteúdo extraído (manual)
     */
    public function update(Request $request, Document $document)
    {
        if (!$this->canUpdate($request, $document)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $request->validate([
            'extracted_text' => 'required|string',
            'extraction_status' => 'nullable|in:pending,processing,completed,failed',
        ]);

        $content = $document->content ?? new DocumentContent(['document_id' => $document->id]);

        $content->extracted_text = $request->extracted_text;
        $content->extraction_status = $request->extraction_status ?? 'completed';
        $content->save();

        AuditLogger::log(auth()->user(), 'UPDATE_METADATA', $content);

        return response()->json($content);
    }

    /**
     * Deletar conteúdo extraído
     */
    public function destroy(Request $request, Document $document)
    {
        if (!$this->canUpdate($request, $document)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        if ($document->content) {
            $document->content->delete();
            AuditLogger::log(auth()->user(), 'SOFT_DELETE', $document->content);
        }

        return response()->noContent();
    }

    /**
     * Buscar documentos por conteúdo extraído
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $searchTerm = $request->input('q');
        $perPage = (int) $request->input('per_page', 15);
        $page = LengthAwarePaginator::resolveCurrentPage();
        $offset = ($page - 1) * $perPage;
        $visibleContents = [];
        $visibleCount = 0;

        $contents = DocumentContent::query()
            ->whereRaw('extracted_text LIKE ?', ["%{$searchTerm}%"])
            ->whereHas('document')
            ->with('document.folder')
            ->lazyById(100);

        foreach ($contents as $content) {
            $document = $content->document;

            if (!$document || !$this->canView($request, $document)) {
                continue;
            }

            if ($visibleCount >= $offset && count($visibleContents) < $perPage) {
                $visibleContents[] = $content;
            }

            $visibleCount++;
        }

        return response()->json(new LengthAwarePaginator(
            $visibleContents,
            $visibleCount,
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ],
        ));
    }

    /**
     * Obter status de extração de um documento
     */
    public function status(Request $request, Document $document)
    {
        if (!$this->canView($request, $document)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $content = $document->content;

        if (!$content) {
            return response()->json(['status' => 'no_content']);
        }

        return response()->json([
            'status' => $content->extraction_status,
            'extracted_at' => $content->created_at,
            'has_text' => !empty($content->extracted_text),
        ]);
    }

    private function canView(Request $request, Document $document): bool
    {
        try {
            $this->permissionValidator->validateDocumentAction($request->user(), $document, 'view');

            return true;
        } catch (PermissionDeniedException) {
            return false;
        }
    }

    private function canUpdate(Request $request, Document $document): bool
    {
        try {
            $this->permissionValidator->validateDocumentAction($request->user(), $document, 'update');

            return true;
        } catch (PermissionDeniedException) {
            return false;
        }
    }
}
