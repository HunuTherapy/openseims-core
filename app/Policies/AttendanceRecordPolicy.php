<?php

namespace App\Policies;

use App\Models\AttendanceRecord;
use App\Models\User;

class AttendanceRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view attendance record');
    }

    public function view(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return $user->hasPermissionTo('view attendance record');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('record attendance');
    }

    public function update(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return $user->hasPermissionTo('edit attendance record');
    }

    public function confirm(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return $user->hasPermissionTo('edit attendance record');
    }

    public function delete(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return $user->hasPermissionTo('delete attendance record');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo('delete attendance record');
    }

    public function restore(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return false;
    }

    public function forceDelete(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return false;
    }
}
