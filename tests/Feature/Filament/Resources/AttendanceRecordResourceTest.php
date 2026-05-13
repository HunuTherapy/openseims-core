<?php

namespace Tests\Feature\Filament\Resources;

use App\Enums\LearnerClass;
use App\Filament\Resources\AttendanceRecordResource\Pages\CreateAttendanceRecord;
use App\Filament\Resources\AttendanceRecordResource\Pages\EditAttendanceRecord;
use App\Filament\Resources\AttendanceRecordResource\Pages\ListAttendanceRecords;
use App\Models\AttendanceRecord;
use App\Models\Learner;
use App\Models\Teacher;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Tests\Feature\Filament\SeimsResourceTestCase;

class AttendanceRecordResourceTest extends SeimsResourceTestCase
{
    public function test_can_list_attendance_records(): void
    {
        $records = AttendanceRecord::factory()->count(3)->create();

        Livewire::test(ListAttendanceRecords::class)
            ->assertOk()
            ->assertCanSeeTableRecords($records);
    }

    public function test_can_create_attendance_record(): void
    {
        $class = LearnerClass::P1->value;
        $teacher = Teacher::factory()->create([
            'class' => $class,
        ]);
        $learner = Learner::factory()->create([
            'class' => $class,
        ]);

        $data = [
            'teacher_id' => $teacher->id,
            'class' => $class,
            'date' => '2024-06-10',
        ];

        $component = Livewire::test(CreateAttendanceRecord::class)
            ->fillForm($data);

        $entries = $component->get('data.attendance_entries');
        $entryKey = is_array($entries) ? array_key_first($entries) : null;

        if ($entryKey) {
            $component
                ->set("data.attendance_entries.{$entryKey}.learner_id", $learner->id)
                ->set("data.attendance_entries.{$entryKey}.present", true)
                ->set("data.attendance_entries.{$entryKey}.reason", null);
        }

        $component
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(AttendanceRecord::class, [
            'teacher_id' => $teacher->id,
            'learner_id' => $learner->id,
            'class' => $class,
            'date' => '2024-06-10 00:00:00',
            'present' => true,
        ]);
    }

    public function test_attendance_record_requires_minimum_fields(): void
    {
        Livewire::test(CreateAttendanceRecord::class)
            ->fillForm([
                'teacher_id' => null,
                'class' => null,
                'date' => null,
                'attendance_entries' => [],
            ])
            ->call('create')
            ->assertHasFormErrors([
                'teacher_id' => 'required',
                'class' => 'required',
                'date' => 'required',
                'attendance_entries' => 'min',
            ]);
    }

    public function test_can_edit_attendance_record(): void
    {
        $record = AttendanceRecord::factory()->create();

        Livewire::test(EditAttendanceRecord::class, ['record' => $record->id])
            ->fillForm([
                'present' => false,
                'reason' => 'Sick',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(AttendanceRecord::class, [
            'id' => $record->id,
            'present' => false,
            'reason' => 'Sick',
        ]);
    }

    public function test_can_delete_attendance_records(): void
    {
        $records = AttendanceRecord::factory()->count(2)->create();

        Livewire::test(ListAttendanceRecords::class)
            ->selectTableRecords($records)
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
            ->assertNotified();

        $records->each(fn (AttendanceRecord $record) => $this->assertDatabaseMissing('attendance_records', ['id' => $record->id]));
    }
}
