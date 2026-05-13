<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\School;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Spatie\Activitylog\ActivityLogStatus;
use Tests\Feature\Filament\AdminResourceTestCase;

class AuditTrailTest extends AdminResourceTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'activitylog.enabled' => true,
        ]);
        app(ActivityLogStatus::class)->enable();

        Activity::query()->delete();
    }

    public function test_school_model_changes_are_logged_with_old_and_new_values(): void
    {
        $school = School::factory()->create([
            'name' => 'Original School',
        ]);

        $school->update([
            'name' => 'Updated School',
        ]);

        $createdLog = Activity::query()
            ->where('event', 'created')
            ->where('subject_type', School::class)
            ->first();

        $updatedLog = Activity::query()
            ->where('event', 'updated')
            ->where('subject_type', School::class)
            ->latest('id')
            ->first();

        $this->assertNotNull($createdLog);
        $this->assertSame('School', $createdLog->module);
        $this->assertSame('Original School', $createdLog->subject_label);
        $this->assertSame('Updated School', $updatedLog?->getExtraProperty('attributes.name'));
        $this->assertSame('Original School', $updatedLog?->getExtraProperty('old.name'));
        $this->assertSame($this->user->id, $updatedLog?->causer_id);
    }

    public function test_authentication_events_are_logged(): void
    {
        event(new Login('web', $this->user, false));
        event(new Logout('web', $this->user));

        $loginLog = Activity::query()->where('event', 'login')->latest('id')->first();
        $logoutLog = Activity::query()->where('event', 'logout')->latest('id')->first();

        $this->assertNotNull($loginLog);
        $this->assertSame('Authentication', $loginLog->module);
        $this->assertSame($this->user->email, $loginLog->subject_identifier);

        $this->assertNotNull($logoutLog);
        $this->assertSame($this->user->id, $logoutLog->causer_id);
    }
}
