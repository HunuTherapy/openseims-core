<?php

namespace App\Support;

use App\Enums\TeacherType;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TeacherUserAccountManager
{
    public function ensureForTeacher(Teacher $teacher, ?string $email = null): ?User
    {
        $teacherType = $teacher->teacher_type instanceof TeacherType
            ? $teacher->teacher_type
            : TeacherType::normalize((string) $teacher->teacher_type);

        if (! $teacherType?->requiresUserAccount()) {
            return $teacher->user;
        }

        $teacher->loadMissing('user');

        $email = trim((string) ($email ?: $teacher->user?->email));

        if ($email === '') {
            throw ValidationException::withMessages([
                'email' => 'Email is required for school coordinators.',
            ]);
        }

        $this->ensureEmailIsAvailable($teacher, $email);

        $school = School::withoutGlobalScopes()
            ->with('district')
            ->find($teacher->school_id);

        $user = $teacher->user ?? new User;
        $user->fill([
            'name' => trim($teacher->first_name.' '.$teacher->last_name),
            'email' => $email,
            'region_id' => $school?->district?->region_id,
            'district_id' => $school?->district_id,
            'school_id' => $school?->id,
        ]);

        if (! $user->exists) {
            $user->password = Hash::make(config('seims.imports.default_password', 'Pass1234'));
        }

        $user->save();
        $user->syncRoles([TeacherType::SCHOOL_COORDINATOR->value === $teacherType->value ? 'school_coordinator' : $teacherType->value]);

        if ($teacher->user_id !== $user->id) {
            $teacher->user()->associate($user);
            $teacher->saveQuietly();
        }

        return $user;
    }

    protected function ensureEmailIsAvailable(Teacher $teacher, string $email): void
    {
        $query = User::query()->where('email', $email);

        if ($teacher->user_id) {
            $query->whereKeyNot($teacher->user_id);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'email' => 'This email address is already in use.',
            ]);
        }
    }
}
