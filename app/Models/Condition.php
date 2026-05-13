<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Condition extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    public function learners()
    {
        return $this->belongsToMany(Learner::class, 'learner_condition');
    }

    public function category()
    {
        return $this->belongsTo(ConditionCategory::class, 'category_id');
    }
}
