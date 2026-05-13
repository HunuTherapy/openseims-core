<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\GeographyLevel;
use App\Models\Concerns\Auditable;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use Auditable;
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use Notifiable;

    protected array $auditExcept = ['email_verified_at'];

    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->roles()->exists()) {
            return false;
        }

        if ($panel->getId() === 'admin') {
            return $this->hasRole('national_admin');
        }

        if ($panel->getId() === 'seims') {
            return true;
        }

        return false;
    }

    // Relations
    public function supervisions(): HasMany
    {
        return $this->hasMany(SupervisionReport::class, 'supervisor_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'assessor_id');
    }

    public function officer(): HasOne
    {
        return $this->hasOne(Officer::class);
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function directReports(): HasMany
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    public function iepGoals(): BelongsToMany
    {
        return $this->belongsToMany(IepGoal::class, 'iep_team_members', 'iep_goal_id')
            ->using(IepTeamMember::class)
            ->withTimestamps();
    }

    public function isHqAdmin(): bool
    {
        return $this->hasRole('national_admin');
    }

    public function hasFullDataAccess(): bool
    {
        return $this->hasRole('national_admin')
            || $this->hasRole('national_sped_officer');
    }

    /**
     * @return array{regionId: ?int, districtId: ?int, schoolId: ?int}
     */
    public function geographyScope(): array
    {
        $schoolId = $this->school_id
            ?? $this->teacher?->school_id;

        $school = $schoolId
            ? School::withoutGlobalScopes()->select(['id', 'district_id'])->find($schoolId)
            : null;

        $districtId = $this->district_id
            ?? $school?->district_id;

        $district = $districtId
            ? District::query()->select(['id', 'region_id'])->find($districtId)
            : null;

        $regionId = $this->region_id
            ?? $district?->region_id;

        return [
            'regionId' => $regionId,
            'districtId' => $districtId,
            'schoolId' => $schoolId,
        ];
    }

    public function geographyLevel(): GeographyLevel
    {
        if ($this->hasFullDataAccess()) {
            return GeographyLevel::NATIONAL;
        }

        $scope = $this->geographyScope();

        if ($scope['schoolId']) {
            return GeographyLevel::SCHOOL;
        }

        if ($scope['districtId']) {
            return GeographyLevel::DISTRICT;
        }

        if ($scope['regionId']) {
            return GeographyLevel::REGION;
        }

        return GeographyLevel::NONE;
    }

    public function hasNationalAccess(): bool
    {
        return $this->geographyLevel() === GeographyLevel::NATIONAL;
    }

    public function isRegionScoped(): bool
    {
        return $this->geographyLevel() === GeographyLevel::REGION;
    }

    public function isDistrictScoped(): bool
    {
        return $this->geographyLevel() === GeographyLevel::DISTRICT;
    }

    public function isSchoolScoped(): bool
    {
        return $this->geographyLevel() === GeographyLevel::SCHOOL;
    }

    public function canViewOfficerContactOf(?User $other): bool
    {
        if (! $other) {
            return false;
        }

        if ($this->hasRole('national_admin')) {
            return true;
        }

        $viewerScope = $this->geographyScope();
        $otherScope = $other->geographyScope();

        return match ($this->geographyLevel()) {
            GeographyLevel::REGION => $viewerScope['regionId'] !== null
                && $viewerScope['regionId'] === $otherScope['regionId']
                && $this->geographyRank($other->geographyLevel()) > $this->geographyRank(GeographyLevel::REGION),
            GeographyLevel::DISTRICT => $viewerScope['districtId'] !== null
                && $viewerScope['districtId'] === $otherScope['districtId']
                && $this->geographyRank($other->geographyLevel()) > $this->geographyRank(GeographyLevel::DISTRICT),
            GeographyLevel::SCHOOL => $viewerScope['schoolId'] !== null
                && $viewerScope['schoolId'] === $otherScope['schoolId']
                && $this->geographyRank($other->geographyLevel()) > $this->geographyRank(GeographyLevel::SCHOOL),
            default => false,
        };
    }

    private function geographyRank(GeographyLevel $level): int
    {
        return match ($level) {
            GeographyLevel::NATIONAL => 0,
            GeographyLevel::REGION => 1,
            GeographyLevel::DISTRICT => 2,
            GeographyLevel::SCHOOL => 3,
            GeographyLevel::NONE => 4,
        };
    }

    public function getDistrictNameAttribute(): ?string
    {
        return $this->district?->name;
    }

    public function getRegionNameAttribute(): ?string
    {
        return $this->region?->name
            ?? $this->district?->region?->name;
    }
}
