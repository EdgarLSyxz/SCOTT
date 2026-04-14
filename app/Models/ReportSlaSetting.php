<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportSlaSetting extends Model
{
    protected $fillable = [
        'area',
        'is_active',
        'level_1_minutes',
        'level_2_minutes',
        'level_3_minutes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
