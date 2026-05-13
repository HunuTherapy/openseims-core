<?php

namespace App\Policies;

use App\Models\Learner;
use App\Models\User;

class LearnerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view learner');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Learner $learner): bool
    {
        return $user->hasPermissionTo('view learner')
            && $this->withinGeography($user, $learner);
    }

    public function viewNames(User $user, Learner $learner): bool
    {
        return $user->hasPermissionTo('view learner names');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create learner');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Learner $learner): bool
    {
        return $user->hasPermissionTo('edit learner');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Learner $learner): bool
    {
        return $user->hasPermissionTo('delete learner');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo('delete learner');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Learner $learner): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Learner $learner): bool
    {
        return false;
    }

    private function withinGeography(User $user, Learner $learner): bool
    {
        if ($user->hasFullDataAccess()) {
            return true;
        }

        $scope = $user->geographyScope();

        if ($scope['schoolId']) {
            return $learner->school_id === $scope['schoolId'];
        }

        if ($scope['districtId']) {
            return $learner->school?->district_id === $scope['districtId'];
        }

        if ($scope['regionId']) {
            return $learner->school?->district?->region_id === $scope['regionId'];
        }

        return false;
    }
}
