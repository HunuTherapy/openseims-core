<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\TeacherResource\Pages\CreateTeacher;
use App\Filament\Resources\TeacherResource\Pages\EditTeacherBasicInfo;
use App\Filament\Resources\TeacherResource\Pages\ListTeachers;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Livewire\Livewire;
use Tests\Feature\Filament\SeimsResourceTestCase;

class TeacherResourceTest extends SeimsResourceTestCase
{
    public function test_can_list_teachers(): void
    {
        $records = Teacher::factory()->count(3)->create();

        Livewire::test(ListTeachers::class)
            ->assertOk()
            ->assertCanSeeTableRecords($records);
    }

    public function test_can_create_teacher(): void
    {
        $school = School::factory()->create();

        Livewire::test(CreateTeacher::class)
            ->fillForm([
                'teacher_no' => 'TCH-1001',
                'school_id' => $school->id,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'class' => 'P1',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Teacher::class, [
            'teacher_no' => 'TCH-1001',
            'school_id' => $school->id,
        ]);
    }

    public function test_teacher_requires_minimum_fields(): void
    {
        Livewire::test(CreateTeacher::class)
            ->fillForm([
                'teacher_no' => null,
                'school_id' => null,
                'first_name' => null,
                'last_name' => null,
                'class' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'teacher_no' => 'required',
                'school_id' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                'class' => 'required',
            ]);
    }

    public function test_can_edit_teacher(): void
    {
        $record = Teacher::factory()->create();

        Livewire::test(EditTeacherBasicInfo::class, ['record' => $record->id])
            ->fillForm([
                'first_name' => 'Updated',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Teacher::class, [
            'id' => $record->id,
            'first_name' => 'Updated',
        ]);
    }

    public function test_cannot_delete_teachers(): void
    {
        $records = Teacher::factory()->count(2)->create();

        Livewire::test(ListTeachers::class)
            ->assertTableBulkActionHidden(DeleteBulkAction::class);

        $records->each(fn (Teacher $record) => $this->assertDatabaseHas('teachers', ['id' => $record->id]));
    }

    public function test_creating_school_coordinator_creates_linked_user_account(): void
    {
        $school = School::factory()->create();

        Livewire::test(CreateTeacher::class)
            ->fillForm([
                'teacher_no' => 'TCH-2001',
                'teacher_type' => 'school_coordinator',
                'school_id' => $school->id,
                'first_name' => 'Akua',
                'last_name' => 'Mensah',
                'class' => 'P1',
            ])
            ->fillForm([
                'email' => 'akua.mensah@example.com',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $teacher = Teacher::query()->where('teacher_no', 'TCH-2001')->firstOrFail();

        $this->assertNotNull($teacher->user_id);
        $this->assertDatabaseHas(User::class, [
            'id' => $teacher->user_id,
            'email' => 'akua.mensah@example.com',
            'school_id' => $school->id,
        ]);
        $this->assertTrue($teacher->user()->firstOrFail()->hasRole('school_coordinator'));
    }
}
