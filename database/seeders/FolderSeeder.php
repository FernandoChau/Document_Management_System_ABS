<?php

namespace Database\Seeders;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FolderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Folder::create(attributes: [
            'name' => 'Políticas Institucionais',
            'folder_ref' => 'PL',
            'parent_id' => null,
            'created_by'=> User::first()->id,
        ]);

        Folder::create(attributes: [
            'name' => 'Documentos Correntes',
            'folder_ref' => 'DC',
            'parent_id' => null,
            'created_by'=> User::first()->id,
        ]);

        Folder::create(attributes: [
            'name' => 'Normas e Regulamentos',
            'folder_ref' => 'NR',
            'parent_id' => null,
            'created_by'=> User::first()->id,
        ]);

        Folder::create(attributes: [
            'name' => 'Documentos de Área Operacional',
            'folder_ref' => 'DAO',
            'parent_id' => null,
            'created_by'=> User::first()->id,
        ]);

        Folder::create(attributes: [
            'name' => 'Memorandos ou Acordos',
            'folder_ref' => 'MA',
            'parent_id' => null,
            'created_by'=> User::first()->id,
        ]);
    }
}
