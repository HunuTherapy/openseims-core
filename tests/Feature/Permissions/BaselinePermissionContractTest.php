<?php

namespace Tests\Feature\Permissions;

use App\Filament\Admin\Resources\UserResource;
use App\Filament\Resources\AssessmentFormResource;
use App\Filament\Resources\AttendanceRecordResource;
use App\Filament\Resources\IepGoalResource;
use App\Filament\Resources\LearnerResource;
use App\Filament\Resources\SchoolResource;
use App\Filament\Resources\SupervisionResource;
use App\Filament\Resources\TeacherResource;
use App\Policies\AssessmentFormPolicy;
use App\Policies\AttendanceRecordPolicy;
use App\Policies\ConditionPolicy;
use App\Policies\IepGoalPolicy;
use App\Policies\LearnerPolicy;
use App\Policies\SchoolPolicy;
use App\Policies\SupervisionReportPolicy;
use App\Policies\TeacherPolicy;

class BaselinePermissionContractTest extends BaselinePermissionTestCase
{
    public function test_seeded_roles_match_core_resource_access_matrix(): void
    {
        $this->assertResourcePageAccess(LearnerResource::class, 'index', self::ROLE_NAMES);
        $this->assertResourcePageAccess(LearnerResource::class, 'create', [
            'national_admin',
            'school_coordinator',
        ]);

        $this->assertResourcePageAccess(AttendanceRecordResource::class, 'create', [
            'national_admin',
            'school_coordinator',
        ]);

        $this->assertResourcePageAccess(IepGoalResource::class, 'create', self::ROLE_NAMES);
        $this->assertResourcePageAccess(AssessmentFormResource::class, 'create', [
            'national_admin',
            'district_officer',
        ]);
        $this->assertResourcePageAccess(SchoolResource::class, 'create', self::ROLE_NAMES);
        $this->assertResourcePageAccess(TeacherResource::class, 'create', self::ROLE_NAMES);
        $this->assertResourcePageAccess(SupervisionResource::class, 'index', [
            'national_admin',
            'district_officer',
        ]);
        $this->assertResourcePageAccess(SupervisionResource::class, 'my-reports', self::ROLE_NAMES);
    }

    public function test_seeded_roles_match_core_policy_matrix(): void
    {
        $learnerPolicy = new LearnerPolicy;
        $attendancePolicy = new AttendanceRecordPolicy;
        $iepGoalPolicy = new IepGoalPolicy;
        $assessmentFormPolicy = new AssessmentFormPolicy;
        $schoolPolicy = new SchoolPolicy;
        $conditionPolicy = new ConditionPolicy;
        $teacherPolicy = new TeacherPolicy;
        $supervisionPolicy = new SupervisionReportPolicy;

        $this->assertPolicyAccess('learner deletion', [
            'national_admin',
            'school_coordinator',
        ], fn ($user) => $learnerPolicy->delete($user, $this->learner));

        $this->assertPolicyAccess('attendance deletion', [
            'national_admin',
            'school_coordinator',
        ], fn ($user) => $attendancePolicy->delete($user, $this->attendanceRecord));

        $this->assertPolicyAccess('iep goal deletion', [
            'national_admin',
            'school_coordinator',
        ], fn ($user) => $iepGoalPolicy->delete($user, $this->iepGoal));

        $this->assertPolicyAccess('assessment form update', [
            'national_admin',
            'district_officer',
        ], fn ($user) => $assessmentFormPolicy->update($user, $this->assessmentForm));

        $this->assertPolicyAccess('school deletion', self::ROLE_NAMES, fn ($user) => $schoolPolicy->delete($user, $this->school));
        $this->assertPolicyAccess('condition deletion', self::ROLE_NAMES, fn ($user) => $conditionPolicy->delete($user, $this->condition));
        $this->assertPolicyAccess('teacher deletion', [], fn ($user) => $teacherPolicy->delete($user, $this->teacher));
        $this->assertPolicyAccess('supervision report view any', [
            'national_admin',
            'district_officer',
            'school_coordinator',
        ], fn ($user) => $supervisionPolicy->viewAny($user));
    }

    public function test_only_national_admin_can_access_admin_user_resource_ui(): void
    {
        $this->assertResourcePageAccess(UserResource::class, 'index', [
            'national_admin',
        ], panel: 'admin');
    }
}
