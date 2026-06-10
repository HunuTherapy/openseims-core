<?php

namespace Tests;

use Database\Seeders\RegionDistrictSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\URL;
use Spatie\Activitylog\ActivityLogStatus;

abstract class TestCase extends BaseTestCase
{
    protected bool $seed = true;

    protected string $seeder = RegionDistrictSeeder::class;

    protected function setUp(): void
    {
        parent::setUp();

        URL::forceRootUrl(config('app.url'));

        config([
            'activitylog.enabled' => false,
        ]);

        app(ActivityLogStatus::class)->disable();
    }
}
