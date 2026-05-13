<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use App\Models\Teacher;
use App\Support\TeacherUserAccountManager;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditTeacherBasicInfo extends EditRecord
{
    protected static string $resource = TeacherResource::class;

    protected ?string $teacherEmail = null;

    public static function getNavigationLabel(): string
    {
        return 'Basic Information';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components(TeacherResource::basicInfoSchema());
    }

    public function getRelationManagers(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['email'] = $this->record->user?->email;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->teacherEmail = $data['email'] ?? null;
        unset($data['email']);

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var Teacher $teacher */
        $teacher = $this->record;

        app(TeacherUserAccountManager::class)->ensureForTeacher($teacher, $this->teacherEmail);
    }
}
