<?php

namespace App\Models;

use App\Enums\DiagnosisStatus;
use App\Enums\DisabilityOnset;
use App\Enums\SeverityLevel;
use App\Enums\SeveritySource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TeacherCondition extends Pivot
{
    use HasFactory;

    protected $table = 'teacher_condition';

    public $timestamps = false;

    protected $fillable = [
        'teacher_id',
        'condition_id',
        'assessment_id',
        'is_primary',
        'status',
        'severity_level',
        'disability_onset',
        'severity_source',
        'assigned_at',
        'notes',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'assigned_at' => 'date',
        'status' => DiagnosisStatus::class,
        'severity_level' => SeverityLevel::class,
        'severity_source' => SeveritySource::class,
        'disability_onset' => DisabilityOnset::class,
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function condition()
    {
        return $this->belongsTo(Condition::class);
    }
}
