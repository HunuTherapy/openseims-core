<?php

namespace App\Models;

use App\Enums\AccommodationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearnerAccommodation extends Model
{
    use HasFactory;

    protected $table = 'learner_accommodation';

    protected $fillable = [
        'learner_id',
        'accommodation_type_id',
        'status',
        'start_date',
        'end_date',
        'assessment_id',
        'notes',
    ];

    protected $casts = [
        'status' => AccommodationStatus::class,
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function learner()
    {
        return $this->belongsTo(Learner::class);
    }

    public function accommodationType()
    {
        return $this->belongsTo(AccommodationType::class);
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }
}
