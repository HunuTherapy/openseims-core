<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IepGoalEntryScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'iep_goal_entry_id',
        'recorded_at',
        'score',
        'notes',
    ];

    protected $casts = [
        'recorded_at' => 'date',
        'score' => 'integer',
    ];

    public function iepGoalEntry()
    {
        return $this->belongsTo(IepGoalEntry::class);
    }
}
