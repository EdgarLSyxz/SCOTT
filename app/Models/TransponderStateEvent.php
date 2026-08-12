<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransponderStateEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'transponder_id',
        'from_site',
        'to_site',
        'from_state_on',
        'to_state_on',
        'previous_duration_seconds',
        'commands_log',
        'user_id',
        'created_at',
    ];

    protected $casts = [
        'from_state_on' => 'boolean',
        'to_state_on' => 'boolean',
        'previous_duration_seconds' => 'integer',
        'created_at' => 'datetime',
    ];

    public function transponder(): BelongsTo
    {
        return $this->belongsTo(Transponder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
