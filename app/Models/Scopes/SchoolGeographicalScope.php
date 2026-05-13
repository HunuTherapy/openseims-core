<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class SchoolGeographicalScope implements Scope
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
            $builder->whereKey($scope['schoolId']);

            return;
        }

        if ($scope['districtId']) {
            $builder->where('district_id', $scope['districtId']);

            return;
        }

        if ($scope['regionId']) {
            $builder->whereHas('district', function (Builder $query) use ($scope): void {
                $query->where('region_id', $scope['regionId']);
            });

            return;
        }

        $builder->whereRaw('1 = 0');
    }
}
