<?php

namespace Tests\Feature\Reporting;

use App\Http\Middleware\SetActiveReportingYear;
use App\Models\User;
use App\Services\Reporting\ActiveReportingYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Tests\TestCase;

class ActiveReportingYearTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        config([
            'seims.active_reporting_year' => (int) env('SEIMS_ACTIVE_REPORTING_YEAR', 2026),
        ]);
    }

    public function test_service_returns_default_year_when_no_cached_value_exists(): void
    {
        $user = User::factory()->create();

        $this->assertSame(
            (int) config('seims.active_reporting_year'),
            app(ActiveReportingYear::class)->current($user),
        );
    }

    public function test_service_persists_year_per_user(): void
    {
        $user = User::factory()->create();
        $service = app(ActiveReportingYear::class);

        $service->setForUser($user, 2023);

        $this->assertSame(2023, $service->current($user));
    }

    public function test_service_rejects_invalid_years(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(ActiveReportingYear::class)->setForUser(User::factory()->create(), 2019);
    }

    public function test_middleware_applies_cached_year_to_runtime_config_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $service = app(ActiveReportingYear::class);
        $service->setForUser($user, 2022);

        Auth::login($user);

        $request = Request::create('/dashboard', 'GET');
        $request->setUserResolver(fn () => $user);

        app(SetActiveReportingYear::class)->handle($request, function () {
            return response('ok');
        });

        $this->assertSame(2022, (int) config('seims.active_reporting_year'));
    }
}
