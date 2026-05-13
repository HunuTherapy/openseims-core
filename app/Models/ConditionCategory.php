<?php

namespace App\Models;

use Database\Factories\ConditionCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConditionCategory extends Model
{
    /** @use HasFactory<ConditionCategoryFactory> */
    use HasFactory;

    protected $table = 'condition_categories';

    protected $fillable = [
        'code',
        'name',
    ];

    public function conditions(): HasMany
    {
        return $this->hasMany(Condition::class, 'category_id');
    }
}
