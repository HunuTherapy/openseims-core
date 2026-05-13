<?php

namespace Tests\Feature\Filament;

abstract class AdminResourceTestCase extends FilamentTestCase
{
    protected function setFilamentPanel(): void
    {
        $this->usePanel('admin');
    }

    protected function getUserRoleName(): string
    {
        return 'national_admin';
    }
}
