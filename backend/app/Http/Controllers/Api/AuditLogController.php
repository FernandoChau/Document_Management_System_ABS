<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\Folder;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Listar logs de auditoria (com filtros)
     */
    public function index(Request $request)
    {
        $request->validate([
            'action' => 'nullable|string',
            'resource_type' => 'nullable|string',
            'user_id' => 'nullable|uuid',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = AuditLog::query();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('resource_type')) {
            $query->where('resource_type', $request->resource_type);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = $request->input('per_page', 15);
        $logs = $query->with('user')
            ->latest('created_at')
            ->paginate($perPage);

        return response()->json($logs);
    }

    /**
     * Ver log de auditoria específico
     */
    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');
        return response()->json($auditLog);
    }

    /**
     * Logs de um documento específico
     */
    public function documentLogs(Document $document, Request $request)
    {
        $request->validate([
            'action' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = AuditLog::where('resource_type', 'Document')
            ->where('resource_id', $document->id);

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        $perPage = $request->input('per_page', 15);
        $logs = $query->with('user')
            ->latest('created_at')
            ->paginate($perPage);

        return response()->json($logs);
    }

    /**
     * Logs de uma pasta específica
     */
    public function folderLogs(Folder $folder, Request $request)
    {
        // dd($folder);
        $request->validate([
            'action' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = AuditLog::where('resource_type', 'Folder')
            ->where('resource_id', $folder->id);

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        $perPage = $request->input('per_page', 15);
        $logs = $query->with('user')
            ->latest('created_at')
            ->paginate($perPage);

        return response()->json($logs);
    }

    /**
     * Estatísticas de auditoria
     */
    public function stats(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $baseQuery = AuditLog::query();

        if ($request->filled('date_from')) {
            $baseQuery->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $baseQuery->whereDate('created_at', '<=', $request->date_to);
        }

        $stats = [
            'total_actions' => (clone $baseQuery)->count(),
            'by_action' => (clone $baseQuery)->selectRaw('action, count(*) as count')
                ->groupBy('action')
                ->pluck('count', 'action'),
            'by_resource_type' => (clone $baseQuery)->selectRaw('resource_type, count(*) as count')
                ->groupBy('resource_type')
                ->pluck('count', 'resource_type'),
            'by_user' => (clone $baseQuery)->selectRaw('user_id, count(*) as count')
                ->where('user_id', '!=', null)
                ->groupBy('user_id')
                ->limit(10)
                ->pluck('count', 'user_id'),
            'recent_actions' => (clone $baseQuery)->with('user')
                ->latest('created_at')
                ->limit(10)
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Logs de um usuário específico
     */
    public function userLogs($userId, Request $request)
    {
        $request->validate([
            'action' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = AuditLog::where('user_id', $userId);

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        $perPage = $request->input('per_page', 15);
        $logs = $query->with(['user', 'resource'])
            ->latest('created_at')
            ->paginate($perPage);

        return response()->json($logs);
    }
}
