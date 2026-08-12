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

        $cacheKey = 'weather:forecast:' . $site;
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
                    'current' => 'temperature_2m,weather_code,wind_speed_10m,relative_humidity_2m',
                    'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max',
                    'timezone' => 'America/Mexico_City',
                    'forecast_days' => 3,
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

        $days = [];
        $count = count($daily['time'] ?? []);
        for ($i = 0; $i < $count; $i++) {
            $dayCode = (int) ($daily['weather_code'][$i] ?? 0);
            $dayCondition = self::WEATHER_CODE_MAP[$dayCode] ?? ['label' => 'Sin datos', 'icon' => 'fa-cloud'];

            $days[] = [
                'date' => $daily['time'][$i] ?? null,
                'weather_code' => $dayCode,
                'label' => $dayCondition['label'],
                'icon' => $dayCondition['icon'],
                'temp_max' => $daily['temperature_2m_max'][$i] ?? null,
                'temp_min' => $daily['temperature_2m_min'][$i] ?? null,
                'precipitation_probability_max' => $daily['precipitation_probability_max'][$i] ?? null,
            ];
        }

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
            ],
            'daily' => $days,
        ];
    }
}
