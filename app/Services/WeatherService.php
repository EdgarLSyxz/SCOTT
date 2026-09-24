<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WeatherService
{
    private const CACHE_TTL_SECONDS = 1800;

    private const LOCATIONS = [
        'Zacatecas' => [
            'label' => 'Guadalupe, Zacatecas',
            'latitude' => 22.7475,
            'longitude' => -102.5117,
        ],
        'Toluca' => [
            'label' => 'Toluca, Estado de México',
            'latitude' => 19.2826,
            'longitude' => -99.6557,
        ],
    ];

    private const WEATHER_CODE_MAP = [
        0 => ['label' => 'Despejado', 'icon' => 'fa-sun'],
        1 => ['label' => 'Mayormente despejado', 'icon' => 'fa-sun'],
        2 => ['label' => 'Parcialmente nublado', 'icon' => 'fa-cloud-sun'],
        3 => ['label' => 'Nublado', 'icon' => 'fa-cloud'],
        45 => ['label' => 'Niebla', 'icon' => 'fa-smog'],
        48 => ['label' => 'Niebla escarchada', 'icon' => 'fa-smog'],
        51 => ['label' => 'Llovizna ligera', 'icon' => 'fa-cloud-rain'],
        53 => ['label' => 'Llovizna', 'icon' => 'fa-cloud-rain'],
        55 => ['label' => 'Llovizna densa', 'icon' => 'fa-cloud-rain'],
        56 => ['label' => 'Llovizna helada', 'icon' => 'fa-snowflake'],
        57 => ['label' => 'Llovizna helada fuerte', 'icon' => 'fa-snowflake'],
        61 => ['label' => 'Lluvia ligera', 'icon' => 'fa-cloud-rain'],
        63 => ['label' => 'Lluvia', 'icon' => 'fa-cloud-showers-heavy'],
        65 => ['label' => 'Lluvia fuerte', 'icon' => 'fa-cloud-showers-heavy'],
        66 => ['label' => 'Lluvia helada', 'icon' => 'fa-snowflake'],
        67 => ['label' => 'Lluvia helada fuerte', 'icon' => 'fa-snowflake'],
        71 => ['label' => 'Nieve ligera', 'icon' => 'fa-snowflake'],
        73 => ['label' => 'Nieve', 'icon' => 'fa-snowflake'],
        75 => ['label' => 'Nieve fuerte', 'icon' => 'fa-snowflake'],
        77 => ['label' => 'Granizo', 'icon' => 'fa-snowflake'],
        80 => ['label' => 'Chubascos ligeros', 'icon' => 'fa-cloud-rain'],
        81 => ['label' => 'Chubascos', 'icon' => 'fa-cloud-rain'],
        82 => ['label' => 'Chubascos fuertes', 'icon' => 'fa-cloud-showers-heavy'],
        85 => ['label' => 'Nevadas ligeras', 'icon' => 'fa-snowflake'],
        86 => ['label' => 'Nevadas fuertes', 'icon' => 'fa-snowflake'],
        95 => ['label' => 'Tormenta eléctrica', 'icon' => 'fa-bolt'],
        96 => ['label' => 'Tormenta con granizo', 'icon' => 'fa-bolt'],
        99 => ['label' => 'Tormenta fuerte con granizo', 'icon' => 'fa-bolt'],
    ];

    public function locations(): array
    {
        return self::LOCATIONS;
    }

    public function getForecast(string $site, bool $forceRefresh = false): ?array
    {
        if (! isset(self::LOCATIONS[$site])) {
            return null;
        }

        $cacheKey = 'weather:forecast:'.$site;
        $cacheTtl = self::CACHE_TTL_SECONDS;

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $cacheTtl, function () use ($site) {
            return $this->fetchForecast($site);
        });
    }

    public function getForecastForSites(array $sites, bool $forceRefresh = false): array
    {
        $result = [];
        foreach ($sites as $site) {
            $result[$site] = $this->getForecast($site, $forceRefresh);
        }

        return $result;
    }

    private function fetchForecast(string $site): ?array
    {
        $location = self::LOCATIONS[$site];

        try {
            $response = Http::timeout(8)
                ->retry(2, 250)
                ->withOptions($this->getRequestOptions())
                ->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'current' => 'temperature_2m,weather_code,wind_speed_10m,relative_humidity_2m,precipitation,rain,uv_index,shortwave_radiation',
                    'hourly' => 'temperature_2m,precipitation,precipitation_probability,weather_code,shortwave_radiation,uv_index',
                    'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max,precipitation_sum,rain_sum,showers_sum,precipitation_hours,wind_speed_10m_max,uv_index_max,shortwave_radiation_sum,sunrise,sunset,daylight_duration,sunshine_duration',
                    'timezone' => 'America/Mexico_City',
                    'forecast_days' => 3,
                    'past_hours' => 0,
                    'language' => 'es',
                ]);

            if (! $response->successful()) {
                Log::warning('Open-Meteo request failed', [
                    'site' => $site,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $data = $response->json();
            if (! is_array($data) || empty($data['current']) || empty($data['daily'])) {
                Log::warning('Open-Meteo returned unexpected payload', [
                    'site' => $site,
                    'payload' => $data,
                ]);

                return null;
            }

            return $this->formatForecast($site, $location, $data);
        } catch (Throwable $e) {
            Log::error('Open-Meteo exception', [
                'site' => $site,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function getRequestOptions(): array
    {
        $cacertPath = storage_path('app/cacert.pem');

        if (is_file($cacertPath) && is_readable($cacertPath)) {
            return ['verify' => $cacertPath];
        }

        if ($configured = ini_get('curl.cainfo')) {
            return ['verify' => $configured];
        }

        if ($configured = ini_get('openssl.cafile')) {
            return ['verify' => $configured];
        }

        Log::warning('Open-Meteo: no CA bundle found, falling back to insecure verify');

        return ['verify' => false];
    }

    private function formatForecast(string $site, array $location, array $data): array
    {
        $current = $data['current'];
        $daily = $data['daily'];

        $condition = self::WEATHER_CODE_MAP[(int) ($current['weather_code'] ?? 0)]
            ?? ['label' => 'Sin datos', 'icon' => 'fa-cloud'];

        $hourly = $this->buildHourly($data['hourly'] ?? []);

        $days = [];
        $count = count($daily['time'] ?? []);
        for ($i = 0; $i < $count; $i++) {
            $dayCode = (int) ($daily['weather_code'][$i] ?? 0);
            $dayCondition = self::WEATHER_CODE_MAP[$dayCode] ?? ['label' => 'Sin datos', 'icon' => 'fa-cloud'];

            $probability = $daily['precipitation_probability_max'][$i] ?? null;
            $precipSum = $daily['precipitation_sum'][$i] ?? null;
            $rainSum = $daily['rain_sum'][$i] ?? null;
            $showersSum = $daily['showers_sum'][$i] ?? null;
            $precipHours = $daily['precipitation_hours'][$i] ?? null;
            $windMax = $daily['wind_speed_10m_max'][$i] ?? null;

            $dateKey = substr((string) ($daily['time'][$i] ?? ''), 0, 10);
            $dayHourly = $hourly['by_date'][$dateKey] ?? [];

            $peakHour = $this->findPeakRainHour($dayHourly);
            $rainWindows = $this->buildRainWindows($dayHourly);

            $days[] = [
                'date' => $daily['time'][$i] ?? null,
                'weather_code' => $dayCode,
                'label' => $dayCondition['label'],
                'icon' => $dayCondition['icon'],
                'temp_max' => $daily['temperature_2m_max'][$i] ?? null,
                'temp_min' => $daily['temperature_2m_min'][$i] ?? null,
                'precipitation_probability_max' => $probability,
                'precipitation_sum' => $precipSum,
                'rain_sum' => $rainSum,
                'showers_sum' => $showersSum,
                'precipitation_hours' => $precipHours,
                'wind_speed_10m_max' => $windMax,
                'rain_intensity' => $this->rainIntensity($precipSum),
                'rain_probability_level' => $this->rainProbabilityLevel($probability),
                'peak_hour' => $peakHour,
                'rain_windows' => $rainWindows,
                'sun' => [
                    'sunrise' => $daily['sunrise'][$i] ?? null,
                    'sunset' => $daily['sunset'][$i] ?? null,
                    'daylight_duration_seconds' => $daily['daylight_duration'][$i] ?? null,
                    'sunshine_duration_seconds' => $daily['sunshine_duration'][$i] ?? null,
                    'uv_index_max' => $daily['uv_index_max'][$i] ?? null,
                    'shortwave_radiation_sum' => $daily['shortwave_radiation_sum'][$i] ?? null,
                ],
            ];
        }

        $summary = $this->summarizeRainfall($days);
        $sunSummary = $this->summarizeSun($days, $daily);

        $timeline = $hourly['timeline'] ?? [];
        $timelineStart = $hourly['start_date'] ?? null;
        $timelineEnd = $hourly['end_date'] ?? null;

        return [
            'site' => $site,
            'label' => $location['label'],
            'latitude' => $location['latitude'],
            'longitude' => $location['longitude'],
            'timezone' => $data['timezone'] ?? 'America/Mexico_City',
            'fetched_at' => now()->toIso8601String(),
            'current' => [
                'temperature' => $current['temperature_2m'] ?? null,
                'weather_code' => (int) ($current['weather_code'] ?? 0),
                'label' => $condition['label'],
                'icon' => $condition['icon'],
                'wind_speed' => $current['wind_speed_10m'] ?? null,
                'humidity' => $current['relative_humidity_2m'] ?? null,
                'precipitation' => $current['precipitation'] ?? null,
                'rain' => $current['rain'] ?? null,
                'uv_index' => $current['uv_index'] ?? null,
                'shortwave_radiation' => $current['shortwave_radiation'] ?? null,
            ],
            'daily' => $days,
            'rainfall_summary' => $summary,
            'sun_summary' => $sunSummary,
            'hourly_timeline' => [
                'start_date' => $timelineStart,
                'end_date' => $timelineEnd,
                'hours' => $timeline,
            ],
        ];
    }

    private function buildHourly(array $hourly): array
    {
        $times = $hourly['time'] ?? [];
        $temps = $hourly['temperature_2m'] ?? [];
        $precips = $hourly['precipitation'] ?? [];
        $probs = $hourly['precipitation_probability'] ?? [];
        $codes = $hourly['weather_code'] ?? [];
        $radiations = $hourly['shortwave_radiation'] ?? [];
        $uvs = $hourly['uv_index'] ?? [];

        $timeline = [];
        $byDate = [];
        $maxTimelineHours = 72;

        foreach ($times as $i => $iso) {
            if (count($timeline) >= $maxTimelineHours) {
                break;
            }
            $dateKey = substr((string) $iso, 0, 10);
            $hourKey = substr((string) $iso, 11, 5);
            $precip = isset($precips[$i]) ? (float) $precips[$i] : 0.0;
            $prob = isset($probs[$i]) ? (int) $probs[$i] : null;
            $temp = isset($temps[$i]) ? (float) $temps[$i] : null;
            $code = isset($codes[$i]) ? (int) $codes[$i] : null;
            $radiation = isset($radiations[$i]) ? (float) $radiations[$i] : null;
            $uv = isset($uvs[$i]) ? (float) $uvs[$i] : null;
            $condition = self::WEATHER_CODE_MAP[$code] ?? ['label' => 'Sin datos', 'icon' => 'fa-cloud'];

            $entry = [
                'iso' => $iso,
                'date' => $dateKey,
                'hour' => $hourKey,
                'precipitation' => $precip,
                'probability' => $prob,
                'temperature' => $temp,
                'weather_code' => $code,
                'label' => $condition['label'],
                'icon' => $condition['icon'],
                'rain_intensity' => $this->rainIntensity($precip),
                'shortwave_radiation' => $radiation,
                'uv_index' => $uv,
                'sun_intensity' => $this->sunIntensity($radiation),
            ];

            $timeline[] = $entry;
            $byDate[$dateKey][] = $entry;
        }

        $startDate = $timeline ? substr((string) $timeline[0]['iso'], 0, 10) : null;
        $endDate = $timeline ? substr((string) $timeline[count($timeline) - 1]['iso'], 0, 10) : null;

        return [
            'timeline' => $timeline,
            'by_date' => $byDate,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    private function findPeakRainHour(array $dayHourly): ?array
    {
        if (empty($dayHourly)) {
            return null;
        }

        $peak = null;
        foreach ($dayHourly as $entry) {
            if (($entry['precipitation'] ?? 0) <= 0) {
                continue;
            }
            if ($peak === null || $entry['precipitation'] > $peak['precipitation']) {
                $peak = $entry;
            }
        }

        return $peak;
    }

    private function buildRainWindows(array $dayHourly): array
    {
        if (empty($dayHourly)) {
            return [];
        }

        $windows = [];
        $current = null;

        foreach ($dayHourly as $entry) {
            $hasRain = ($entry['precipitation'] ?? 0) > 0;
            if ($hasRain) {
                if ($current === null) {
                    $current = [
                        'start_hour' => $entry['hour'],
                        'end_hour' => $entry['hour'],
                        'precipitation_sum' => 0.0,
                        'probability_max' => $entry['probability'] ?? 0,
                        'icon' => $entry['icon'],
                    ];
                } else {
                    $current['end_hour'] = $entry['hour'];
                    if (($entry['probability'] ?? 0) > $current['probability_max']) {
                        $current['probability_max'] = $entry['probability'];
                    }
                    if ($entry['icon'] === 'fa-cloud-showers-heavy') {
                        $current['icon'] = 'fa-cloud-showers-heavy';
                    }
                }
                $current['precipitation_sum'] += (float) $entry['precipitation'];
            } else {
                if ($current !== null) {
                    $current['precipitation_sum'] = round($current['precipitation_sum'], 2);
                    $windows[] = $current;
                    $current = null;
                }
            }
        }

        if ($current !== null) {
            $current['precipitation_sum'] = round($current['precipitation_sum'], 2);
            $windows[] = $current;
        }

        return $windows;
    }

    private function rainIntensity(?float $precipSum): string
    {
        if ($precipSum === null) {
            return 'none';
        }
        if ($precipSum <= 0.1) {
            return 'none';
        }
        if ($precipSum < 2.0) {
            return 'light';
        }
        if ($precipSum < 10.0) {
            return 'moderate';
        }

        return 'heavy';
    }

    private function rainProbabilityLevel(?int $probability): string
    {
        if ($probability === null) {
            return 'unknown';
        }
        if ($probability < 20) {
            return 'low';
        }
        if ($probability < 60) {
            return 'moderate';
        }

        return 'high';
    }

    private function summarizeRainfall(array $days): array
    {
        $totalPrecip = 0.0;
        $totalHours = 0.0;
        $hasRain = false;
        $maxProbability = null;
        $rainyDays = 0;

        foreach ($days as $day) {
            $sum = $day['precipitation_sum'] ?? null;
            if ($sum !== null && $sum > 0.1) {
                $hasRain = true;
                $rainyDays++;
                $totalPrecip += (float) $sum;
            }
            $hours = $day['precipitation_hours'] ?? null;
            if ($hours !== null) {
                $totalHours += (float) $hours;
            }
            $prob = $day['precipitation_probability_max'] ?? null;
            if ($prob !== null && ($maxProbability === null || $prob > $maxProbability)) {
                $maxProbability = (int) $prob;
            }
        }

        return [
            'has_rain' => $hasRain,
            'total_precipitation_mm' => round($totalPrecip, 2),
            'total_precipitation_hours' => round($totalHours, 1),
            'rainy_days' => $rainyDays,
            'max_probability' => $maxProbability,
            'max_probability_level' => $this->rainProbabilityLevel($maxProbability),
        ];
    }

    private function sunIntensity(?float $radiation): string
    {
        if ($radiation === null) {
            return 'unknown';
        }
        if ($radiation <= 0) {
            return 'night';
        }
        if ($radiation < 150) {
            return 'low';
        }
        if ($radiation < 500) {
            return 'moderate';
        }

        return 'high';
    }

    public function uvLevel(?float $uv): string
    {
        if ($uv === null) {
            return 'unknown';
        }
        if ($uv < 3) {
            return 'low';
        }
        if ($uv < 6) {
            return 'moderate';
        }
        if ($uv < 8) {
            return 'high';
        }
        if ($uv < 11) {
            return 'very_high';
        }

        return 'extreme';
    }

    public static function uvLevelStatic(?float $uv): string
    {
        if ($uv === null) {
            return 'unknown';
        }
        if ($uv < 3) {
            return 'low';
        }
        if ($uv < 6) {
            return 'moderate';
        }
        if ($uv < 8) {
            return 'high';
        }
        if ($uv < 11) {
            return 'very_high';
        }

        return 'extreme';
    }

    private function summarizeSun(array $days, array $daily): array
    {
        $maxUv = null;
        $totalRadiation = 0.0;
        $totalSunshine = 0.0;
        $hasRadiation = false;
        $hasSunshine = false;
        $firstSunrise = null;
        $lastSunset = null;

        foreach ($days as $idx => $day) {
            $uv = $daily['uv_index_max'][$idx] ?? null;
            if ($uv !== null && ($maxUv === null || $uv > $maxUv)) {
                $maxUv = (float) $uv;
            }

            $rad = $daily['shortwave_radiation_sum'][$idx] ?? null;
            if ($rad !== null) {
                $totalRadiation += (float) $rad;
                $hasRadiation = true;
            }

            $sun = $daily['sunshine_duration'][$idx] ?? null;
            if ($sun !== null) {
                $totalSunshine += (float) $sun;
                $hasSunshine = true;
            }

            $sunrise = $daily['sunrise'][$idx] ?? null;
            $sunset = $daily['sunset'][$idx] ?? null;

            if ($idx === 0) {
                $firstSunrise = $sunrise;
                $lastSunset = $sunset;
            } else {
                if ($sunrise !== null && ($firstSunrise === null || $sunrise < $firstSunrise)) {
                    $firstSunrise = $sunrise;
                }
                if ($sunset !== null && ($lastSunset === null || $sunset > $lastSunset)) {
                    $lastSunset = $sunset;
                }
            }
        }

        return [
            'max_uv_index' => $maxUv,
            'max_uv_level' => $this->uvLevel($maxUv),
            'total_radiation_mj' => $hasRadiation ? round($totalRadiation / 1000, 2) : null,
            'total_sunshine_seconds' => $hasSunshine ? (int) round($totalSunshine) : null,
            'first_sunrise' => $firstSunrise,
            'last_sunset' => $lastSunset,
        ];
    }
}
