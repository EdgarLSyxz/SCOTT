<?php

namespace App\Console\Commands;

use App\Models\Transponder;
use App\Services\RainEventDetector;
use App\Services\WeatherService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class WeatherRainCheckCommand extends Command
{
    protected $signature = 'weather:check-rain';

    protected $description = 'Checks current rain conditions for the uplink sites and sends rain started/ended notification emails.';

    public function handle(WeatherService $weather, RainEventDetector $detector): int
    {
        if (! Schema::hasTable('weather_rain_events')) {
            $this->warn('weather_rain_events table is missing; skipping rain notifications.');
            return self::SUCCESS;
        }

        $sites = [Transponder::SITE_ZACATECAS, Transponder::SITE_TOLUCA];
        $payloadBySite = $weather->getForecastForSites($sites, true);

        $results = $detector->processAllSites($payloadBySite);

        foreach ($results as $site => $result) {
            $startedId = $result['started'] ? '#' . $result['started']->id : '-';
            $endedId = $result['ended'] ? '#' . $result['ended']->id : '-';

            $this->line(sprintf(
                '[%s] %s | started=%s ended=%s',
                now()->format('Y-m-d H:i:s'),
                $site,
                $startedId,
                $endedId
            ));
        }

        return self::SUCCESS;
    }
}
