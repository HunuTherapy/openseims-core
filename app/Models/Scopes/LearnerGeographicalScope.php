<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class LearnerGeographicalScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        if (! $user || $user->hasFullDataAccess()) {
            return;
        }

        $scope = $user->geographyScope();

        if ($scope['schoolId']) {
            $builder->where('school_id', $scope['schoolId']);

            return;
        }

        if ($scope['districtId']) {
            $builder->whereHas('school', function (Builder $query) use ($scope): void {
                $query->where('district_id', $scope['districtId']);
            });

            return;
        }

        if ($scope['regionId']) {
            $builder->whereHas('school', function (Builder $query) use ($scope): void {
                $query->whereHas('district', fn (Builder $districtQuery) => $districtQuery->where('region_id', $scope['regionId']));
            });

            return;
        }

        $builder->whereRaw('1 = 0');
    }
}
