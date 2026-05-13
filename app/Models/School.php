<?php

namespace App\Models;

use App\Enums\SchoolLevel;
use App\Enums\SchoolType;
use App\Models\Concerns\Auditable;
use App\Models\Scopes\SchoolGeographicalScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([SchoolGeographicalScope::class])]
class School extends Model
{
    use Auditable;
    use HasFactory;

    protected $fillable = [
        'emis_code',
        'name',
        'district_id',
        'school_level',
        'school_type',
        'is_inclusive',
        'resource_teacher',
        'number_of_teachers',
        'accessibility',
    ];

    protected $casts = [
        'is_inclusive' => 'boolean',
        'resource_teacher' => 'boolean',
        'accessibility' => 'array',
        'school_level' => SchoolLevel::class,
        'school_type' => SchoolType::class,
    ];

    public function learners(): HasMany
    {
        return $this->hasMany(Learner::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }

    public function supervisions(): HasMany
    {
        return $this->hasMany(SupervisionReport::class);
    }

    public function officers(): BelongsToMany
    {
        return $this->belongsToMany(Officer::class)->withTimestamps();
    }

    public function getDistrictNameAttribute(): ?string
    {
        return $this->district?->name;
    }

    public function getRegionNameAttribute(): ?string
    {
        return $this->district?->region?->name;
    }
}
