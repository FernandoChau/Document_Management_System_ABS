<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function index()
    {
        $folders = Folder::all();
        return view('folders.index', compact('folders'));
    }

    public function create()
    {
        return view('folders.create');
    }

    public function store(Request $request)
    {   

        
        //validate
        $request->validate([
            'name' => 'required|string|max:255|',
            'parent_id' => 'nullable|uuid',
            'tag' => 'nullable|in:Important,Relevant,Optional',
            'is_accessible' => 'boolean',
            'is_removable' => 'boolean',
        ],[
            'name.required' => 'O campo nome não pode ser nulo',
            'name.string' => 'O nome da pasta deve ser uma string',
            'name.max' => 'O nome da pasta não pode ter mais de 255 caracteres',
            'parent_id.uuid' => 'Selecione um diretório válido',
            'tag.in' => 'A tag da pasta deve ser uma das seguintes: Importante, Relevante, Opcional',
            'created_by.required' => 'O ID do criador é obrigatório',
            'is_accessible.boolean' => 'O campo de acessibilidade deve ser verdadeiro ou falso',
            'is_removable.boolean' => 'O campo de remoção deve ser verdadeiro ou falso',
        ]);

        $folder = Folder::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'tag' => 'Optional',
            'is_accessible' => $request->is_accessible ?? true,
            'is_removable' => $request->is_removable ?? true,
            'created_by' => auth()->user()->id,
        ]);


        
        if (!$folder) 
            return redirect()->back()->with('error', 'Falha ao atualizar pasta.');

        return redirect()->back()->with('success', 'Pasta criada com sucesso.');
    }

    public function show($id)
    {
        $folder = Folder::findOrFail($id);
        if ($folder->is_accessible == false) {
            return redirect()->back()->with('error', 'A pasta não é acessível.');
        }
        return view('folders.show', compact('folder'));
    }

    public function edit($id)
    {
        $folder = Folder::findOrFail($id);
        if ($folder->is_accessible == false) {
            return redirect()->back()->with('error', 'A pasta não é acessível.');
        }
        return view('folders.edit', compact('folder'));
    }

    public function update(Request $request, $id)
    {
        $folder = Folder::findOrFail($id);

        //validate
        $request->validate([
            'name' => 'required|string|max:255|',
            'parent_id' => 'nullable|uuid',
            'tag' => 'in:Important,Relevant,Optional',

            'is_accessible' => 'boolean',
            'is_removable' => 'boolean',
        ],[
            'name.required' => 'O campo nome não pode ser nulo',
            'name.string' => 'O nome da pasta deve ser uma string',
            'name.max' => 'O nome da pasta não pode ter mais de 255 caracteres',
            'parent_id.uuid' => 'Selecione um diretório válido',
            'tag.in' => 'A tag da pasta deve ser uma das seguintes: Importante, Relevante, Opcional',

            'is_accessible.boolean' => 'O campo de acessibilidade deve ser verdadeiro ou falso',
            'is_removable.boolean' => 'O campo de remoção deve ser verdadeiro ou falso',
        ]);

        $folder->update([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'tag' => $request->tag,
            'updated_by' => auth()->user()->id,
            'is_accessible' => $request->is_accessible ? true : false,
            'is_removable' => $request->is_removable ? true : false,
        ]);
        
        if (!$folder) 
            return redirect()->back()->with('error', 'Falha ao atualizar pasta.');

        return redirect()->back()->with('success', 'Pasta atualizada com sucesso.');
        // return redirect()->route('dashboard')->with('success', 'Pasta atualizada com sucesso.');
    }

    public function update_aux(Request $request)
    {
        $folder = Folder::findOrFail($request->id);

        // dd($request->all());
        //validate
        $request->validate([
            'name' => 'required|string|max:255|',
            'tag' => 'in:Important,Relevant,Optional',
            'is_accessible' => 'boolean',
            'is_removable' => 'boolean',
        ],[
            'name.required' => 'O campo nome não pode ser nulo',
            'name.string' => 'O nome da pasta deve ser uma string',
            'name.max' => 'O nome da pasta não pode ter mais de 255 caracteres',
            'tag.in' => 'A tag da pasta deve ser uma das seguintes: Importante, Relevante, Opcional',
            'is_accessible.boolean' => 'O campo de acessibilidade deve ser verdadeiro ou falso',
            'is_removable.boolean' => 'O campo de remoção deve ser verdadeiro ou falso',
        ]);

        $folder->update([
            'name' => $request->name,
            'tag' => $request->tag,
            'is_accessible' => $request->is_accessible ? true : false,
            'is_removable' => $request->is_removable ? true : false,
        ]);
        
        if (!$folder) 
            return redirect()->route('dashboard')->with('error', 'Falha ao atualizar pasta.');

        return redirect()->route('dashboard')->with('success', 'Pasta atualizada com sucesso.');
    }

    public function disable($id)
    {
        $folder = Folder::findOrFail($id);
        $folder->is_accessible = !$folder->is_accessible;
        $folder->deleted_by = auth()->user()->id;
        $folder->save();
        return redirect()->back()->with('success', 'Acessibilidade da pasta alterada com sucesso.');
    }

    public function destroy($id)
    {
        $folder = Folder::findOrFail($id);
        if ($folder->is_accessible == false) {
            return redirect()->back()->with('error', 'A pasta não é acessível.');
        }
        $folder->delete();
        return redirect()->back()->with('success', 'Pasta excluída com sucesso.');
    }
}
