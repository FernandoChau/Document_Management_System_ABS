<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Folder;
use App\Models\Department;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\Album;
use App\Models\Photo;
use App\Models\FolderPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Dashboard geral com estatísticas
     */
    public function index(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $dateFrom = $request->date_from ? \Carbon\Carbon::parse($request->date_from) : \Carbon\Carbon::now()->subDays(30);
        $dateTo = $request->date_to ? \Carbon\Carbon::parse($request->date_to) : \Carbon\Carbon::now();

        $stats = [
            'summary' => [
                'total_departments' => Department::count(),
                'total_folders' => Folder::count(),
                'total_documents' => Document::count(),
                'total_size_gb' => round(Document::sum('size') / 1024 / 1024 / 1024, 2),
                'total_users_accessed' => AuditLog::distinct('user_id')->count('user_id'),
            ],
            'documents_by_type' => $this->documentsByType(),
            'uploads_by_date' => $this->uploadsByDate($dateFrom, $dateTo),
            'recent_uploads' => $this->recentUploads(10),
            'recent_downloads' => $this->recentDownloads(10),
            'documents_by_department' => $this->documentsByDepartment(),
            'top_users' => $this->topUsers(10, $dateFrom, $dateTo),
            'storage_usage' => $this->storageUsage(),
        ];

        return response()->json($stats);
    }

    /**
     * Dashboard for Admin
     */
    public function admin(Request $request)
    {
        $dateFrom = \Carbon\Carbon::now()->startOfMonth();
        $dateTo = \Carbon\Carbon::now();

        $totalSizeDocs = (float) Document::sum('size');
        $totalSizeImages = (float) Photo::sum('size');

        $period = $request->query('period', 'month');
        switch ($period) {
            case 'quarter':
                $topUsersDateFrom = \Carbon\Carbon::now()->subMonths(3);
                break;
            case 'semester':
                $topUsersDateFrom = \Carbon\Carbon::now()->subMonths(6);
                break;
            case 'year':
                $topUsersDateFrom = \Carbon\Carbon::now()->subYear();
                break;
            case 'month':
            default:
                $topUsersDateFrom = \Carbon\Carbon::now()->subMonth();
                break;
        }

        $stats = [
            'summary' => [
                'total_documents' => Document::count(),
                'total_folders' => Folder::count(),
                'total_images' => Photo::count(),
                'total_albums' => Album::count(),
                'total_users' => User::count(),
                'documents_this_month' => Document::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'total_size_documents' => $totalSizeDocs,
                'total_size_images' => $totalSizeImages,
                'total_size_general' => $totalSizeDocs + $totalSizeImages,
            ],
            'evolution_chart' => $this->uploadsByDate(\Carbon\Carbon::now()->subMonths(6), \Carbon\Carbon::now()),
            'distribution_chart' => $this->documentsByType(),
            'department_chart' => $this->documentsByDepartment(),
            'recent_activities' => $this->recentUploads(5),
            'top_users' => $this->topUsers(5, $topUsersDateFrom, \Carbon\Carbon::now()),
        ];

        return response()->json($stats);
    }

    /**
     * Dashboard for User
     */
    public function user(Request $request)
    {
        $user = $request->user();

        $userSizeDocs = (float) Document::where('user_id', $user->id)->sum('size');
        $userSizeImages = (float) Photo::where('uploaded_by', $user->id)->sum('size');

        $stats = [
            'summary' => [
                'my_documents' => Document::where('user_id', $user->id)->count(),
                'my_images' => Photo::where('user_id', $user->id)->count(),
                'my_albums' => Album::where('user_id', $user->id)->count(),
                'my_folders' => FolderPermission::where('user_id', $user->id)->distinct('folder_id')->count('folder_id'),
                'total_size_documents' => $userSizeDocs,
                'total_size_images' => $userSizeImages,
                'total_size_general' => $userSizeDocs + $userSizeImages,
            ],
            'evolution_chart' => Document::where('user_id', $user->id)
                ->where('created_at', '>=', \Carbon\Carbon::now()->subMonths(6))
                ->selectRaw('DATE(created_at) as date, count(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            'distribution_chart' => Document::where('user_id', $user->id)
                ->selectRaw('mime_type, count(*) as count, sum(size) as total_size')
                ->groupBy('mime_type')
                ->orderByDesc('count')
                ->get(),
            'recent_activities' => Document::with('folder', 'uploader')
                ->where('user_id', $user->id)
                ->latest('created_at')
                ->limit(5)
                ->get(['id', 'name', 'size', 'mime_type', 'created_at', 'folder_id', 'user_id']),
        ];

        return response()->json($stats);
    }

    /**
     * Dashboard por departamento
     */
    public function department(Department $department, Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $dateFrom = $request->date_from ? \Carbon\Carbon::parse($request->date_from) : \Carbon\Carbon::now()->subDays(30);
        $dateTo = $request->date_to ? \Carbon\Carbon::parse($request->date_to) : \Carbon\Carbon::now();

        $stats = [
            'summary' => [
                'total_documents' => $department->documents()->count(),
                'total_folders' => $department->folders()->count(),
                'total_size_gb' => round($department->documents()->sum('size') / 1024 / 1024 / 1024, 2),
            ],
            'documents_by_type' => $this->documentsByTypeInDepartment($department),
            'uploads_by_date' => $this->uploadsByDateInDepartment($department, $dateFrom, $dateTo),
            'recent_uploads' => $this->recentUploadsInDepartment($department, 10),
            'storage_usage' => $this->storageUsageByFolder($department),
        ];

        return response()->json($stats);
    }

    /**
     * Dashboard por pasta
     */
    public function folder(Folder $folder, Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $dateFrom = $request->date_from ? \Carbon\Carbon::parse($request->date_from) : \Carbon\Carbon::now()->subDays(30);
        $dateTo = $request->date_to ? \Carbon\Carbon::parse($request->date_to) : \Carbon\Carbon::now();

        $stats = [
            'summary' => [
                'total_documents' => $folder->documents()->count(),
                'total_size_gb' => round($folder->documents()->sum('size') / 1024 / 1024 / 1024, 2),
                'total_subfolders' => $folder->children()->count(),
            ],
            'documents_by_type' => $this->documentsByTypeInFolder($folder),
            'uploads_by_date' => $this->uploadsByDateInFolder($folder, $dateFrom, $dateTo),
            'recent_uploads' => $this->recentUploadsInFolder($folder, 10),
        ];

        return response()->json($stats);
    }

    // ==================== Helper Methods ====================

    private function documentsByType()
    {
        return Document::selectRaw('mime_type, count(*) as count, sum(size) as total_size')
            ->groupBy('mime_type')
            ->orderByDesc('count')
            ->get();
    }

    private function documentsByTypeInDepartment(Department $department)
    {
        return $department->documents()
            ->selectRaw('mime_type, count(*) as count, sum(size) as total_size')
            ->groupBy('mime_type')
            ->orderByDesc('count')
            ->get();
    }

    private function documentsByTypeInFolder(Folder $folder)
    {
        return $folder->documents()
            ->selectRaw('mime_type, count(*) as count, sum(size) as total_size')
            ->groupBy('mime_type')
            ->orderByDesc('count')
            ->get();
    }

    private function uploadsByDate($dateFrom, $dateTo)
    {
        return Document::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function uploadsByDateInDepartment(Department $department, $dateFrom, $dateTo)
    {
        return $department->documents()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function uploadsByDateInFolder(Folder $folder, $dateFrom, $dateTo)
    {
        return $folder->documents()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function recentUploads($limit = 10)
    {
        return Document::with('folder', 'uploader')
            ->latest('created_at')
            ->limit($limit)
            ->get(['id', 'name', 'size', 'mime_type', 'created_at', 'user_id', 'folder_id']);
    }

    private function recentUploadsInDepartment(Department $department, $limit = 10)
    {
        return $department->documents()
            ->with('folder', 'uploader')
            ->latest('created_at')
            ->limit($limit)
            ->get(['id', 'name', 'size', 'mime_type', 'created_at', 'user_id', 'folder_id']);
    }

    private function recentUploadsInFolder(Folder $folder, $limit = 10)
    {
        return $folder->documents()
            ->with('uploader')
            ->latest('created_at')
            ->limit($limit)
            ->get(['id', 'name', 'size', 'mime_type', 'created_at', 'user_id']);
    }

    private function recentDownloads($limit = 10)
    {
        return AuditLog::where('action', 'DOWNLOAD')
            ->with('user')
            ->latest('created_at')
            ->limit($limit)
            ->get(['id', 'user_id', 'resource_id', 'created_at']);
    }

    private function documentsByDepartment()
    {
        return Department::withCount('documents')
            ->orderByDesc('documents_count')
            ->get(['id', 'name', 'documents_count']);
    }

    private function topUsers($limit = 10, $dateFrom, $dateTo)
    {
        return AuditLog::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('user_id, count(*) as actions_count')
            ->where('user_id', '!=', null)
            ->groupBy('user_id')
            ->with('user')
            ->orderByDesc('actions_count')
            ->limit($limit)
            ->get();
    }

    private function storageUsage()
    {
        return Department::with('documents')
            ->get()
            ->map(function ($dept) {
                return [
                    'department' => $dept->name,
                    'total_size_gb' => round($dept->documents->sum('size') / 1024 / 1024 / 1024, 2),
                    'document_count' => $dept->documents->count(),
                ];
            });
    }

    private function storageUsageByFolder(Department $department)
    {
        return $department->folders()
            ->with('documents')
            ->get()
            ->map(function ($folder) {
                return [
                    'folder' => $folder->name,
                    'total_size_gb' => round($folder->documents->sum('size') / 1024 / 1024 / 1024, 2),
                    'document_count' => $folder->documents->count(),
                ];
            });
    }
}
