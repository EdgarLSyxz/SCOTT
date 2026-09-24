<?php

namespace App\Services;

use App\Mail\Weather\RainEndedMail;
use App\Mail\Weather\RainStartedMail;
use App\Models\WeatherRainEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class RainEventDetector
{
    public function __construct(private readonly WeatherService $weather) {}

    public function processSite(string $site, array $payload): array
    {
        $result = [
            'site' => $site,
            'started' => null,
            'ended' => null,
            'errors' => [],
        ];

        if (empty($payload)) {
            return $result;
        }

        if (! $this->isEnabled()) {
            return $result;
        }

        $threshold = $this->getThreshold();
        $currentRain = (float) ($payload['current']['precipitation'] ?? 0);
        $currentRain = max($currentRain, (float) ($payload['current']['rain'] ?? 0));

        $isRaining = $currentRain > $threshold;
        $openEvent = WeatherRainEvent::getOpenEventForSite($site);

        if ($isRaining && ! $openEvent) {
            $event = $this->createStartedEvent($site, $payload, $currentRain);
            if ($event) {
                $result['started'] = $event;
                $this->dispatchStartedNotification($event);
            }
        } elseif (! $isRaining && $openEvent) {
            $closed = $this->closeEvent($openEvent, $payload);
            if ($closed) {
                $result['ended'] = $closed;
                $this->dispatchEndedNotification($closed);
            }
        }

        return $result;
    }

    public function processAllSites(array $payloadBySite): array
    {
        $results = [];
        foreach ($payloadBySite as $site => $payload) {
            $results[$site] = $this->processSite($site, $payload ?? []);
        }

        return $results;
    }

    private function isEnabled(): bool
    {
        return (bool) config('weather.rain_notification.enabled', true);
    }

    private function getThreshold(): float
    {
        return (float) config('weather.rain_notification.threshold_mm', 0.1);
    }

    private function getCooldownMinutes(): int
    {
        return (int) config('weather.rain_notification.cooldown_minutes', 15);
    }

    private function createStartedEvent(string $site, array $payload, float $currentRain): ?WeatherRainEvent
    {
        try {
            $timeline = $payload['hourly_timeline']['hours'] ?? [];
            $peak = null;
            $maxProb = null;

            foreach ($timeline as $entry) {
                if (($entry['precipitation'] ?? 0) > 0 && ($peak === null || $entry['precipitation'] > $peak['precipitation'])) {
                    $peak = $entry;
                }
                $prob = $entry['probability'] ?? null;
                if ($prob !== null && ($maxProb === null || $prob > $maxProb)) {
                    $maxProb = (int) $prob;
                }
            }

            $today = $payload['daily'][0] ?? [];
            $windows = $today['rain_windows'] ?? [];
            $estimatedHours = null;
            foreach ($windows as $win) {
                $estimatedHours = ($estimatedHours ?? 0) + 1;
            }

            $event = WeatherRainEvent::create([
                'site' => $site,
                'rain_started_at' => now(),
                'peak_precipitation_mm' => $peak['precipitation'] ?? $currentRain,
                'total_precipitation_mm' => $today['precipitation_sum'] ?? $currentRain,
                'precipitation_hours' => $estimatedHours,
                'max_probability' => $maxProb,
                'intensity' => $today['rain_intensity'] ?? null,
                'condition_label' => $payload['current']['label'] ?? null,
                'temperature_c' => $payload['current']['temperature'] ?? null,
                'humidity' => $payload['current']['humidity'] ?? null,
                'wind_kmh' => $payload['current']['wind_speed'] ?? null,
                'notification_started_status' => WeatherRainEvent::STATUS_PENDING,
            ]);

            return $event;
        } catch (Throwable $e) {
            Log::error('RainEventDetector: failed to create start event', [
                'site' => $site,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function closeEvent(WeatherRainEvent $event, array $payload): ?WeatherRainEvent
    {
        try {
            $event->rain_ended_at = now();
            $event->save();

            return $event;
        } catch (Throwable $e) {
            Log::error('RainEventDetector: failed to close event', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function dispatchStartedNotification(WeatherRainEvent $event): void
    {
        $recipients = $this->getRecipients();
        if (empty($recipients)) {
            $event->update([
                'notification_started_status' => WeatherRainEvent::STATUS_SKIPPED,
            ]);

            return;
        }

        if ($this->isInCooldown($event->site, 'started')) {
            $event->update([
                'notification_started_status' => WeatherRainEvent::STATUS_SKIPPED,
            ]);

            return;
        }

        try {
            $mail = new RainStartedMail($event, $this->getForecastSnapshot($event->site));
            Mail::to($recipients)->send($mail);

            $event->update([
                'notification_started_status' => WeatherRainEvent::STATUS_SENT,
                'notified_started_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('RainEventDetector: failed to send start notification', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
            $event->update([
                'notification_started_status' => WeatherRainEvent::STATUS_FAILED,
            ]);
        }
    }

    private function dispatchEndedNotification(WeatherRainEvent $event): void
    {
        $recipients = $this->getRecipients();
        if (empty($recipients)) {
            $event->update([
                'notification_ended_status' => WeatherRainEvent::STATUS_SKIPPED,
            ]);

            return;
        }

        if ($this->isInCooldown($event->site, 'ended')) {
            $event->update([
                'notification_ended_status' => WeatherRainEvent::STATUS_SKIPPED,
            ]);

            return;
        }

        try {
            $mail = new RainEndedMail($event, $this->getForecastSnapshot($event->site));
            Mail::to($recipients)->send($mail);

            $event->update([
                'notification_ended_status' => WeatherRainEvent::STATUS_SENT,
                'notified_ended_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('RainEventDetector: failed to send end notification', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
            $event->update([
                'notification_ended_status' => WeatherRainEvent::STATUS_FAILED,
            ]);
        }
    }

    private function isInCooldown(string $site, string $type): bool
    {
        $column = $type === 'started' ? 'notified_started_at' : 'notified_ended_at';
        $cutoff = Carbon::now()->subMinutes($this->getCooldownMinutes());

        return WeatherRainEvent::where('site', $site)
            ->whereNotNull($column)
            ->where($column, '>=', $cutoff)
            ->exists();
    }

    private function getRecipients(): array
    {
        $configured = config('weather.rain_notification.recipients', []);
        if (is_string($configured)) {
            $configured = array_filter(array_map('trim', explode(',', $configured)));
        }

        return $configured;
    }

    private function getForecastSnapshot(string $site): ?array
    {
        try {
            return $this->weather->getForecast($site, false);
        } catch (Throwable $e) {
            Log::warning('RainEventDetector: unable to fetch forecast snapshot', [
                'site' => $site,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
