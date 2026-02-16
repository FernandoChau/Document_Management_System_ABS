<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\FolderResponsible;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    /**
     * Listar todos os departamentos (com paginação)
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);

        // dd($perPage);

        $departments = Department::query()
            ->withCount('folders', 'documents')
            // ->withCount('folders')
            ->paginate($perPage);

        return response()->json($departments);
    }

    /**
     * Visualizar um departamento específico com suas pastas raiz
     */
    public function show(Department $department)
    {
        $department->load(['folders' => function ($query) {
            $query->where('is_root', true)->with('children');
        }, 'documents']);

        AuditLogger::log(auth()->user(), 'VIEW', $department);

        return response()->json($department);
    }

    /**
     * Criar novo departamento
     */
    public function store(Request $request)
    {
        // Only admin can create departments
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'description' => 'nullable|string|max:1000',
            'slug' => 'required|string|max:50|unique:departments,slug',
        ]);

        $department = DB::transaction(function () use ($request) {
            $user = auth()->user();

            // Criar departamento
            $dept = Department::create([
                'name' => $request->name,
                'description' => $request->description,
                'slug' => $request->slug,
            ]);

            // Criar pasta raiz automaticamente
            $rootFolder = $dept->folders()->create([
                'name' => $request->name,
                'reference_code' => $dept->slug,
                'is_root' => true,
            ]);

            // Create folder responsible (owner)
            FolderResponsible::create([
                'folder_id' => $rootFolder->id,
                'user_id' => $user->id,
                'is_owner' => true,
            ]);

            // Create root folder permissions for creator
            $rootFolder->permissions()->create([
                'user_id' => $user->id,
                'can_view' => true,
                'can_update_metadata' => true,
                'can_delete' => true,
                'can_upload' => true,
                'can_share' => true,
                'can_download' => true,
            ]);

            // Log auditoria
            AuditLogger::log($user, 'CREATE', $dept);

            return $dept;
        });

        return response()->json($department, 201);
    }

    /**
     * Atualizar departamento
     */
    public function update(Request $request, Department $department)
    {
        // Only admin can update departments
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255|unique:departments,name,' . $department->id,
            'description' => 'nullable|string|max:1000',
            'slug' => 'sometimes|string|max:50|unique:departments,slug,' . $department->id,
        ]);

        $department->update($request->only('name', 'description', 'slug'));
        AuditLogger::log(auth()->user(), 'UPDATE_METADATA', $department);

        return response()->json($department);
    }

    /**
     * Deletar departamento (soft delete)
     */
    public function destroy(Department $department)
    {
        // Only admin can delete departments
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $department->delete();
        AuditLogger::log(auth()->user(), 'SOFT_DELETE', $department);

        return response()->noContent();
    }

    /**
     * Restaurar departamento deletado
     */
    public function restore($id)
    {
        // Only admin can restore departments
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $department = Department::withTrashed()->findOrFail($id);
        $department->restore();
        AuditLogger::log(auth()->user(), 'RESTORE', $department);

        return response()->json($department);
    }

    /**
     * Deletar permanentemente departamento
     */
    public function forceDelete($id)
    {
        // Only admin can force delete departments
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $department = Department::withTrashed()->findOrFail($id);
        $department->forceDelete();
        AuditLogger::log(auth()->user(), 'FORCE_DELETE', $department);

        return response()->noContent();
    }

    /**
     * Estatísticas do departamento
     */
    public function stats(Department $department)
    {
        
        $stats = [
            'total_documents' => $department->documents()->count(),
            'total_folders' => $department->folders()->count(),
            'total_size' => $department->documents()->sum('size'),
            'recent_uploads' => $department->documents()
                ->latest('created_at')
                ->limit(5)
                ->get(['id', 'name', 'created_at', 'user_id']),
        ];

        return response()->json($stats);
    }
}
