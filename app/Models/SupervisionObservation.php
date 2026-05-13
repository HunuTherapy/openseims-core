<?php

namespace App\Models;

use Database\Factories\SupervisionObservationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupervisionObservation extends Model
{
    /** @use HasFactory<SupervisionObservationFactory> */
    use HasFactory;

    protected $fillable = [
        'supervision_report_id', 'issues_found', 'intervention_provided', 'deadline_date', 'resolved',
    ];

    protected $casts = [
        'resolved' => 'boolean',
    ];

    public function supervision(): BelongsTo
    {
        return $this->belongsTo(SupervisionReport::class, 'supervision_report_id');
    }
}
