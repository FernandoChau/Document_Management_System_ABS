<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

        // dd($request->all());

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
            return redirect()->route('dashboard')->with('error', 'Falha ao publicar os ficheiros.');
        }


        $files = $request->file('files');
        if ($files && is_array($files)) {
            foreach ($files as $fileRequest) {
                $fileSize = $fileRequest->getSize();
                $fileRequestExt = $fileRequest->getClientOriginalExtension();
                $fileName = pathinfo($fileRequest->getClientOriginalName(), PATHINFO_FILENAME) . '_' . time() . '.' . $fileRequestExt;

                $fileRequest->move(public_path('uploads'), $fileName);

                $file = new File();
                $file->name = $fileName;
                $file->extension = $fileRequestExt;
                $file->size = $fileSize;
                $file->path = 'uploads/' . $fileName;
                $file->tag = $request->tag ? $request->tag : "Optional";
                $file->folder_id = $request->folder_id;
                $file->created_by = auth()->user()->id;
                $file->is_accessible = true;
                $file->is_removable = true;
                $file->is_removed = false;
                $file->is_public = false;
                $file->save();
            }
        }

        return redirect()->back()->with('success', 'Ficheiros criados com sucesso.');
        // Validate and create the file
        // $request->validate([
        //     'name' => 'required|string|max:255',
        //     'folder_id' => 'nullable|uuid',
        //     'tag' => 'in:Important,Relevant,Optional',
        //     'created_by' => 'required',
        //     'is_accessible' => 'boolean',
        //     'is_removable' => 'boolean',
        // ],[
        //     'name.required' => 'O campo nome não pode ser nulo',
        //     'name.string' => 'O nome do arquivo deve ser uma string',
        //     'name.max' => 'O nome do arquivo não pode ter mais de 255 caracteres',
        //     'folder_id.uuid' => 'Selecione um diretório válido',
        //     'tag.in' => 'A tag do arquivo deve ser uma das seguintes: Importante, Relevante, Opcional',
        //     'created_by.required' => 'O ID do criador é obrigatório',
        //     'is_accessible.boolean' => 'O campo de acessibilidade deve ser verdadeiro ou falso',
        //     'is_removable.boolean' => 'O campo de remoção deve ser verdadeiro ou falso',
        // ]);

        // $file = File::create([
        //     'name' => $request->name,
        //     'folder_id' => $request->folder_id,
        //     'tag' => $request->tag,
        //     'created_by' => $request->created_by,
        //     'is_accessible' => $request->is_accessible ?? true,
        //     'is_removable' => $request->is_removable ?? true,
        // ]);

        // if (!$file) {
        //     return redirect()->route('files.index')->with('error', 'Falha ao criar arquivo.');
        // }

        // return redirect()->route('files.index')->with('success', 'Arquivo criado com sucesso.');
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
        // Validate and update the file
        $request->validate([
            'name' => 'required|string|max:255',
            'folder_id' => 'nullable|uuid',
            'tag' => 'in:Important,Relevant,Optional',
            'is_accessible' => 'boolean',
            'is_removable' => 'boolean',
        ],[
            'name.required' => 'O campo nome não pode ser nulo',
            'name.string' => 'O nome do arquivo deve ser uma string',
            'name.max' => 'O nome do arquivo não pode ter mais de 255 caracteres',
            'folder_id.uuid' => 'Selecione um diretório válido',
            'tag.in' => 'A tag do arquivo deve ser uma das seguintes: Importante, Relevante, Opcional',
            'is_accessible.boolean' => 'O campo de acessibilidade deve ser verdadeiro ou falso',
            'is_removable.boolean' => 'O campo de remoção deve ser verdadeiro ou falso',
        ]);

        $file->update([
            'name' => $request->name . "." . $file->extension,
            'folder_id' => $request->folder_id ? $request->folder_id : $file->folder_id,
            'tag' => $request->tag,
            'is_accessible' => $request->is_accessible ? true : false,
            'is_removable' => $request->is_removable ? true : false,
        ]);

        if (!$file) {
            return redirect()->back()->with('error', 'Falha ao atualizar arquivo.');
        }

        return redirect()->back()->with('success', 'Arquivo atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $file = File::findOrFail($id);
        $file->delete();
        return redirect()->back()->with('success','Arquivo excluído com sucesso.');
    }

    public function download($id)
    {
        $file = File::findOrFail($id);
        $filePath = public_path($file->path);
        return response()->download($filePath, $file->name);
    }

    public function preview($id)
    {
        $file = File::findOrFail($id);
        return response()->file(public_path($file->path));
    }
}
