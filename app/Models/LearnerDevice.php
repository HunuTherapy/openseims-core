<?php

namespace App\Models;

use App\Enums\NeedStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearnerDevice extends Model
{
    use HasFactory;

    protected $table = 'learner_device';

    protected $fillable = [
        'learner_id',
        'assistive_device_id',
        'status',
        'requested_at',
        'allocated_at',
        'returned_at',
        'notes',
    ];

    protected $casts = [
        'status' => NeedStatus::class,
        'requested_at' => 'date',
        'allocated_at' => 'date',
        'returned_at' => 'date',
    ];

    public function learner()
    {
        return $this->belongsTo(Learner::class);
    }

    public function assistiveDevice()
    {
        return $this->belongsTo(AssistiveDevice::class);
    }
}
