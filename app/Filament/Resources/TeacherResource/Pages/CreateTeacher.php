<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use App\Models\Teacher;
use App\Support\TeacherUserAccountManager;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTeacher extends CreateRecord
{
    protected static string $resource = TeacherResource::class;

    protected ?string $teacherEmail = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->teacherEmail = $data['email'] ?? null;
        unset($data['email']);

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        /** @var Teacher $teacher */
        $teacher = static::getModel()::query()->create($data);

        app(TeacherUserAccountManager::class)->ensureForTeacher($teacher, $this->teacherEmail);

        return $teacher;
    }

    protected function getRedirectUrl(): string
    {
        return TeacherResource::getUrl('view', ['record' => $this->record]);
    }
}
