<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditTeacherQualifications extends EditRecord
{
    protected static string $resource = TeacherResource::class;

    public static function getNavigationLabel(): string
    {
        return 'Qualifications';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components(TeacherResource::qualificationsSchema());
    }
}
