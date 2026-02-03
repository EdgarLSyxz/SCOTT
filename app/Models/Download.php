<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Download extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'year',
        'month',
        'day',
        'count',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'day' => 'integer',
        'count' => 'integer',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public static function addToMonth(int $deviceId, int $year, int $month, int $day = 1, int $count = 0)
    {
        $download = static::firstOrCreate(
            ['device_id' => $deviceId, 'year' => $year, 'month' => $month, 'day' => $day],
            ['count' => 0]
        );

        if ($count !== 0) {
            $download->increment('count', $count);
            $download->refresh();
        }

        return $download;
    }
}
