<?php

namespace App\Models;

use Database\Factories\WebhookLogsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    /** @use HasFactory<WebhookLogsFactory> */
    use HasFactory;

    protected $primaryKey = 'event_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['event_id', 'provider', 'event_type', 'payload'];

    protected $casts = [
        'payload' => 'array',
    ];
}
