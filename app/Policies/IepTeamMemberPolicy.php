<?php

namespace App\Policies;

use App\Models\IepTeamMember;
use App\Models\User;

class IepTeamMemberPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, IepTeamMember $iepTeamMember): bool
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

    public function update(User $user, IepTeamMember $iepTeamMember): bool
    {
        if ($user->roles()->count() === 0) {
            return false;
        }

        return $user->roles()->whereIn('name', config('app.view_only_roles'))->doesntExist();
    }

    public function delete(User $user, IepTeamMember $iepTeamMember): bool
    {
        if ($user->roles()->count() === 0) {
            return false;
        }

        return $user->roles()->whereIn('name', config('app.view_only_roles'))->doesntExist();
    }

    public function restore(User $user, IepTeamMember $iepTeamMember): bool
    {
        if ($user->roles()->count() === 0) {
            return false;
        }

        return $user->roles()->whereIn('name', config('app.view_only_roles'))->doesntExist();
    }

    public function forceDelete(User $user, IepTeamMember $iepTeamMember): bool
    {
        if ($user->roles()->count() === 0) {
            return false;
        }

        return $user->roles()->whereIn('name', config('app.view_only_roles'))->doesntExist();
    }
}
