<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $syncPermissionsOnSeed = (bool) config('seims.roles.sync_permissions_on_seed', false);

        $roles = [
            'national_admin' => [
                'name' => 'National Admin',
                'description' => 'Oversees system setup, national reporting, user management, and aggregated data review.',
                'permissions' => [
                    'view learner',
                    'view learner names',
                    'create learner',
                    'edit learner',
                    'delete learner',
                    'view attendance record',
                    'record attendance',
                    'edit attendance record',
                    'delete attendance record',
                    'view iep goal',
                    'create iep goal',
                    'edit iep goal',
                    'delete iep goal',
                    'view assessment form',
                    'create assessment form',
                    'edit assessment form',
                    'view school',
                    'create school',
                    'edit school',
                    'delete school',
                    'view condition',
                    'create condition',
                    'edit condition',
                    'delete condition',
                    'view teacher',
                    'create teacher',
                    'edit teacher',
                    'submit report',
                    'view all reports',
                    'view assigned reports',
                    'edit any report',
                    'edit own report',
                    'manage users',
                ],
            ],
            'national_sped_officer' => [
                'name' => 'National SPED Officer',
                'description' => 'Nationwide read-only oversight for special education.',
                'permissions' => [
                    'view learner',
                    'view learner names',
                    'view attendance record',
                    'view iep goal',
                    'view assessment form',
                    'view school',
                    'view condition',
                    'view teacher',
                    'view all reports',
                    'view assigned reports',
                ],
            ],
            'regional_education_director' => [
                'name' => 'Regional Education Director',
                'description' => 'Provides oversight and supports implementation at regional level.',
                // Keep parity with District Officer; geography scoping enforces region-only access.
                'permissions' => [
                    'view learner',
                    'view attendance record',
                    'view iep goal',
                    'create iep goal',
                    'edit iep goal',
                    'view assessment form',
                    'create assessment form',
                    'edit assessment form',
                    'view school',
                    'create school',
                    'edit school',
                    'delete school',
                    'view condition',
                    'create condition',
                    'edit condition',
                    'delete condition',
                    'view teacher',
                    'create teacher',
                    'edit teacher',
                    'submit report',
                    'view all reports',
                    'view assigned reports',
                    'edit own report',
                ],
            ],
            'district_officer' => [
                'name' => 'District Officer',
                'description' => 'Monitors schools, reviews submissions, conducts supervision, and supports implementation at district level.',
                'permissions' => [
                    'view learner',
                    'view attendance record',
                    'view iep goal',
                    'create iep goal',
                    'edit iep goal',
                    'view assessment form',
                    'create assessment form',
                    'edit assessment form',
                    'view school',
                    'create school',
                    'edit school',
                    'delete school',
                    'view condition',
                    'create condition',
                    'edit condition',
                    'delete condition',
                    'view teacher',
                    'create teacher',
                    'edit teacher',
                    'submit report',
                    'view all reports',
                    'view assigned reports',
                    'edit own report',
                ],
            ],
            'school_coordinator' => [
                'name' => 'School Coordinator',
                'description' => 'Manages school records, learner data, attendance, IEPs, and school-level submissions.',
                'permissions' => [
                    'view learner',
                    'create learner',
                    'edit learner',
                    'delete learner',
                    'view attendance record',
                    'record attendance',
                    'edit attendance record',
                    'delete attendance record',
                    'view iep goal',
                    'create iep goal',
                    'edit iep goal',
                    'delete iep goal',
                    'view assessment form',
                    'view school',
                    'create school',
                    'edit school',
                    'delete school',
                    'view condition',
                    'create condition',
                    'edit condition',
                    'delete condition',
                    'view teacher',
                    'create teacher',
                    'edit teacher',
                    'submit report',
                    'view assigned reports',
                    'edit own report',
                ],
            ],
        ];

        Role::query()
            ->whereNotIn('name', array_keys($roles))
            ->delete();

        foreach ($roles as $name => $data) {
            $role = Role::query()->firstOrNew([
                'name' => $name,
                'guard_name' => 'web',
            ]);

            $newlyCreated = ! $role->exists;

            $role->fill([
                'description' => $data['description'],
                'display_name' => $data['name'],
                'supervisor_role_id' => null,
            ]);

            $role->save();

            if ($syncPermissionsOnSeed || $newlyCreated) {
                $role->syncPermissions($data['permissions']);
            }
        }

        $supervisorMap = [
            'national_admin' => null,
            'national_sped_officer' => 'national_admin',
            'regional_education_director' => 'national_sped_officer',
            'district_officer' => 'regional_education_director',
            'school_coordinator' => 'district_officer',
        ];

        foreach ($supervisorMap as $roleName => $supervisorRoleName) {
            $role = Role::query()->where('name', $roleName)->where('guard_name', 'web')->first();

            if (! $role) {
                continue;
            }

            $supervisorRoleId = $supervisorRoleName
                ? Role::query()->where('name', $supervisorRoleName)->where('guard_name', 'web')->value('id')
                : null;

            $role->forceFill(['supervisor_role_id' => $supervisorRoleId])->save();
        }
    }
}
