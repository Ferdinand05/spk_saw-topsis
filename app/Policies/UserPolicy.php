<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, User $model): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isOwner();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isOwner();
    }

    public function delete(User $user, User $model): bool
    {
        return $user->isOwner();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isOwner();
    }

    public function restore(User $user, User $model): bool
    {
        return $user->isOwner();
    }

    public function restoreAny(User $user): bool
    {
        return $user->isOwner();
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->isOwner();
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->isOwner();
    }
}
