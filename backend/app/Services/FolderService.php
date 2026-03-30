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
                $isRoot = false;  // ✅ Subpasta: NOT root
            } else {
                // ✅ PASTA RAIZ (root folder)
                // Se tiver department, usa slug do department; senão usa slug da pasta
                $referenceCode = $department ? $department->slug : $slug;
                $departmentId = $department?->id;
                $isRoot = true;  // ✅ Raiz: IS root
            }

            // Cria a pasta com is_root definido correctamente
            $folder = Folder::create([
                'name' => $name,
                'slug' => $slug,
                'parent_id' => $parent?->id,  // ✅ NULL para raiz, ID para subpasta
                'department_id' => $departmentId,
                'reference_code' => $referenceCode,
                'is_root' => $isRoot,  // ✅ true para raiz, false para subpasta
            ]);
            
            // Criar permissões básicas baseado se é subpasta
            if (!$isRoot) {
                $folder->permissions()->create([
                    'user_id' => $creator->id,
                    'permission_level' => 'manage',
                ]);
            }

            return $folder;
        });
    }
}
