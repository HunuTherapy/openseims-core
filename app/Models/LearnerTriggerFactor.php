<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearnerTriggerFactor extends Model
{
    use HasFactory;

    protected $table = 'learner_trigger_factor';

    protected $fillable = [
        'learner_id',
        'trigger_factor_id',
        'notes',
    ];

    public function learner()
    {
        return $this->belongsTo(Learner::class);
    }

    public function triggerFactor()
    {
        return $this->belongsTo(TriggerFactor::class);
    }
}
