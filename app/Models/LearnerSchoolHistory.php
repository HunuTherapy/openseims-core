<?php

namespace App\Models;

use App\Enums\PlacementType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearnerSchoolHistory extends Model
{
    use HasFactory;

    protected $table = 'learner_school_history';

    protected $fillable = [
        'learner_id',
        'school_id',
        'placement_type',
        'start_date',
        'end_date',
        'recorded_by_user_id',
    ];

    protected $casts = [
        'placement_type' => PlacementType::class,
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function learner()
    {
        return $this->belongsTo(Learner::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
