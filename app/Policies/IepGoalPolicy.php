<?php

namespace App\Policies;

use App\Models\IepGoal;
use App\Models\User;

class IepGoalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view iep goal');
    }

    public function view(User $user, IepGoal $iepGoal): bool
    {
        return $user->hasPermissionTo('view iep goal');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create iep goal');
    }

    public function update(User $user, IepGoal $iepGoal): bool
    {
        return $user->hasPermissionTo('edit iep goal');
    }

    public function delete(User $user, IepGoal $iepGoal): bool
    {
        return $user->hasPermissionTo('delete iep goal');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo('delete iep goal');
    }

    public function restore(User $user, IepGoal $iepGoal): bool
    {
        return false;
    }

    public function forceDelete(User $user, IepGoal $iepGoal): bool
    {
        return false;
    }
}
