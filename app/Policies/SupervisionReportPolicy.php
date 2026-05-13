<?php

namespace App\Policies;

use App\Models\SupervisionReport;
use App\Models\User;

class SupervisionReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['view all reports', 'view assigned reports']);
    }

    public function view(User $user, SupervisionReport $supervisionReport): bool
    {
        if (! $this->withinGeography($user, $supervisionReport)) {
            return false;
        }

        return $user->hasPermissionTo('view all reports')
            || $supervisionReport->supervisor_id === $user->id
            || ($user->hasPermissionTo('view assigned reports')
                && $supervisionReport->recipient_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('submit report');
    }

    public function update(User $user, SupervisionReport $supervisionReport): bool
    {
        if (! $this->withinGeography($user, $supervisionReport)) {
            return false;
        }

        return $user->hasPermissionTo('edit any report')
            || ($user->hasPermissionTo('edit own report')
                && $supervisionReport->supervisor_id === $user->id);
    }

    public function delete(User $user, SupervisionReport $supervisionReport): bool
    {
        return false;
    }

    public function restore(User $user, SupervisionReport $supervisionReport): bool
    {
        return false;
    }

    public function forceDelete(User $user, SupervisionReport $supervisionReport): bool
    {
        return false;
    }

    private function withinGeography(User $user, SupervisionReport $supervisionReport): bool
    {
        if ($user->hasFullDataAccess()) {
            return true;
        }

        $scope = $user->geographyScope();

        if ($scope['schoolId']) {
            return $supervisionReport->school_id === $scope['schoolId'];
        }

        $districtId = $supervisionReport->school?->district_id;

        if ($scope['districtId']) {
            return $districtId === $scope['districtId'];
        }

        $regionId = $supervisionReport->school?->district?->region_id;

        if ($scope['regionId']) {
            return $regionId === $scope['regionId'];
        }

        return false;
    }
}
