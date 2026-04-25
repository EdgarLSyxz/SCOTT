<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DTHTransponderRecord extends Model
{
    use HasFactory;

    protected $table = 'dth_transponder_records';

    protected $fillable = [
        'up_link_site',
        'transponders',
        'description',
        'recorded_at',
        'user_id',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
