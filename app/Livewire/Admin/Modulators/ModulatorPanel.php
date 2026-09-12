<?php

namespace App\Livewire\Admin\Modulators;

use App\Models\Transponder;
use App\Models\TransponderStateEvent;
use App\Models\User;
use App\Services\RainEventDetector;
use App\Services\WeatherService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\On;
use Livewire\Component;

class ModulatorPanel extends Component
{
    public array $weatherBySite = [];

    public ?string $weatherLastUpdatedAt = null;

    public function mount(): void
    {
        $this->authorizeAccess();
    }

    public function title(): string
    {
        return __('modulators.modulators_panel');
    }

    public function confirmSwitch(?int $transponderId, ?string $password = null): array
    {
        $this->authorizeAccess();

        if ($transponderId === null) {
            return [
                'ok' => false,
                'error' => __('modulators.transponder_not_found'),
                'title' => __('modulators.transponder_not_found'),
            ];
        }

        if (! $this->verifySwitchPassword($password)) {
            return [
                'ok' => false,
                'error' => __('modulators.password_invalid_text'),
                'title' => __('modulators.password_invalid'),
            ];
        }

        if (! Schema::hasTable('transponders')) {
            return [
                'ok' => false,
                'error' => __('modulators.module_not_ready_text'),
                'title' => __('modulators.module_not_ready'),
            ];
        }

        $transponder = Transponder::find($transponderId);
        if (! $transponder) {
            return [
                'ok' => false,
                'error' => __('modulators.transponder_not_found'),
                'title' => __('modulators.transponder_not_found'),
            ];
        }

        $previousDuration = $this->secondsSinceLastEvent($transponder);
        $fromSite = $transponder->active_site;
        $toSite = $transponder->oppositeSite();
        $transponderCode = $transponder->code;

        $commandsOutput = $this->runSwitchCommand($transponderCode, $fromSite, $toSite);

        DB::transaction(function () use ($transponder, $previousDuration, $commandsOutput) {
            $fromSite = $transponder->active_site;
            $toSite = $transponder->oppositeSite();

            $transponder->update([
                'active_site' => $toSite,
                'is_on' => true,
            ]);

            TransponderStateEvent::create([
                'transponder_id' => $transponder->id,
                'from_site' => $fromSite,
                'to_site' => $toSite,
                'from_state_on' => $transponder->getOriginal('is_on') ?? true,
                'to_state_on' => true,
                'previous_duration_seconds' => $previousDuration,
                'commands_log' => $commandsOutput,
                'user_id' => Auth::id(),
                'created_at' => now(),
            ]);
        });

        return [
            'ok' => true,
            'code' => $transponderCode,
            'from' => $fromSite,
            'to' => $toSite,
            'title' => __('modulators.switch_completed'),
            'message' => __('modulators.transponder_switched_text'),
            'commands' => $commandsOutput,
        ];
    }

    private function verifySwitchPassword(?string $password): bool
    {
        $expected = (string) config('modulators.switch_password', '');
        $provided = is_string($password) ? $password : '';

        if ($expected === '') {
            return false;
        }

        return hash_equals($expected, $provided);
    }

    #[On('modulator-refresh')]
    public function refreshData(): void
    {

    }

    public function refreshWeather(bool $force = false): void
    {
        $service = app(WeatherService::class);

        $sites = [Transponder::SITE_ZACATECAS, Transponder::SITE_TOLUCA];
        $fresh = $force
            ? $service->getForecastForSites($sites, true)
            : $service->getForecastForSites($sites, false);

        $this->weatherBySite = $fresh;

        $mostRecent = null;
        foreach ($fresh as $payload) {
            if (! is_array($payload) || empty($payload['fetched_at'])) {
                continue;
            }
            $ts = \Carbon\Carbon::parse($payload['fetched_at']);
            if ($mostRecent === null || $ts->greaterThan($mostRecent)) {
                $mostRecent = $ts;
            }
        }

        $this->weatherLastUpdatedAt = $mostRecent?->toIso8601String();

        $this->processRainNotifications($fresh);
    }

