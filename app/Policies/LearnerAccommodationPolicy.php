<?php

namespace App\Policies;

use App\Models\LearnerAccommodation;
use App\Models\User;

class LearnerAccommodationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LearnerAccommodation $learnerAccommodation): bool
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

    public function update(User $user, LearnerAccommodation $learnerAccommodation): bool
    {
        if ($user->roles()->count() === 0) {
            return false;
        }

        return $user->roles()->whereIn('name', config('app.view_only_roles'))->doesntExist();
    }

    public function delete(User $user, LearnerAccommodation $learnerAccommodation): bool
    {
        if ($user->roles()->count() === 0) {
            return false;
        }

        return $user->roles()->whereIn('name', config('app.view_only_roles'))->doesntExist();
    }

    public function restore(User $user, LearnerAccommodation $learnerAccommodation): bool
    {
        if ($user->roles()->count() === 0) {
            return false;
        }

        return $user->roles()->whereIn('name', config('app.view_only_roles'))->doesntExist();
    }

    public function forceDelete(User $user, LearnerAccommodation $learnerAccommodation): bool
    {
        if ($user->roles()->count() === 0) {
            return false;
        }

        return $user->roles()->whereIn('name', config('app.view_only_roles'))->doesntExist();
    }
}
