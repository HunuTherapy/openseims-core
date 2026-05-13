<?php

namespace App\Models;

use Database\Factories\OfficerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Officer extends Model
{
    /** @use HasFactory<OfficerFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'role',
        'formal_training',
        'phone',
        'is_deployed',
        'user_id',
    ];

    protected $casts = [
        'is_deployed' => 'boolean',
        'formal_training' => 'boolean',
    ];

    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class)->withTimestamps();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getRegionAttribute(): ?string
    {
        return $this->user?->region_name;
    }

    public function getDistrictAttribute(): ?string
    {
        return $this->user?->district_name;
    }

    public function getEmailAttribute(): ?string
    {
        return $this->user?->email;
    }
}
