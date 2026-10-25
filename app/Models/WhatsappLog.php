<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WhatsappLog extends Model
{
    protected $fillable = [
        'event_key',
        'recipient_type',
        'to_number',
        'message',
        'status',
        'response',
        'related_id',
        'related_type',
    ];

    public function related(): MorphTo
    {
        return $this->morphTo();
    }
}
