<?php

namespace App\Models;

use App\Enums\DiagnosisStatus;
use App\Enums\DisabilityOnset;
use App\Enums\SeverityLevel;
use App\Enums\SeveritySource;
use Database\Factories\LearnerConditionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class LearnerCondition extends Pivot implements HasMedia
{
    /** @use HasFactory<LearnerConditionFactory> */
    use HasFactory, InteractsWithMedia;

    protected $table = 'learner_condition';

    public $incrementing = true;

    public $timestamps = true;

    protected $keyType = 'int';

    protected $fillable = [
        'learner_id',
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

    public function learner()
    {
        return $this->belongsTo(Learner::class);
    }

    public function condition()
    {
        return $this->belongsTo(Condition::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('diagnosis_documents')
            ->acceptsFile(function ($file) {
                return $file->mimeType === 'application/pdf';
            });
        // ->useDisk('public');
    }
}
