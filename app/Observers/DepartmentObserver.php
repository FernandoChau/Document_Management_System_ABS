<?php

namespace App\Observers;

use App\Models\Department;
use Illuminate\Support\Str;

class DepartmentObserver
{
    /**
     * Handle the Department "updated" event.
     * 
     * Synchronize slug changes to root folder.
     */
    public function updated(Department $department): void
    {
        // If slug changed, sync with root folder
        if ($department->isDirty('slug')) {
            $department->rootFolders()
                ->where('is_root', true)
                ->update(['slug' => $department->slug]);
        }
    }
}
