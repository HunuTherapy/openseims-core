<?php

namespace App\Models;

use Database\Factories\SupervisionDomainScoreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupervisionDomainScore extends Model
{
    /** @use HasFactory<SupervisionDomainScoreFactory> */
    use HasFactory;

    protected $fillable = ['supervision_report_id', 'domain_name', 'score'];

    public function supervision(): BelongsTo
    {
        return $this->belongsTo(SupervisionReport::class, 'supervision_report_id');
    }
}
