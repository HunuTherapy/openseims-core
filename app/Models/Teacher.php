<?php

namespace App\Models;

use App\Enums\TeacherType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_no',
        'teacher_type',
        'school_id',
        'first_name',
        'last_name',
        'user_id',
        'class',
        'qualification',
        'special_education_background',
        'training_on_inclusion',
        'skills',
        'other_qualifications',
        'in_service_trainings_attended',
        'sen_certified',
        'is_deployed',
    ];

    protected $casts = [
        'teacher_type' => TeacherType::class,
        'training_on_inclusion' => 'boolean',
        'sen_certified' => 'boolean',
        'is_deployed' => 'boolean',
        'in_service_trainings_attended' => 'integer',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conditions(): BelongsToMany
    {
        return $this->belongsToMany(Condition::class, 'teacher_condition')
            ->using(TeacherCondition::class)
            ->withPivot([
                'assessment_id',
                'is_primary',
                'status',
                'severity_level',
                'severity_source',
                'assigned_at',
                'notes',
            ]);
    }

    /**
     * Direct relation to teacher_condition records to manage pivot fields.
     */
    public function teacherConditions(): HasMany
    {
        return $this->hasMany(TeacherCondition::class)->orderBy('is_primary', 'desc');
    }

    public function learners(): BelongsToMany
    {
        return $this->belongsToMany(Learner::class, 'teacher_learner')
            ->withPivot(['class'])
            ->withTimestamps();
    }

    public function getRegionAttribute(): ?string
    {
        return $this->school?->region_name;
    }
}
