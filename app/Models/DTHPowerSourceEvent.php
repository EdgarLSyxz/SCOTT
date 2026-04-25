<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DTHPowerSourceEvent extends Model
{
    use HasFactory;

    protected $table = 'dth_power_source_events';

    protected $fillable = [
        'power_source',
        'notes',
        'changed_at',
        'user_id',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
