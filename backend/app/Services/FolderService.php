<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FolderService
{
    /**
     * Create a new folder (Subfolder or Root).
     *
     * @param string $name
     * @param User $creator
     * @param Folder|null $parent
     * @param Department|null $department (Optional, only used for root folders)
     * @return Folder
     */
    public function createFolder(string $name, User $creator, ?Folder $parent = null, ?Department $department = null): Folder
    {
        return DB::transaction(function () use ($name, $creator, $parent, $department) {
            $slug = Str::slug($name);
            
            if ($parent) {
                // ✅ SUBPASTA (child folder)
                $referenceCode = $parent->reference_code . '.' . $slug;
                $departmentId = null;
            } else {
                // ✅ PASTA RAIZ (root folder)
                // Se tiver department, usa slug do department; senão usa slug da pasta
                $referenceCode = $department ? $department->slug : $slug;
                $departmentId = $department?->id;
            }

            // Cria a pasta sem o campo is_root (agora calculado pelo parent_id === NULL)
            $folder = Folder::create([
                'name' => $name,
                'slug' => $slug,
                'parent_id' => $parent?->id,  // ✅ NULL para raiz, ID para subpasta
                'department_id' => $departmentId,
                'reference_code' => $referenceCode,
            ]);

            return $folder;
        });
    }
}
