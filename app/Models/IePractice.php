<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class IePractice extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function cpdModules(): BelongsToMany
    {
        return $this->belongsToMany(CpdModule::class, 'cpd_module_ie_practice');
    }
}
