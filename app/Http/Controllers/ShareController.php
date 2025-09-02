<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\File;
use App\Models\Folder;
use App\Models\SharedLink;
use App\Services\FolderZipper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ShareController extends Controller
{
    // apenas usuários autenticados criam links
    public function store(Request $request)
    {
        // dd(request()->all());
        $data = $request->validate([
            'type' => 'required|in:file,folder',
            'id' => 'required|uuid',
        ]);

        $shareable = $data['type'] === 'file'
            ? File::findOrFail($data['id'])
            : Folder::findOrFail($data['id']);


        $link = new SharedLink();
        $link->token = (string) Str::uuid();
        $link->expires_at = now()->addHours(360);
        $link->created_by = auth()->id();
        // morph
        $link->shareable()->associate($shareable);

        if ($link->save() && $data['type'] === 'file') {
            
            $file = File::find($data['id']);
            $file->expires_at = $link->expires_at;
            $file->link = route('share.show', $link->token);
            $file->save();
            
        } else{
            
            $folder = Folder::find($data['id']);
            $folder->expires_at = $link->expires_at;
            $folder->link = route('share.show', $link->token);
            $folder->save();
        }

        return redirect()->back()->with([
            'success' => 'Link compartilhado com sucesso!',
            'link' => route('share.show', $link->token),
            'expires_day' => $link->expires_at->format('d/m/Y'),
            'expires_time' => $link->expires_at->format('H:i'),
            // 'expires_at' => $link->expires_at,
            'file' => $shareable->name,
        ]);

        // return response()->json([
        //     'url' => route('share.show', $link->token),
        //     'expires_at' => $link->expires_at->toIso8601String(),
        // ]);
    }

    // público: consome o link e baixa
    public function show(string $token, FolderZipper $zipper)
    {
        $link = SharedLink::where('token', $token)->firstOrFail();

        if ($link->isExpired()) {
            abort(410, 'Este link expirou.'); // 410 Gone
        }

        $shareable = $link->shareable;

        // registra o acesso
        $link->increment('downloads_count');

        if ($shareable instanceof File) {
            // ajuste o disk se usar S3: Storage::disk('s3')->download(...)
            $fullPath = public_path( $shareable->path);

            // dd($shareable);

            if (!file_exists($fullPath)) {
                abort(404, 'Ficheiro não encontrado');
            }

            $name = $shareable->name;
            if ($shareable->extension) 
                return response()->file($fullPath);
            
            return response()->download($fullPath, $name);
        }

        if ($shareable instanceof Folder) {
            return $zipper->downloadZip($shareable, $shareable->name);
        }

        abort(404);
    }

    // opcional: revogar um link antes de expirar
    public function revoke(string $token)
    {
        $link = SharedLink::where('token', $token)->firstOrFail();
        $this->authorize('delete', $link); // se quiser usar Policies
        $link->expires_at = now();
        $link->save();

        return response()->noContent();
    }
}
