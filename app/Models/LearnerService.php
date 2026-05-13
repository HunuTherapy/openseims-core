<?php

namespace App\Models;

use App\Enums\FrequencyType;
use App\Enums\FrequencyUnit;
use App\Enums\ServiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearnerService extends Model
{
    use HasFactory;

    protected $table = 'learner_service';

    protected $fillable = [
        'learner_id',
        'service_type_id',
        'frequency_value',
        'frequency_unit',
        'frequency_type',
        'status',
        'requested_at',
        'start_date',
        'end_date',
        'notes',
    ];

    protected $casts = [
        'frequency_value' => 'integer',
        'frequency_unit' => FrequencyUnit::class,
        'frequency_type' => FrequencyType::class,
        'status' => ServiceStatus::class,
        'requested_at' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function learner()
    {
        return $this->belongsTo(Learner::class);
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }
}
