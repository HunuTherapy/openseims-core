<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Scopes\SupervisionReportGeographicalScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ScopedBy([SupervisionReportGeographicalScope::class])]
class SupervisionReport extends Model implements HasMedia
{
    use Auditable, HasFactory, InteractsWithMedia;

    protected $fillable = [
        'school_id', 'supervisor_id', 'supervisor_role', 'school_district', 'school_level', 'school_type',
        'visit_date', 'issues_found', 'deadline_date', 'resolved', 'intervention_provided', 'recipient_id',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'deadline_date' => 'date',
        'resolved' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function domainScores(): HasMany
    {
        return $this->hasMany(SupervisionDomainScore::class, 'supervision_report_id');
    }

    public function observations(): HasMany
    {
        return $this->hasMany(SupervisionObservation::class, 'supervision_report_id');
    }

    public function scopeForRecipient(Builder $query, User $user): Builder
    {
        return $query->where('recipient_id', $user->id);
    }

    // public function responses()
    // {
    //     return $this->hasMany(SupervisionResponse::class);
    // }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public');
    }
}
