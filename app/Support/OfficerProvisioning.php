<?php

namespace App\Support;

use App\Models\District;
use App\Models\Officer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OfficerProvisioning
{
    /**
     * Returns the app role names that are considered officers.
     *
     * @return list<string>
     */
    public static function supportedRoleNames(): array
    {
        return [
            'district_officer',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function roleOptions(): array
    {
        $roles = Role::query()
            ->whereIn('name', self::supportedRoleNames())
            ->pluck('display_name', 'name')
            ->all();

        $orderedRoles = [];

        foreach (self::supportedRoleNames() as $roleName) {
            if (isset($roles[$roleName])) {
                $orderedRoles[$roleName] = $roles[$roleName];
            }
        }

        return $orderedRoles;
    }

    public static function normalizeAppRole(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $normalized = Str::of($value)
            ->trim()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();

        return in_array($normalized, self::supportedRoleNames(), true)
            ? $normalized
            : null;
    }

    public function roleDisplayName(?string $roleName): string
    {
        if (blank($roleName)) {
            return 'Officer';
        }

        return Role::query()
            ->where('name', $roleName)
            ->value('display_name')
            ?? Str::of($roleName)->replace('_', ' ')->title()->toString();
    }

    /**
     * @param  array{name:string,email:string,phone:string,formal_training:bool,is_deployed:bool}  $officerData
     */
    public function createOfficerWithUser(array $officerData, string $appRole, District $district): Officer
    {
        return DB::transaction(function () use ($officerData, $appRole, $district): Officer {
            $user = User::query()->create([
                'name' => $officerData['name'],
                'email' => $officerData['email'],
                'password' => Hash::make(config('seims.imports.default_password', 'Pass1234')),
                'region_id' => $district->region_id,
                'district_id' => $district->id,
            ]);

            $user->assignRole($appRole);

            return Officer::query()->create([
                'name' => $officerData['name'],
                'role' => $this->roleDisplayName($appRole),
                'formal_training' => $officerData['formal_training'],
                'phone' => $officerData['phone'],
                'is_deployed' => $officerData['is_deployed'],
                'user_id' => $user->id,
            ]);
        });
    }
}
