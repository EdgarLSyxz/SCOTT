<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class WeatherRainEvent extends Model
{
    protected $table = 'weather_rain_events';

    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SKIPPED = 'skipped';

    protected $fillable = [
        'site',
        'rain_started_at',
        'rain_ended_at',
        'peak_precipitation_mm',
        'total_precipitation_mm',
        'precipitation_hours',
        'max_probability',
        'intensity',
        'condition_label',
        'temperature_c',
        'humidity',
        'wind_kmh',
        'notification_started_status',
        'notification_ended_status',
        'notified_started_at',
        'notified_ended_at',
    ];

    protected $casts = [
        'rain_started_at' => 'datetime',
        'rain_ended_at' => 'datetime',
        'notified_started_at' => 'datetime',
        'notified_ended_at' => 'datetime',
        'peak_precipitation_mm' => 'float',
        'total_precipitation_mm' => 'float',
        'precipitation_hours' => 'float',
        'max_probability' => 'integer',
        'temperature_c' => 'float',
        'humidity' => 'float',
        'wind_kmh' => 'float',
    ];

    public function getDurationMinutes(): ?int
    {
        if (! $this->rain_ended_at) {
            return null;
        }
        return (int) max(0, $this->rain_started_at->diffInMinutes($this->rain_ended_at));
    }

    public function getDurationHuman(): string
    {
        $minutes = $this->getDurationMinutes();
        if ($minutes === null) {
            return '—';
        }
        if ($minutes < 60) {
            return $minutes . ' min';
        }
        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;
        if ($remaining === 0) {
            return $hours . ' h';
        }
        return $hours . ' h ' . $remaining . ' min';
    }

    public static function getOpenEventForSite(string $site): ?self
    {
        return static::where('site', $site)
            ->whereNull('rain_ended_at')
            ->orderByDesc('rain_started_at')
            ->first();
    }
}