<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AuthorizationService;

class AuthorizeAccess
{
    protected AuthorizationService $authorizationService;

    public function __construct(AuthorizationService $authorizationService)
    {
        $this->authorizationService = $authorizationService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get the folder or document being accessed from route parameters
        $folderId = $request->route('folder_id') ?? $request->route('folder');
        $documentId = $request->route('document_id') ?? $request->route('document');

        // Check folder authorization
        if ($folderId) {
            $folder = \App\Models\Folder::find($folderId);
            if ($folder && !$this->authorizationService->canViewFolder($user, $folder)) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
        }

        // Check document authorization
        if ($documentId) {
            $document = \App\Models\Document::find($documentId);
            if ($document && !$this->authorizationService->canViewDocument($user, $document)) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
        }

        return $next($request);
    }
}
