<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function index()
    {
        $files = File::all();
        return view('files.index', compact('files'));
    }

    public function create()
    {
        return view('files.create');
    }

    public function store(Request $request)
    {
        $filesInFolder = count(File::where('folder_id', $request->folder_id)->get()) + 1;

        $rules = [
            'files' => 'required',
            'files.*' => 'file',
            'folder_id' => 'nullable|uuid',
            'tag' => 'nullable|in:Important,Relevant,Optional',
            'is_accessible' => 'nullable|boolean',
            'is_removable' => 'nullable|boolean',
        ];

        $params = [
            'files.required' => 'Selecione pelo menos um ficheiro.',
            'files.*.file' => 'Todos os ficheiros devem ser válidos.',
            'folder_id.uuid' => 'Selecione um diretório válido',
            'tag.in' => 'A tag do arquivo deve ser uma das seguintes: Importante, Relevante, Opcional',
            'is_accessible.boolean' => 'O campo de acessibilidade deve ser verdadeiro ou falso',
            'is_removable.boolean' => 'O campo de remoção deve ser verdadeiro ou falso',
        ];
    
        $validator = Validator::make($request->all(), $rules, $params);
        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Falha ao publicar os ficheiros.');
        }

        if ($request->folder_id === null) {
            $fileRef = 'ABS'.'.'. str_pad($filesInFolder, 4, '0', STR_PAD_LEFT).'.'.now()->format('y');
        } else {
            $fileRef = Folder::find($request->folder_id)->folder_ref.'.'. str_pad($filesInFolder, 4, '0', STR_PAD_LEFT).'.'.now()->format('y');
        }

        $files = $request->file('files');
        if ($files && is_array($files)) {
            foreach ($files as $fileRequest) {
                $fileSize = $fileRequest->getSize();
                $fileRequestExt = $fileRequest->getClientOriginalExtension();
                $fileName = $fileRef.'.'.pathinfo($fileRequest->getClientOriginalName(), PATHINFO_FILENAME) . '.' . $fileRequestExt;

                // 🔥 Upload direto para Wasabi
                $path = Storage::disk('wasabi')->putFileAs('uploads', $fileRequest, $fileName, ['visibility' => 'private']);

                $file = new File();
                $file->name = $fileName;
                $file->file_ref = $fileRef;
                $file->extension = $fileRequestExt;
                $file->size = $fileSize;
                $file->path = $path; // guarda apenas o path no Wasabi
                $file->tag = $request->tag ? $request->tag : "Optional";
                $file->folder_id = $request->folder_id;

                $file->created_by = auth()->user()->id;
                $file->updated_by = null;
                $file->deleted_by = null;
                
                $file->is_accessible = true;
                $file->is_removable = true;
                $file->is_public = false;
                
                $file->save();
            }
        }

        return redirect()->back()->with('success', 'Ficheiros criados com sucesso.');
    }

    public function show($id)
    {
        $file = File::findOrFail($id);
        return view('files.show', compact('file'));
    }

    public function edit($id)
    {
        $file = File::findOrFail($id);
        return view('files.edit', compact('file'));
    }

    public function update(Request $request, $id)
    {
        $file = File::findOrFail($id);

        if (auth()->user()->id !== $file->created_by && auth()->user()->role != 'admin') {
            return redirect()->back()->with('error','Você não tem permissão para editar este arquivo.');
        } 
        
        $request->validate([
            'name' => 'required|string|max:255',
            'folder_id' => 'nullable|uuid',
            'tag' => 'in:Important,Relevant,Optional',
            'is_accessible' => 'boolean',
            'is_removable' => 'boolean',
        ]);

        $file->update([
            'name' => $request->name . "." . $file->extension,
            'folder_id' => $request->folder_id ? $request->folder_id : $file->folder_id,
            'tag' => $request->tag,
            'updated_by' => auth()->user()->id,
            'is_accessible' => $request->is_accessible ?? false,
            'is_removable' => $request->is_removable ?? false,
        ]);

        return redirect()->back()->with('success', 'Arquivo atualizado com sucesso.');
    }

    public function disable($id)
    {
        $file = File::findOrFail($id);
        if (auth()->user()->id !== $file->created_by && auth()->user()->role !== 'admin') {
            return redirect()->back()->with('error','Você não tem permissão para remover este ficheiro.');
        }

        if (!$file->is_removable || !$file->is_accessible) 
            return redirect()->back()->with('error','A remoção deste arquivo não é permitida.');
        
        $file->is_accessible = false;
        $file->deleted_by = auth()->user()->id;
        $file->save();
        return redirect()->back()->with('success','Arquivo Removido com sucesso.');
    }

    public function destroy($id)
    {
        $file = File::findOrFail($id);

        // 🔥 também remove do Wasabi
        Storage::disk('wasabi')->delete($file->path);

        $file->delete();
        return redirect()->back()->with('success','Arquivo excluído com sucesso.');
    }

    public function download($id)
    {
        $file = File::findOrFail($id);
        if (!$file->is_accessible) 
            return redirect()->back()->with('error', 'Acesso negado. O arquivo não é acessível.');

        // 🔥 gera URL temporária (assinada) para download seguro
        $url = Storage::disk('wasabi')->temporaryUrl($file->path, now()->addMinutes(10));
        return redirect($url);
    }

    public function preview($id)
    {
        $file = File::findOrFail($id);
        if (!$file->is_accessible) 
            return redirect()->back()->with('error', 'Acesso negado. O arquivo não é acessível.');

        // 🔥 gera URL temporária para visualizar
        $url = Storage::disk('wasabi')->temporaryUrl($file->path, now()->addMinutes(10));
        return redirect($url);
    }
}
