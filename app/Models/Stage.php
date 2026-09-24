<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
        'area',
    ];

    public const AREA_OTT = 'OTT';

    public const AREA_DTH = 'DTH';

    public static function getAreas(): array
    {
        return [self::AREA_OTT, self::AREA_DTH];
    }
}
