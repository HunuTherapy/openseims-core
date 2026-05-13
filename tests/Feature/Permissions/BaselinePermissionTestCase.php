<?php

namespace Tests\Feature\Permissions;

use App\Models\AssessmentForm;
use App\Models\AttendanceRecord;
use App\Models\Condition;
use App\Models\IepGoal;
use App\Models\Learner;
use App\Models\Role;
use App\Models\School;
use App\Models\SupervisionReport;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

abstract class BaselinePermissionTestCase extends TestCase
{
    use RefreshDatabase;

    protected const ROLE_NAMES = [
        'national_admin',
        'district_officer',
        'school_coordinator',
    ];

    protected School $school;

    protected Learner $learner;

    protected Teacher $teacher;

    protected AttendanceRecord $attendanceRecord;

    protected IepGoal $iepGoal;

    protected Condition $condition;

    protected AssessmentForm $assessmentForm;

    protected SupervisionReport $supervisionReport;

    protected User $managedUser;

    protected function setUp(): void
    {
        parent::setUp();

        ini_set('memory_limit', '512M');

        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->school = School::factory()->create();
        $this->school->load('district');

        $this->learner = Learner::factory()->create([
            'school_id' => $this->school->id,
            'class' => 'P3',
        ]);

        $this->teacher = Teacher::factory()->create([
            'school_id' => $this->school->id,
            'class' => 'P3',
        ]);

        $this->attendanceRecord = AttendanceRecord::factory()->create([
            'teacher_id' => $this->teacher->id,
            'learner_id' => $this->learner->id,
            'class' => 'P3',
        ]);

        $this->iepGoal = IepGoal::factory()->create([
            'learner_id' => $this->learner->id,
        ]);

        $this->condition = Condition::factory()->create();
        $this->assessmentForm = AssessmentForm::factory()->create();
        $this->managedUser = User::factory()->create();

        $this->supervisionReport = SupervisionReport::factory()->create([
            'school_id' => $this->school->id,
            'supervisor_id' => $this->managedUser->id,
            'recipient_id' => $this->managedUser->id,
        ]);
    }

    protected function makeUserForRole(string $roleName): User
    {
        $user = User::factory()->create($this->roleScopeAttributes($roleName));
        $user->assignRole(Role::query()->where('name', $roleName)->where('guard_name', 'web')->firstOrFail());
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user;
    }

    /**
     * @param  array<int, string>  $allowedRoles
     * @param  array<string, mixed>  $parameters
     */
    protected function assertResourcePageAccess(string $resourceClass, string $page, array $allowedRoles, array $parameters = [], string $panel = 'seims'): void
    {
        $url = $resourceClass::getUrl($page, $parameters, panel: $panel);

        foreach (self::ROLE_NAMES as $roleName) {
            $user = $this->makeUserForRole($roleName);
            $response = $this->actingAs($user)->get($url);
            $shouldAllow = in_array($roleName, $allowedRoles, true);

            $this->assertSame(
                $shouldAllow ? 200 : 403,
                $response->getStatusCode(),
                sprintf('Expected role [%s] to %s %s::%s, got [%s].', $roleName, $shouldAllow ? 'access' : 'be denied from', class_basename($resourceClass), $page, $response->getStatusCode())
            );
        }
    }

    /**
     * @param  array<int, string>  $allowedRoles
     */
    protected function assertPolicyAccess(string $label, array $allowedRoles, callable $callback): void
    {
        foreach (self::ROLE_NAMES as $roleName) {
            $user = $this->makeUserForRole($roleName);
            $expected = in_array($roleName, $allowedRoles, true);

            $this->assertSame(
                $expected,
                (bool) $callback($user),
                sprintf('Expected role [%s] to %s [%s].', $roleName, $expected ? 'be allowed for' : 'be denied from', $label)
            );
        }
    }

    /**
     * @return array<string, int>
     */
    protected function roleScopeAttributes(string $roleName): array
    {
        $regionId = $this->school->district?->region_id;
        $districtId = $this->school->district_id;
        $schoolId = $this->school->id;

        return match ($roleName) {
            'district_officer' => [
                'region_id' => $regionId,
                'district_id' => $districtId,
            ],
            'school_coordinator' => [
                'region_id' => $regionId,
                'district_id' => $districtId,
                'school_id' => $schoolId,
            ],
            default => [],
        };
    }
}
