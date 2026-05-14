<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\Document;

class StructureValidator
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

        // Validate: parent_id exists and is valid
        if (isset($data['parent_id']) && !is_null($data['parent_id'])) {
            $parent = Folder::find($data['parent_id']);
            if (!$parent) {
                throw new \InvalidArgumentException('Parent folder does not exist.');
            }

            // Prevent circular references
            if ($existingFolder && $this->isDescendant($data['parent_id'], $existingFolder->id)) {
                throw new \InvalidArgumentException('Cannot set a descendant folder as parent (would create circular reference).');
            }
        }

        // Validate: name is not empty
        if (isset($data['name']) && empty(trim($data['name']))) {
            throw new \InvalidArgumentException('Folder name cannot be empty.');
        }

        return $data;
    }

    /**
     * Validate document data before create/update
     * 
     * @param array $data
     * @param Document|null $existingDocument (for updates)
     * @return array
     * @throws \InvalidArgumentException
     */
    public function validateDocumentData(array $data, ?Document $existingDocument = null): array
    {
        // Validate: folder_id must be set and not null
        if (!isset($data['folder_id']) || is_null($data['folder_id'])) {
            throw new \InvalidArgumentException('Documents must have a parent folder (folder_id is required).');
        }

        // Validate: folder exists
        $folder = Folder::find($data['folder_id']);
        if (!$folder) {
            throw new \InvalidArgumentException('Parent folder does not exist.');
        }

        // Validate: name is not empty
        if (isset($data['name']) && empty(trim($data['name']))) {
            throw new \InvalidArgumentException('Document name cannot be empty.');
        }

        // Validate: mime type is not empty
        if (isset($data['mime_type']) && empty(trim($data['mime_type']))) {
            throw new \InvalidArgumentException('Document mime type cannot be empty.');
        }

        // Validate: size is positive if provided
        if (isset($data['size']) && $data['size'] < 0) {
            throw new \InvalidArgumentException('Document size must be positive.');
        }

        return $data;
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
}
