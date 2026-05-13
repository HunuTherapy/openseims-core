<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\SeimsPanelProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    SeimsPanelProvider::class,
];
