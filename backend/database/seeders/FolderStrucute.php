<?php

namespace Database\Seeders;

use App\Models\Folder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FolderStrucute extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Folder::create([
            'name' => 'Documentos Corentes',
            'parent_id' => null,
            'reference_code' => 'doc.corentes',
        ]);
        Folder::create([
            'name' => 'Memorandos ou Acordos',
            'parent_id' => null,
            'reference_code' => 'memorandos.acordos',
        ]);
        Folder::create([
            'name' => 'Normas e Regulamentos',
            'parent_id' => null,
            'reference_code' => 'normas.regulamentos',
        ]);

        //subpastas de Documentos Corentes
        Folder::create([
            'name' => 'Administrativos',
            'parent_id' => Folder::where('name', 'Documentos Corentes')->first()->id,
            'reference_code' => 'doc.administrativos',
        ]);
        Folder::create([
            'name' => 'Contabilidade',
            'parent_id' => Folder::where('name', 'Documentos Corentes')->first()->id,
            'reference_code' => 'doc.contabilidade',
        ]);
        Folder::create([
            'name' => 'Recursos Humanos',
            'parent_id' => Folder::where('name', 'Documentos Corentes')->first()->id,
            'reference_code' => 'doc.recursos_humanos',
        ]);

        //Subpastas de Administrativos
        Folder::create([
            'name' => 'Cartas',
            'parent_id' => Folder::where('name', 'Administrativos')->first()->id,
            'reference_code' => 'administrativos.cartas',
        ]);
        Folder::create([
            'name' => 'Relatórios',
            'parent_id' => Folder::where('name', 'Administrativos')->first()->id,
            'reference_code' => 'administrativos.relatorios',
        ]);

        //Subpastas de RH
        Folder::create([
            'name' => 'Contratos',
            'parent_id' => Folder::where('name', 'Recursos Humanos')->first()->id,
            'reference_code' => 'recursos.contratos',
        ]);

        //Subpastas de Memorandos ou Acordos
        Folder::create([
            'name' => 'Acordos de Actividades',
            'parent_id' => Folder::where('name', 'Memorandos ou Acordos')->first()->id,
            'reference_code' => 'memorandos.acordos_actividades',
        ]);
        Folder::create([
            'name' => 'Parcerias',
            'parent_id' => Folder::where('name', 'Memorandos ou Acordos')->first()->id,
            'reference_code' => 'memorandos.parcerias',
        ]);
        
    }
}
