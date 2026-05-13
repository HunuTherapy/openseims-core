<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class IepTeamMember extends Pivot
{
    use HasFactory;

    protected $table = 'iep_team_members';

    public $incrementing = true;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function iepGoal(): BelongsTo
    {
        return $this->belongsTo(IepGoal::class);
    }
}
