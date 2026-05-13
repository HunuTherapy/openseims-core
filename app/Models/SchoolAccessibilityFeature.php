<?php

namespace App\Models;

use Database\Factories\SchoolAccessibilityFeatureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolAccessibilityFeature extends Model
{
    /** @use HasFactory<SchoolAccessibilityFeatureFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
    ];
}
