<?php

namespace App\Models;

use App\Enums\EvaluationDecision;
use App\Enums\GoalType;
use App\Enums\ParentalConsent;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class IepGoal extends Model implements HasMedia
{
    use Auditable;
    use HasFactory, InteractsWithMedia;

    protected $table = 'iep_goals';

    protected $fillable = [
        'learner_id',
        'frequency_of_engagement',
        'start_date',
        'end_date',
        'parental_consent',
        'program_placement',
        'program_placement_other',
        'related_services',
        'related_services_other',
        'status',
        'evaluation_decision',
        'goal_type',
        'recommendation_details',
    ];

    protected $attributes = [
        'status' => 'on_track',
    ];

    protected $casts = [
        'last_review_at' => 'date',
        'related_services' => 'array',
        'iep_team' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'parental_consent' => ParentalConsent::class,
        'evaluation_decision' => EvaluationDecision::class,
        'goal_type' => GoalType::class,
    ];

    public function learner()
    {
        return $this->belongsTo(Learner::class);
    }

    public function goalEntries()
    {
        return $this->hasMany(IepGoalEntry::class);
    }

    public function teamMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'iep_team_members', 'user_id')
            ->withPivot(['role'])
            ->using(IepTeamMember::class)
            ->withTimestamps();
    }

    public function iepTeamMembers(): HasMany
    {
        return $this->hasMany(IepTeamMember::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('iep_documents')
            ->acceptsFile(function ($file) {
                return $file->mimeType === 'application/pdf';
            })
            ->useDisk('public');

        $this->addMediaCollection('parental_consent_evidence')
            ->acceptsFile(function ($file) {
                return in_array($file->mimeType, [
                    'application/pdf',
                    'image/jpeg',
                    'image/png',
                ], true);
            })
            ->useDisk('public');
    }
}
