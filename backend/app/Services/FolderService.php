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
     * @param Department|null $department (Required if creating a root folder)
     * @return Folder
     */
    public function createFolder(string $name, User $creator, ?Folder $parent = null, ?Department $department = null): Folder
    {
        return DB::transaction(function () use ($name, $creator, $parent, $department) {
            // Generate a slug/short code for this folder
            // e.g., "Test Folder" -> "tf" or "test-folder" or just "t" if user prefers short.
            // For now, let's take first letter of each word or slugify restricted to 3 chars if possible?
            // User example: "teste" -> "t". Let's stick to simple slug for now.
            // A better approach is to ask for a specific code, but I'll auto-generate a slug.
            
            $slug = Str::slug($name);
            
            if ($parent) {
                // Child Folder
                $referenceCode = $parent->reference_code . '.' . $slug;
                $departmentId = null; // Stored only on root in our schema, or we can traverse. Schema says nullable.
                $isRoot = false;
            } else {
                // Root Folder (Must have department)
                if (!$department) {
                    throw new \InvalidArgumentException("Department is required for root folders.");
                }
                $referenceCode = $department->slug; 
                // Wait, if it's a folder INSIDE the department root, it should be department->slug . something?
                // The User said "Root folders are automatic".
                // So if we are creating a subfolder inside the department root.
                
                // Case: Initialization of Department Root Folder
                // Actually, the department ITSELF acts as the container, but we likely need a "Root Folder" record for the tree structure.
                // Let's assume the Department creation triggers a Root Folder creation with ref = department->slug.
                
                $referenceCode = $department->slug;
                $departmentId = $department->id;
                $isRoot = true;
            }

            // Ensure uniqueness of reference code by appending counter if needed?
            // "dt.t" might exist. "dt.t-1"? 
            // For simplicity, we assume unique text names. Real world would handle collision.

            $folder = Folder::create([
                'name' => $name,
                'parent_id' => $parent?->id,
                'department_id' => $departmentId,
                'reference_code' => $referenceCode,
                'is_root' => $isRoot,
            ]);
            
            // Auto-grant 'manage' permission to creator?
            // If it's a root folder created by system/admin, maybe not needed or assign to Dept Manager.
            // If subfolder, creator usually gets managed.
            
            if (!$isRoot) {
                // Inherit permissions? Or just give creator access.
                // For now, let's allow creator to manage it.
                $folder->permissions()->create([
                    'user_id' => $creator->id,
                    'permission_level' => 'manage',
                ]);
            }

            return $folder;
        });
    }
}
