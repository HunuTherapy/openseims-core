<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearnerAssessmentHistory extends Model
{
    use HasFactory;

    protected $table = 'learner_assessment_history';

    protected $fillable = [
        'learner_id',
        'centerable_id',
        'centerable_type',
        'event_type',
        'referred_to_center_id',
        'event_date',
        'notes',
    ];

    public function learner(): BelongsTo
    {
        return $this->belongsTo(Learner::class);
    }

    public function centerable()
    {
        return $this->morphTo();
    }

    public function referredToCenter(): BelongsTo
    {
        return $this->belongsTo(AssessmentCenter::class, 'referred_to_center_id');
    }
}
