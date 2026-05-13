<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use Auditable;
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'learner_id',
        'class',
        'date',
        'present',
        'reason',
    ];

    protected $casts = [
        'date' => 'date',
        'present' => 'boolean',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function learner()
    {
        return $this->belongsTo(Learner::class);
    }
}
