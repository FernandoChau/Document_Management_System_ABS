<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Folder;
use App\Models\Document;
use App\Models\ShareLink;
use App\Services\PermissionValidator;

class ValidateFileAccessMiddleware
{
    protected PermissionValidator $permissionValidator;

    public function __construct(PermissionValidator $permissionValidator)
    {
        $this->permissionValidator = $permissionValidator;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        $shareToken = $request->query('share_token');

        // If share token provided, validate it (no auth required)
        if ($shareToken) {
            $shareLink = ShareLink::where('token', $shareToken)->first();
            
            if (!$shareLink || $shareLink->isExpired()) {
                return response()->json(['error' => 'Invalid or expired share link'], 403);
            }

            // Validate password if required
            if ($shareLink->password && !$request->query('password')) {
                return response()->json(['error' => 'Password required for this share link'], 403);
            }

            if ($shareLink->password && !hash_equals(
                $shareLink->password,
                hash('sha256', $request->query('password'))
            )) {
                return response()->json(['error' => 'Invalid password'], 403);
            }

            // Inject share link into request
            $request->attributes->set('share_link', $shareLink);
            $request->attributes->set('resource_type', $shareLink->shareable_type);
            $request->attributes->set('resource_id', $shareLink->shareable_id);

            return $next($request);
        }

        // Otherwise, user must be authenticated
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get the folder or document from route parameters
        $folderParam = $request->route('folder') ?? $request->route('folder_id');
        $documentParam = $request->route('document') ?? $request->route('document_id');

        // Handle folder - model binding may have already resolved it
        $folder = null;
        if ($folderParam instanceof Folder) {
            $folder = $folderParam;
        } elseif ($folderParam) {
            $folder = Folder::find($folderParam);
        }

        // Validate folder access
        if ($folder) {
            try {
                $permissions = $this->permissionValidator->validateFolderAction($user, $folder, 'view');
                $request->attributes->set('resource_permissions', $permissions['permissions']);
                $request->attributes->set('resource_type', 'folder');
                $request->attributes->set('resource', $folder);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 403);
            }
        }

        // Handle document - model binding may have already resolved it
        $document = null;
        if ($documentParam instanceof Document) {
            $document = $documentParam;
        } elseif ($documentParam) {
            $document = Document::find($documentParam);
        }

        // Validate document access
        if ($document) {
            try {
                $permissions = $this->permissionValidator->validateDocumentAction($user, $document, 'view');
                $request->attributes->set('resource_permissions', $permissions['permissions']);
                $request->attributes->set('resource_type', 'document');
                $request->attributes->set('resource', $document);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 403);
            }
        }

        return $next($request);
    }
}
