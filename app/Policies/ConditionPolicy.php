<?php

namespace App\Policies;

use App\Models\Condition;
use App\Models\User;

class ConditionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view condition');
    }

    public function view(User $user, Condition $condition): bool
    {
        return $user->hasPermissionTo('view condition');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create condition');
    }

    public function update(User $user, Condition $condition): bool
    {
        return $user->hasPermissionTo('edit condition');
    }

    public function delete(User $user, Condition $condition): bool
    {
        return $user->hasPermissionTo('delete condition');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo('delete condition');
    }

    public function restore(User $user, Condition $condition): bool
    {
        if ($user->roles()->count() === 0) {
            return false;
        }

        return $user->roles()->whereIn('name', config('app.view_only_roles'))->doesntExist();
    }

    public function forceDelete(User $user, Condition $condition): bool
    {
        if ($user->roles()->count() === 0) {
            return false;
        }

        return $user->roles()->whereIn('name', config('app.view_only_roles'))->doesntExist();
    }
}
