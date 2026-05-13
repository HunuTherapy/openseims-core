<?php

return [
    'active_reporting_year' => (int) env('SEIMS_ACTIVE_REPORTING_YEAR', 2026),
    'allowed_reporting_years' => range(2020, 2026),
    'roles' => [
        'sync_permissions_on_seed' => (bool) env('SEIMS_SYNC_ROLE_PERMISSIONS_ON_SEED', false),
    ],
    'imports' => [
        'default_password' => env('SEIMS_IMPORTED_USER_DEFAULT_PASSWORD', 'Pass1234'),
    ],
];
