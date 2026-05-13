<?php

namespace App\Models;

use App\Enums\GoalCompletionStatus;
use App\Enums\InstructionArea;
use Database\Factories\IepGoalEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IepGoalEntry extends Model
{
    /** @use HasFactory<IepGoalEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'iep_goal_id',
        'instruction_area',
        'baseline',
        'baseline_description',
        'target',
        'target_description',
        'last_review_at',
        'completion_status',
        'recommend_goal_change',
    ];

    protected $casts = [
        'baseline' => 'integer',
        'target' => 'integer',
        'last_review_at' => 'date',
        'instruction_area' => InstructionArea::class,
        'completion_status' => GoalCompletionStatus::class,
        'recommend_goal_change' => 'boolean',
    ];

    public function iepGoal()
    {
        return $this->belongsTo(IepGoal::class);
    }

    public function actualScores()
    {
        return $this->hasMany(IepGoalEntryScore::class);
    }
}
