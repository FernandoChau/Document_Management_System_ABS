<?php

namespace App\Observers;

use App\Models\Folder;
use App\Models\Department;
use Illuminate\Support\Str;

class FolderObserver
{
    /**
     * Handle the Folder "updated" event.
     * 
     * Synchronize slug changes to department if this is a root folder.
     */
    public function updated(Folder $folder): void
    {
        // If this is a root folder and slug changed, sync with department
        if ($folder->is_root && $folder->isDirty('slug') && $folder->department_id) {
            Department::where('id', $folder->department_id)
                ->update(['slug' => $folder->slug]);
        }
    }
}
