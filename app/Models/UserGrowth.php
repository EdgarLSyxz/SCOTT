<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGrowth extends Model
{
    protected $table = 'user_growth';

    protected $fillable = [
        'recorded_at',
        'customers',
        'devices',
    ];

    protected $casts = [
        'recorded_at' => 'date',
        'customers'   => 'integer',
        'devices'     => 'integer',
    ];
}
