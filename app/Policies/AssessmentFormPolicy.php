<?php

namespace App\Policies;

use App\Models\AssessmentForm;
use App\Models\User;

class AssessmentFormPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view assessment form');
    }

    public function view(User $user, AssessmentForm $assessmentForm): bool
    {
        return $user->hasPermissionTo('view assessment form');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create assessment form');
    }

    public function update(User $user, AssessmentForm $assessmentForm): bool
    {
        return $user->hasPermissionTo('edit assessment form');
    }

    public function delete(User $user, AssessmentForm $assessmentForm): bool
    {
        if ($user->roles()->count() === 0) {
            return false;
        }

        return $user->roles()->whereIn('name', config('app.view_only_roles'))->doesntExist();
    }

    public function restore(User $user, AssessmentForm $assessmentForm): bool
    {
        if ($user->roles()->count() === 0) {
            return false;
        }

        return $user->roles()->whereIn('name', config('app.view_only_roles'))->doesntExist();
    }

    public function forceDelete(User $user, AssessmentForm $assessmentForm): bool
    {
        if ($user->roles()->count() === 0) {
            return false;
        }

        return $user->roles()->whereIn('name', config('app.view_only_roles'))->doesntExist();
    }
}
