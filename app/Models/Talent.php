<?php

namespace App\Models;

use Database\Factories\TalentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Talent extends Model
{
    /** @use HasFactory<TalentFactory> */
    use HasFactory;

    protected $table = 'talents';

    protected $fillable = [
        'name',
        'description',
    ];

    public function learners()
    {
        return $this->belongsToMany(Learner::class, 'learner_talent');
    }
}
