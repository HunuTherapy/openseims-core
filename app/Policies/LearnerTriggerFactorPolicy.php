<?php

namespace App\Policies;

use App\Models\LearnerTriggerFactor;
use App\Models\User;

class LearnerTriggerFactorPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LearnerTriggerFactor $learnerTriggerFactor): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        if ($user->roles()->count() === 0) {
            return false;
        }

        return $user->roles()->whereIn('name', config('app.view_only_roles'))->doesntExist();
    }

    public function update(User $user, LearnerTriggerFactor $learnerTriggerFactor): bool
    {
        if ($user->roles()->count() === 0) {
            return false;
        }

        return $user->roles()->whereIn('name', config('app.view_only_roles'))->doesntExist();
    }

    public function delete(User $user, LearnerTriggerFactor $learnerTriggerFactor): bool
    {
        if ($user->roles()->count() === 0) {
            return false;
        }

        return $user->roles()->whereIn('name', config('app.view_only_roles'))->doesntExist();
    }

    public function restore(User $user, LearnerTriggerFactor $learnerTriggerFactor): bool
    {
        if ($user->roles()->count() === 0) {
            return false;
        }

        return $user->roles()->whereIn('name', config('app.view_only_roles'))->doesntExist();
    }

    public function forceDelete(User $user, LearnerTriggerFactor $learnerTriggerFactor): bool
    {
        if ($user->roles()->count() === 0) {
            return false;
        }

        return $user->roles()->whereIn('name', config('app.view_only_roles'))->doesntExist();
    }
}
