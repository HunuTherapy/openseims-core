<?php

namespace App\Policies;

use App\Models\AccommodationType;
use App\Models\User;

class AccommodationTypePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AccommodationType $accommodationType): bool
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

    public function update(User $user, AccommodationType $accommodationType): bool
    {
        if ($user->roles()->count() === 0) {
            return false;
        }

        return $user->roles()->whereIn('name', config('app.view_only_roles'))->doesntExist();
    }

    public function delete(User $user, AccommodationType $accommodationType): bool
    {
        if ($user->roles()->count() === 0) {
            return false;
        }

        return $user->roles()->whereIn('name', config('app.view_only_roles'))->doesntExist();
    }

    public function restore(User $user, AccommodationType $accommodationType): bool
    {
        if ($user->roles()->count() === 0) {
            return false;
        }

        return $user->roles()->whereIn('name', config('app.view_only_roles'))->doesntExist();
    }

    public function forceDelete(User $user, AccommodationType $accommodationType): bool
    {
        if ($user->roles()->count() === 0) {
            return false;
        }

        return $user->roles()->whereIn('name', config('app.view_only_roles'))->doesntExist();
    }
}
