<?php

namespace App\Services;

use App\Models\Folder;
use Illuminate\Support\Collection;

class FolderValidator
{
    /**
     * Validate folder data before create/update
     * 
     * @param array $data
     * @param Folder|null $existingFolder (for updates)
     * @return array
     * @throws \InvalidArgumentException
     */
    public function validateFolderData(array $data, ?Folder $existingFolder = null): array
    {
        // No is_root validation needed anymore. Logic: root if parent_id is null.
        if (isset($data['is_root'])) {
            unset($data['is_root']);
        }

        // Validate: parent_id exists and is valid
        if (isset($data['parent_id']) && !is_null($data['parent_id'])) {
            $parent = Folder::find($data['parent_id']);
            if (!$parent) {
                throw new \InvalidArgumentException('Parent folder does not exist.');
            }

            // Validate: parent folder is not soft-deleted
            if ($parent->trashed()) {
                throw new \InvalidArgumentException('Cannot move folder to a deleted parent folder.');
            }

            // Prevent circular references
            if ($existingFolder && $this->isDescendant($data['parent_id'], $existingFolder->id)) {
                throw new \InvalidArgumentException('Cannot set a descendant folder as parent (would create circular reference).');
            }

            // Prevent folder from being its own parent (direct circular reference)
            if ($existingFolder && $data['parent_id'] === $existingFolder->id) {
                throw new \InvalidArgumentException('A folder cannot be its own parent.');
            }
        }

        // Validate: name is not empty
        if (isset($data['name']) && empty(trim($data['name']))) {
            throw new \InvalidArgumentException('Folder name cannot be empty.');
        }

        // Validate: name length
        if (isset($data['name']) && strlen(trim($data['name'])) > 255) {
            throw new \InvalidArgumentException('Folder name cannot exceed 255 characters.');
        }

        return $data;
    }

    /**
     * Validate folder exists and is not deleted
     * 
     * @param Folder|null $folder
     * @return void
     * @throws \InvalidArgumentException
     */
    public function validateFolderExists(?Folder $folder): void
    {
        if (!$folder) {
            throw new \InvalidArgumentException('Folder not found.');
        }

        if ($folder->trashed()) {
            throw new \InvalidArgumentException('Folder has been deleted.');
        }
    }

    /**
     * Validate folder hierarchy integrity
     * Checks that a folder doesn't have itself as parent (circular reference)
     * 
     * @param Folder $folder
     * @return bool
     */
    public function validateFolderHierarchy(Folder $folder): bool
    {
        if (is_null($folder->parent_id)) {
            return true;
        }

        // Check for circular references
        $visited = collect();
        $current = $folder;

        while ($current && !is_null($current->parent_id)) {
            if ($visited->contains($current->id)) {
                return false; // Circular reference found
            }
            
            $visited->push($current->id);
            $current = $current->parent;
        }

        return true;
    }

    /**
     * Check if a folder (by $checkId) is a descendant of another folder (by $parentId)
     * Used to prevent circular references
     * 
     * @param string $checkId
     * @param string $parentId
     * @return bool
     */
    private function isDescendant(string $checkId, string $parentId): bool
    {
        $current = Folder::find($checkId);
        
        while ($current && !is_null($current->parent_id)) {
            if ($current->parent_id === $parentId) {
                return true;
            }
            $current = $current->parent;
        }
        
        return false;
    }

    /**
     * Validate that folder is not root
     * 
     * @param Folder $folder
     * @return void
     * @throws \InvalidArgumentException
     */
    public function validateNotRoot(Folder $folder): void
    {
        if ($folder->isRoot()) {
            throw new \InvalidArgumentException('This operation cannot be performed on the root folder.');
        }
    }

    /**
     * Validate folder can be deleted (has no active children or documents)
     * 
     * @param Folder $folder
     * @return void
     * @throws \InvalidArgumentException
     */
    public function validateCanDelete(Folder $folder): void
    {
        // Check for non-trashed children
        $activeChildren = $folder->children()->whereNull('deleted_at')->count();
        if ($activeChildren > 0) {
            throw new \InvalidArgumentException('Cannot delete folder with active subfolders.');
        }

        // Check for non-trashed documents
        $activeDocuments = $folder->documents()->whereNull('deleted_at')->count();
        if ($activeDocuments > 0) {
            throw new \InvalidArgumentException('Cannot delete folder with active documents.');
        }
    }

    /**
     * Validate parent folder chain integrity
     * Ensures all ancestors are not deleted
     * 
     * @param Folder $folder
     * @return void
     * @throws \InvalidArgumentException
     */
    public function validateAncestorChainIntegrity(Folder $folder): void
    {
        $current = $folder->parent;
        
        while ($current) {
            if ($current->trashed()) {
                throw new \InvalidArgumentException('One or more parent folders have been deleted.');
            }
            $current = $current->parent;
        }
    }

    /**
     * Get all descendants of a folder (recursive)
     * 
     * @param Folder $folder
     * @return Collection
     */
    public function getDescendants(Folder $folder): Collection
    {
        $descendants = collect();
        
        foreach ($folder->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($this->getDescendants($child));
        }
        
        return $descendants;
    }

    /**
     * Check if folder has any active descendants
     * 
     * @param Folder $folder
     * @return bool
     */
    public function hasActiveDescendants(Folder $folder): bool
    {
        $hasChildren = $folder->children()->whereNull('deleted_at')->exists();
        
        if ($hasChildren) {
            return true;
        }

        // Recursively check descendants
        foreach ($folder->children as $child) {
            if ($this->hasActiveDescendants($child)) {
                return true;
            }
        }

        return false;
    }
}
