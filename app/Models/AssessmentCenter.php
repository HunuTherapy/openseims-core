<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class AssessmentCenter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'email',
    ];

    public function assessmentHistory(): MorphMany
    {
        return $this->morphMany(LearnerAssessmentHistory::class, 'centerable');
    }
}
