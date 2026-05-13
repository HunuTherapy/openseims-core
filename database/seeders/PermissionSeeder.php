<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'view learner' => 'Permission to view learner records (unique code for non-HQ).',
            'view learner names' => 'Permission to view learner\'s full name (ABAC check for HQ role).',
            'create learner' => 'Permission to create a new learner record (Restricted to Head Teacher/HQ Admin).',
            'edit learner' => 'Permission to update a learner record (Restricted to Head Teacher/HQ Admin).',
            'delete learner' => 'Permission to remove a learner record (Restricted to Head Teacher/HQ Admin, requires HQ approval).',
            'view attendance record' => 'Permission to view attendance records.',
            'record attendance' => 'Permission to record attendance for a class/learner.',
            'edit attendance record' => 'Permission to edit an attendance record.',
            'delete attendance record' => 'Permission to delete an attendance record.',
            'view iep goal' => 'Permission to view Individualized Education Program goals.',
            'create iep goal' => 'Permission to create an IEP goal.',
            'edit iep goal' => 'Permission to update an IEP goal.',
            'delete iep goal' => 'Permission to remove an IEP goal.',
            'view assessment form' => 'Permission to view assessment forms.',
            'create assessment form' => 'Permission to create assessment forms.',
            'edit assessment form' => 'Permission to edit assessment forms.',
            'view school' => 'Permission to view school records.',
            'create school' => 'Permission to create a new school record (District level staff/HQ Admin).',
            'edit school' => 'Permission to update a school record (District level staff/HQ Admin).',
            'delete school' => 'Permission to delete school records.',
            'view condition' => 'Permission to view disability and personal factor library entries.',
            'create condition' => 'Permission to create disability and personal factor library entries.',
            'edit condition' => 'Permission to edit disability and personal factor library entries.',
            'delete condition' => 'Permission to delete disability and personal factor library entries.',
            'view teacher' => 'Permission to view teacher records.',
            'create teacher' => 'Permission to create teacher records.',
            'edit teacher' => 'Permission to edit teacher records.',
            'submit report' => 'Permission to submit a new supervision report.',
            'view all reports' => 'Permission to view all supervision reports within the user\'s geography scope.',
            'view assigned reports' => 'Permission to view supervision reports assigned to the user as recipient within scope.',
            'edit any report' => 'Permission to edit any supervision report within scope.',
            'edit own report' => 'Permission to edit supervision reports authored by the user as supervisor within scope.',
            'manage users' => 'Permission to create, edit, and delete user accounts and assign roles.',
        ];

        Permission::query()
            ->whereNotIn('name', array_keys($permissions))
            ->delete();

        foreach ($permissions as $permission => $description) {
            Permission::query()->updateOrCreate(
                [
                    'name' => $permission,
                    'guard_name' => 'web',
                ],
                [
                    'description' => $description,
                ]
            );
        }
    }
}
