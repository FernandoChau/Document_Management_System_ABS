<?php

namespace App\Policies;

use App\Models\Photo;
use App\Models\User;

class PhotoPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Photo $photo): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Photo $photo): bool
    {
        return $user->isImageManager() || $user->id === $photo->uploaded_by;
    }

    public function delete(User $user, Photo $photo): bool
    {
        return $user->isImageManager() || $user->id === $photo->uploaded_by;
    }
}
