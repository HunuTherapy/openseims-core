<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use Auditable;
    use HasFactory;

    protected $fillable = [
        'learner_id',
        'form_id',
        'assessor_id',
        'assessment_date',
        'raw_scores',
        'overall_result',
        'next_step',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'raw_scores' => 'array',
    ];

    public function learner()
    {
        return $this->belongsTo(Learner::class);
    }

    public function form()
    {
        return $this->belongsTo(AssessmentForm::class, 'form_id');
    }

    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }
}
