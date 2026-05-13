<?php

namespace App\Models;

use App\Enums\DeviceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssistiveDevice extends Model
{
    use HasFactory;

    protected $table = 'device_types';

    protected $fillable = [
        'device_type_id',
        'serial_no',
        'spec',
        'service_date',
        'school_id',
        'status',
    ];

    protected $casts = [
        'service_date' => 'date',
        'status' => DeviceStatus::class,
    ];

    public function deviceType()
    {
        return $this->belongsTo(DeviceType::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
