<?php

namespace App\Livewire\Admin\Modulators;

use App\Models\Transponder;
use App\Models\TransponderStateEvent;
use App\Models\User;
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

    public function requestSwitch(int $transponderId): void
    {
        $this->authorizeAccess();

        if (! Schema::hasTable('transponders')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => __('modulators.module_not_ready'),
                'text' => __('modulators.module_not_ready_text'),
            ]);
            return;
        }

        $transponder = Transponder::find($transponderId);
        if (! $transponder) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => __('modulators.transponder_not_found'),
            ]);
            return;
        }

        $fromSite = $transponder->active_site;
        $toSite = $transponder->oppositeSite();

        $this->dispatch('swal-confirm', [
            'transponderId' => $transponder->id,
            'transponderCode' => $transponder->code,
            'from' => $fromSite,
            'to' => $toSite,
            'title' => __('modulators.confirm_title', ['code' => $transponder->code]),
            'text' => __('modulators.confirm_text', [
                'from' => __($fromSite),
                'to' => __($toSite),
            ]),
            'confirmButtonText' => __('modulators.confirm_yes'),
            'cancelButtonText' => __('modulators.confirm_no'),
        ]);
    }

    public function confirmSwitch(int $transponderId): void
    {
        $this->authorizeAccess();

        if (! Schema::hasTable('transponders')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => __('modulators.module_not_ready'),
                'text' => __('modulators.module_not_ready_text'),
            ]);
            return;
        }

        $transponder = Transponder::find($transponderId);
        if (! $transponder) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => __('modulators.transponder_not_found'),
            ]);
            return;
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

        $this->dispatch('switch-completed', [
            'transponderCode' => $transponderCode,
            'from' => $fromSite,
            'to' => $toSite,
            'commands' => $commandsOutput,
        ]);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => __('modulators.switch_completed'),
            'text' => __('modulators.transponder_switched_text'),
        ]);
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
    }

    #[On('confirmSwitchNow')]
    public function onConfirmSwitchFromClient(int $transponderId): void
    {
        $this->confirmSwitch($transponderId);
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
        ]);
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
