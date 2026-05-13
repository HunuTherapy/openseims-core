<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DeviceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    // we're getting rid of this
    public function devices()
    {
        return $this->hasMany(AssistiveDevice::class);
    }

    public function iepGoals()
    {
        return $this->belongsToMany(IepGoal::class)->withTimestamps();
    }

    public function learners(): BelongsToMany
    {
        return $this->belongsToMany(Learner::class, 'device_learner')
            ->withPivot([
                'requested_at',
                'fulfilled_at',
                'returned_at',
                'serial_number',
            ])
            ->withTimestamps();
    }

    public function serviceTypes(): BelongsToMany
    {
        return $this->belongsToMany(ServiceType::class, 'device_type_service_type');
    }
}