    private function processRainNotifications(array $payloadBySite): void
    {
        if (! Schema::hasTable('weather_rain_events')) {
            return;
        }

        try {
            $detector = app(RainEventDetector::class);
            $detector->processAllSites($payloadBySite);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Rain notifications skipped', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        $this->authorizeAccess();

        $hasTable = Schema::hasTable('transponders');
        $hasEventsTable = Schema::hasTable('transponder_state_events');

        if (empty($this->weatherBySite)) {
            $this->refreshWeather(false);
        }

        $transponders = $hasTable
            ? Transponder::orderBy('sort_order')->orderBy('code')->get()
            : collect();

        $recentEvents = ($hasTable && $hasEventsTable)
            ? TransponderStateEvent::with(['transponder:id,code', 'user:id,name'])
                ->latest('created_at')
                ->limit(15)
                ->get()
            : collect();

        $transpondersWithTimers = $transponders->map(function (Transponder $t) use ($hasEventsTable) {
            $times = $hasEventsTable ? $t->timePerSite() : [
                Transponder::SITE_ZACATECAS => 0,
                Transponder::SITE_TOLUCA => 0,
            ];

            $lastEvent = $hasEventsTable ? $t->events()->latest('created_at')->first() : null;
            $since = $lastEvent?->created_at ?? $t->created_at ?? now();
            $liveSeconds = max(0, $since->diffInSeconds(now()));

            return [
                'id' => $t->id,
                'code' => $t->code,
                'active_site' => $t->active_site,
                'is_on' => (bool) $t->is_on,
                'opposite_site' => $t->oppositeSite(),
                'time_current_seconds' => (int) ($times[$t->active_site] ?? 0),
                'time_other_seconds' => (int) ($times[$t->oppositeSite()] ?? 0),
                'time_current_human' => $this->humanDuration((int) ($times[$t->active_site] ?? 0)),
                'time_other_human' => $this->humanDuration((int) ($times[$t->oppositeSite()] ?? 0)),
                'live_since_iso' => $since->toIso8601String(),
                'live_initial_seconds' => $liveSeconds,
            ];
        })->values();

        $recentEventsWithDuration = $recentEvents->map(function ($ev) {
            $ev->previous_duration_human = $ev->previous_duration_seconds !== null
                ? $this->humanDuration((int) $ev->previous_duration_seconds)
                : null;
            return $ev;
        });

        return view('livewire.admin.modulators.modulator-panel', [
            'transponders' => $transpondersWithTimers,
            'recentEvents' => $recentEventsWithDuration,
            'siteZacatecas' => Transponder::SITE_ZACATECAS,
            'siteToluca' => Transponder::SITE_TOLUCA,
            'weatherBySite' => $this->weatherBySite,
            'weatherLastUpdatedAt' => $this->weatherLastUpdatedAt,
            'timezone' => $this->timezoneInfo(),
            'countZacatecas' => $transpondersWithTimers->where('active_site', Transponder::SITE_ZACATECAS)->count(),
            'countToluca' => $transpondersWithTimers->where('active_site', Transponder::SITE_TOLUCA)->count(),
            'zacatecasTransponders' => $transpondersWithTimers->where('active_site', Transponder::SITE_ZACATECAS)->values(),
            'tolucaTransponders' => $transpondersWithTimers->where('active_site', Transponder::SITE_TOLUCA)->values(),
        ]);
    }

    private function timezoneInfo(): array
    {
        $name = (string) config('app.timezone', 'UTC');
        $now = \Carbon\Carbon::now($name);
        $offset = $now->getOffset();

        $sign = $offset < 0 ? '-' : '+';
        $abs = abs($offset);
        $hours = intdiv($abs, 3600);
        $minutes = intdiv($abs % 3600, 60);

        $gmt = sprintf('GMT%s%02d:%02d', $sign, $hours, $minutes);
        $abbr = $now->format('T');

        return [
            'name' => $name,
            'gmt' => $gmt,
            'abbr' => $abbr,
            'label' => sprintf('%s (%s)', $gmt, $name),
            'now' => $now->format('Y-m-d H:i:s'),
            'now_ms' => (int) ($now->getTimestamp() * 1000),
            'format' => __('modulators.timezone_format_sample'),
        ];
    }

    private function authorizeAccess(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        $isMaster = $user->id === 1;
        $isDth = $user->area === 'DTH';

        if (! ($isMaster || $isDth)) {
            abort(403);
        }
    }

    private function humanDuration(int $seconds): string
    {
        if ($seconds < 0) {
            $seconds = 0;
        }

        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs = $seconds % 60;

        if ($days > 0) {
            return sprintf('%dd %02d:%02d:%02d', $days, $hours, $minutes, $secs);
        }

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }

    private function secondsSinceLastEvent(Transponder $transponder): int
    {
        $lastEvent = $transponder->events()->latest('created_at')->first();
        $since = $lastEvent?->created_at ?? $transponder->created_at ?? now();

        return max(0, $since->diffInSeconds(now()));
    }

    private function runSwitchCommand(string $code, string $fromSite, string $toSite): string
    {
        try {
            $exitCode = Artisan::call('modulators:switch', [
                'code' => $code,
                'from' => $fromSite,
                'to' => $toSite,
            ]);

            $output = Artisan::output();

            if ($exitCode !== 0) {
                \Illuminate\Support\Facades\Log::warning('modulators:switch returned non-zero', [
                    'code' => $code,
                    'from' => $fromSite,
                    'to' => $toSite,
                    'exit' => $exitCode,
                    'output' => $output,
                ]);
            }

            return trim($output);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to run modulators:switch', [
                'error' => $e->getMessage(),
            ]);
            return sprintf('TX %s POWER OFF @ %s', $code, $fromSite)
                . "\n" . sprintf('TX %s POWER ON  @ %s', $code, $toSite);
        }
    }
}
