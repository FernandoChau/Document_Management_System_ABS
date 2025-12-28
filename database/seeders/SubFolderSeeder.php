<?php

namespace Database\Seeders;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubFolderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $folder = Folder::where("name","Documentos Correntes")->first("id");
        $id = $folder->id;

        Folder::create(attributes: [
            'name' => 'Administrativos',
            'folder_ref' => 'DC.Ad',
            'parent_id' => $id,
            'created_by'=> User::first()->id,
        ]);

        Folder::create(attributes: [
            'name' => 'Recursos Humanos',
            'folder_ref' => 'DC.RH',
            'parent_id' => $id,
            'created_by'=> User::first()->id,
        ]);

        Folder::create(attributes: [
            'name' => 'Contabilidade',
            'folder_ref' => 'PL.Ct',
            'parent_id' => $id,
            'created_by'=> User::first()->id,
        ]);


        $folder = Folder::where("name","Documentos de Área Operacional")->first("id");
        $id = $folder->id;

        Folder::create(attributes: [
            'name' => 'Contractos',
            'folder_ref' => 'DAO.Ct',
            'parent_id' => $id,
            'created_by'=> User::first()->id,
        ]);

        Folder::create(attributes: [
            'name' => 'Relatórios',
            'folder_ref' => 'DAO.Rl',
            'parent_id' => $id,
            'created_by'=> User::first()->id,
        ]);

        Folder::create(attributes: [
            'name' => 'Cartas',
            'folder_ref' => 'DAO.Cr',
            'parent_id' => $id,
            'created_by'=> User::first()->id,
        ]);

        $folder = Folder::where("name","Memorandos ou Acordos")->first("id");
        $id = $folder->id;

        Folder::create(attributes: [
            'name' => 'Acordos de parceria',
            'folder_ref' => 'MA.AP',
            'parent_id' => $id,
            'created_by'=> User::first()->id,
        ]);

        Folder::create(attributes: [
            'name' => 'Acordos de actividades',
            'folder_ref' => 'MA.AA',
            'parent_id' => $id,
            'created_by'=> User::first()->id,
        ]);
    }
}
