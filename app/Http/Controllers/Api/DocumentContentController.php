<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentContent;
use App\Models\Document;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class DocumentContentController extends Controller
{
    /**
     * Obter conteúdo extraído de um documento
     */
    public function show(Document $document)
    {
        // dd($document);
        $content = $document->content;

        if (!$content) {
            return response()->json(['message' => 'No extracted content found'], 404);
        }

        AuditLogger::log(auth()->user(), 'VIEW', $content);

        return response()->json($content);
    }

    /**
     * Atualizar conteúdo extraído (manual)
     */
    public function update(Request $request, Document $document)
    {
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
    public function destroy(Document $document)
    {
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

        $query = $request->input('q');
        $perPage = $request->input('per_page', 15);

        $results = DocumentContent::whereRaw("extracted_text LIKE ?", ["%{$query}%"])
            ->with('document')
            ->paginate($perPage);

        return response()->json($results);
    }

    /**
     * Obter status de extração de um documento
     */
    public function status(Document $document)
    {
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
}
