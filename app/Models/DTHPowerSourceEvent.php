<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DthPowerSourceEvent extends Model
{
    use HasFactory;

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
