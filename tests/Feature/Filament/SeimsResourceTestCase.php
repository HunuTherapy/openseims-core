<?php

namespace Tests\Feature\Filament;

abstract class SeimsResourceTestCase extends FilamentTestCase
{
    protected function setFilamentPanel(): void
    {
        $this->usePanel('seims');
    }
}
