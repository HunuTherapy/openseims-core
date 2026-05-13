<?php

namespace App\Models;

use Database\Factories\AssessmentFormFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssessmentForm extends Model
{
    /** @use HasFactory<AssessmentFormFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'form_json' => 'array',
        'active' => 'boolean',
    ];

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'form_id');
    }
}
