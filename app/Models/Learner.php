<?php

namespace App\Models;

use App\Enums\LearnerClass;
use App\Enums\LearnerStatus;
use App\Enums\Sex;
use App\Models\Concerns\Auditable;
use App\Models\Scopes\LearnerGeographicalScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Gate;

#[ScopedBy([LearnerGeographicalScope::class])]
class Learner extends Model
{
    use Auditable;
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'enrol_date' => 'date',
        'sex' => Sex::class,
        'status' => LearnerStatus::class,
        'class' => LearnerClass::class,
        'referred_at' => 'date',
        'specialist_visit_completed' => 'boolean',
    ];

    public function talents(): BelongsToMany
    {
        return $this->belongsToMany(Talent::class, 'learner_talent');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function age(): Attribute
    {
        return Attribute::make(
            get: fn () => Carbon::parse($this->date_of_birth)->age,
        );
    }

    /**
     * If fullname must be access-guarded, use getVisibleName() instead.
     */
    public function getFullNameAttribute(): string
    {
        return collect([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])->filter()->join(' ');
    }

    public function getVisibleName(?User $viewer = null, ?string $fallback = null): string
    {
        $viewer ??= auth()->user();
        $fallback ??= sprintf('Learner #%d', $this->getKey());

        if ($viewer && Gate::forUser($viewer)->allows('viewNames', $this) && filled($this->full_name)) {
            return $this->full_name;
        }

        return $fallback;
    }

    public function iepGoals(): HasMany
    {
        return $this->hasMany(IepGoal::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function conditions(): BelongsToMany
    {
        return $this->belongsToMany(Condition::class, 'learner_condition')
            ->using(LearnerCondition::class)
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

    public function triggerFactors(): BelongsToMany
    {
        return $this->belongsToMany(TriggerFactor::class, 'learner_trigger_factor')
            ->using(LearnerTriggerFactor::class)
            ->withPivot(['notes']);
    }

    public function learnerAccommodations(): HasMany
    {
        return $this->hasMany(LearnerAccommodation::class);
    }

    /**
     * Direct relation to learner_condition records to manage pivot fields.
     */
    public function learnerConditions(): HasMany
    {
        return $this->hasMany(LearnerCondition::class)->orderBy('is_primary', 'desc');
    }

    public function learnerAssessmentHistory(): HasMany
    {
        return $this->hasMany(LearnerAssessmentHistory::class);
    }

    /**
     * Retrieve the learner's primary condition based on the pivot flag.
     */
    public function getPrimaryConditionAttribute(): ?Condition
    {
        return $this->conditions()->wherePivot('is_primary', true)->first();
    }

    public function deviceTypes(): BelongsToMany
    {
        return $this->belongsToMany(DeviceType::class, 'device_learner')
            ->withPivot([
                'requested_at',
                'fulfilled_at',
                'returned_at',
                'serial_number',
            ])
            ->withTimestamps();
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'teacher_learner')
            ->withPivot(['class'])
            ->withTimestamps();
    }
}
