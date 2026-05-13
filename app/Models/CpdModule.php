<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CpdModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function iePractices(): BelongsToMany
    {
        return $this->belongsToMany(IePractice::class, 'cpd_module_ie_practice');
    }

    public function getIePracticeIncorporatedAttribute(): bool
    {
        return $this->iePractices()->exists();
    }
}
