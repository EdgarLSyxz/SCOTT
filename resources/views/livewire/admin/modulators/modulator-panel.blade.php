<div>
    <x-slot name="action">
        <span class="text-sm text-gray-500 dark:text-gray-400">
            <i class="fa-solid fa-satellite-dish mr-1.5"></i>
            {{ __('modulators.transponder_control_demo') }}
        </span>
    </x-slot>

    <script>
        function scottFormatDuration(totalSeconds) {
            if (totalSeconds < 0) totalSeconds = 0;
            const days = Math.floor(totalSeconds / 86400);
            const hours = Math.floor((totalSeconds % 86400) / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;
            const pad = (n) => String(n).padStart(2, '0');
            if (days > 0) {
                return days + 'd ' + pad(hours) + ':' + pad(minutes) + ':' + pad(seconds);
            }
            return pad(hours) + ':' + pad(minutes) + ':' + pad(seconds);
        }

        function scottUpdateLiveCounters() {
            const nowMs = Date.now();
            document.querySelectorAll('[data-live-since]').forEach(function (el) {
                const sinceMs = parseInt(el.getAttribute('data-live-since'), 10);
                if (isNaN(sinceMs)) return;
                const seconds = Math.max(0, Math.floor((nowMs - sinceMs) / 1000));
                const target = el.querySelector('[data-live-target]');
                if (target) {
                    target.textContent = scottFormatDuration(seconds);
                }
            });
        }

        function scottUpdateWeatherLegend() {
            const nowMs = Date.now();
            const legend = document.querySelector('[data-weather-legend]');
            if (!legend) return;
            const wrapper = legend.closest('[data-weather-last-updated]');
            const iso = wrapper && wrapper.getAttribute('data-weather-last-updated-iso');
            if (!iso) return;
            const updatedMs = Date.parse(iso);
            if (isNaN(updatedMs)) return;
            const diffSec = Math.max(0, Math.floor((nowMs - updatedMs) / 1000));
            let text;
            if (diffSec < 60) {
                text = '{{ __('modulators.weather_updated_just_now') }}';
            } else if (diffSec < 3600) {
                const minutes = Math.floor(diffSec / 60);
                text = '{{ __('modulators.weather_updated_minutes_ago', ['minutes' => '___MIN___']) }}'.replace('___MIN___', minutes);
            } else {
                const hours = Math.floor(diffSec / 3600);
                const minutes = Math.floor((diffSec % 3600) / 60);
                text = '{{ __('modulators.weather_updated_hours_ago', ['hours' => '___H___', 'minutes' => '___M___']) }}'.replace('___H___', hours).replace('___M___', minutes);
            }
            legend.textContent = '{{ __('modulators.weather_last_updated') }}: ' + text;
        }

        document.addEventListener('livewire:init', () => {
            scottUpdateLiveCounters();
            setInterval(scottUpdateLiveCounters, 1000);
            scottUpdateWeatherLegend();
            setInterval(scottUpdateWeatherLegend, 30000);

            Livewire.on('swal-confirm', (data) => {
                const payload = Array.isArray(data) ? data[0] : data;
                const transponderId = payload.transponderId;

                const ts = new Date().toISOString();
                const offCmd = `[${ts}] TX ${payload.transponderCode || ''} POWER OFF @ ${payload.from}`;
                const onCmd  = `[${ts}] TX ${payload.transponderCode || ''} POWER ON  @ ${payload.to}`;

                console.groupCollapsed('%c[modulators] Switch requested for ' + (payload.transponderCode || 'transponder'), 'color:#d97706;font-weight:bold;');
                console.log('%cOFF command → ' + offCmd, 'color:#dc2626;font-weight:bold;');
                console.log('%cON  command → ' + onCmd,  'color:#16a34a;font-weight:bold;');
                console.groupEnd();

                Swal.fire({
                    icon: 'warning',
                    title: payload.title,
                    text: payload.text,
                    showCancelButton: true,
                    confirmButtonColor: '#d97706',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: payload.confirmButtonText,
                    cancelButtonText: payload.cancelButtonText,
                    reverseButtons: true,
                    focusCancel: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        const ts2 = new Date().toISOString();
                        console.log('%c[modulators] Switch CONFIRMED for ' + (payload.transponderCode || 'transponder') + ' at ' + ts2, 'color:#d97706;font-weight:bold;');
                        Livewire.dispatch('confirmSwitchNow', { transponderId });
                    } else {
                        console.log('%c[modulators] Switch CANCELLED for ' + (payload.transponderCode || 'transponder'), 'color:#6b7280;');
                    }
                });
            });

            Livewire.on('switch-completed', (data) => {
                const payload = Array.isArray(data) ? data[0] : data;
                const ts = new Date().toISOString();
                console.log('%c[modulators] Switch EXECUTED for ' + payload.transponderCode + ' (' + payload.from + ' → ' + payload.to + ') at ' + ts, 'color:#16a34a;font-weight:bold;');
                if (payload.commands) {
                    payload.commands.split('\n').forEach(function (line) {
                        if (line.trim()) console.log('  ' + line);
                    });
                }
            });
        });
    </script>

    <div class="w-full bg-white rounded-lg shadow-2xl dark:border md:mt-0 xl:p-0 dark:bg-gray-800 dark:border-gray-700">
        <div class="p-6 space-y-6 sm:p-8">

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
                    <i class="fa-solid fa-tower-broadcast mr-1.5 text-amber-500"></i>
                    {{ __('modulators.modulators_panel') }}
                </h1>
                <p class="text-sm font-light leading-tight text-gray-500 dark:text-gray-400">
                    {{ __('modulators.modulators_subtitle') }}
                    <span class="font-semibold text-amber-600">{{ __($siteZacatecas) }}</span>
                    {{ __('modulators.and') }}
                    <span class="font-semibold text-amber-600">{{ __($siteToluca) }}</span>.
                    {{ __('modulators.golden_rule') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:bg-amber-900/20 dark:border-amber-800">
                    <div class="text-xs uppercase tracking-wider text-amber-700 dark:text-amber-300 font-semibold">
                        {{ __('modulators.current_uplink_site') }}
                    </div>
                    <div class="mt-1 text-2xl font-bold text-amber-800 dark:text-amber-200">
                        {{ __($siteZacatecas) }}
                    </div>
                </div>
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:bg-amber-900/20 dark:border-amber-800">
                    <div class="text-xs uppercase tracking-wider text-amber-700 dark:text-amber-300 font-semibold">
                        {{ __('modulators.backup_site') }}
                    </div>
                    <div class="mt-1 text-2xl font-bold text-amber-800 dark:text-amber-200">
                        {{ __($siteToluca) }}
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-sky-200 bg-sky-50/60 dark:bg-sky-900/10 dark:border-sky-800 p-4 sm:p-5">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-sky-900 dark:text-sky-100 flex items-center gap-2">
                            <i class="fa-solid fa-cloud-sun text-sky-500"></i>
                            {{ __('modulators.weather_section_title') }}
                        </h3>
                        <p class="text-xs text-sky-700/80 dark:text-sky-300/80">
                            {{ __('modulators.weather_section_subtitle') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-sky-700 dark:text-sky-300 inline-flex items-center gap-1.5"
                              data-weather-last-updated
                              @if($weatherLastUpdatedAt)
                                  data-weather-last-updated-iso="{{ $weatherLastUpdatedAt }}"
                              @endif>
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            <span data-weather-legend>
                                @if($weatherLastUpdatedAt)
                                    {{ __('modulators.weather_last_updated') }}:
                                    <span class="font-mono font-semibold">{{ \Carbon\Carbon::parse($weatherLastUpdatedAt)->format('Y-m-d H:i:s') }}</span>
                                @else
                                    {{ __('modulators.weather_last_updated_never') }}
                                @endif
                            </span>
                        </span>
                        <button type="button"
                                wire:click="refreshWeather(true)"
                                wire:loading.attr="disabled"
                                wire:target="refreshWeather(true)"
                                class="inline-flex items-center gap-1.5 text-white bg-sky-600 hover:bg-sky-700 focus:ring-4 focus:outline-none focus:ring-sky-300 font-semibold rounded-lg text-xs px-3 py-2 transition disabled:opacity-60 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="refreshWeather(true)">
                                <i class="fa-solid fa-rotate"></i>
                                {{ __('modulators.weather_refresh') }}
                            </span>
                            <span wire:loading wire:target="refreshWeather(true)">
                                <i class="fa-solid fa-spinner fa-spin"></i>
                                {{ __('modulators.weather_refreshing') }}
                            </span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    @foreach([$siteZacatecas => 'Zacatecas', $siteToluca => 'Toluca'] as $siteKey => $siteName)
                        @php
                            $payload = $weatherBySite[$siteKey] ?? null;
                        @endphp
                        <div class="rounded-lg border border-sky-200 bg-white dark:bg-gray-800 dark:border-sky-800 p-4 shadow-sm">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-xs uppercase tracking-wider text-sky-700 dark:text-sky-300 font-semibold">
                                        {{ $siteName }}
                                    </div>
                                    <div class="mt-1 flex items-baseline gap-2">
                                        @if($payload && $payload['current']['temperature'] !== null)
                                            <span class="text-3xl font-bold text-sky-900 dark:text-sky-100">
                                                {{ number_format((float) $payload['current']['temperature'], 1) }}°C
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                <i class="fa-solid {{ $payload['current']['icon'] }} mr-1 text-sky-500"></i>
                                                {{ $payload['current']['label'] }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                <i class="fa-solid fa-spinner fa-spin mr-1"></i>
                                                {{ __('modulators.weather_loading') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[11px] uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        {{ __('modulators.weather_forecast_3d') }}
                                    </div>
                                    <div class="text-xs text-gray-600 dark:text-gray-300">
                                        @if($payload)
                                            <span class="font-mono">{{ \Carbon\Carbon::parse($payload['fetched_at'])->format('H:i') }}</span>
                                            {{ __('modulators.weather_last_updated') }}
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if(! $payload)
                                <div class="mt-3 text-xs text-amber-700 dark:text-amber-300">
                                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                                    {{ __('modulators.weather_unavailable') }}
                                </div>
                            @else
                                <div class="mt-3 grid grid-cols-3 gap-2">
                                    @foreach($payload['daily'] as $idx => $day)
                                        @php
                                            $dayLabels = [
                                                __('modulators.weather_today'),
                                                __('modulators.weather_tomorrow'),
                                                __('modulators.weather_day_after'),
                                            ];
                                            $dayLabel = $dayLabels[$idx] ?? '';
                                        @endphp
                                        <div class="rounded-md border border-sky-100 dark:border-sky-900 bg-sky-50/60 dark:bg-sky-900/20 p-2 text-center">
                                            <div class="text-[11px] uppercase tracking-wider text-sky-700 dark:text-sky-300 font-semibold">
                                                {{ $dayLabel }}
                                            </div>
                                            <div class="text-sky-500 text-lg my-1">
                                                <i class="fa-solid {{ $day['icon'] }}"></i>
                                            </div>
                                            <div class="text-xs text-gray-700 dark:text-gray-200 font-mono">
                                                <span class="text-red-600 dark:text-red-300">{{ __('modulators.weather_max') }} {{ $day['temp_max'] !== null ? number_format((float) $day['temp_max'], 0) : '—' }}°</span>
                                                <span class="mx-1 text-gray-400">/</span>
                                                <span class="text-blue-600 dark:text-blue-300">{{ __('modulators.weather_min') }} {{ $day['temp_min'] !== null ? number_format((float) $day['temp_min'], 0) : '—' }}°</span>
                                            </div>
                                            @if(! is_null($day['precipitation_probability_max']))
                                                <div class="text-[10px] text-sky-700 dark:text-sky-300 mt-1">
                                                    <i class="fa-solid fa-droplet mr-0.5"></i>{{ (int) $day['precipitation_probability_max'] }}%
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            @if($transponders->isEmpty())
                <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-600 p-8 text-center text-gray-500 dark:text-gray-400">
                    <i class="fa-solid fa-circle-exclamation text-2xl mb-2"></i>
                    <p>{{ __('modulators.no_transponders_yet') }}</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                    @foreach($transponders as $t)
                        @php
                            $sinceMs = strtotime($t['live_since_iso']) * 1000;
                        @endphp
                        <div wire:key="tp-{{ $t['id'] }}"
                             data-live-since="{{ $sinceMs }}"
                             class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm flex flex-col gap-3">

                            <div class="flex items-center justify-between">
                                <div class="text-lg font-bold text-gray-900 dark:text-white">
                                    <i class="fa-solid fa-satellite mr-1 text-amber-500"></i>
                                    {{ $t['code'] }}
                                </div>
                                <span class="inline-flex items-center text-xs font-semibold px-2 py-1 rounded-full
                                    {{ $t['is_on'] ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200' }}">
                                    <span class="w-2 h-2 mr-1.5 rounded-full {{ $t['is_on'] ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                    {{ $t['is_on'] ? __('modulators.on') : __('modulators.off') }}
                                </span>
                            </div>

                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                <div class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    {{ __('modulators.active_site') }}
                                </div>
                                <div class="font-semibold text-amber-600 dark:text-amber-300">
                                    {{ __($t['active_site']) }}
                                </div>
                            </div>

                            <div class="rounded-md bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-3 py-2">
                                <div class="flex items-center justify-between text-xs text-amber-700 dark:text-amber-300">
                                    <span class="inline-flex items-center gap-1.5 font-semibold">
                                        <span class="relative flex h-2 w-2">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                        </span>
                                        {{ __('modulators.live_counter_label') }}
                                        <span class="text-amber-700/70 dark:text-amber-300/70">·</span>
                                        <span>{{ __($t['active_site']) }}</span>
                                    </span>
                                    <span class="font-mono text-base font-bold text-amber-800 dark:text-amber-200" data-live-target>
                                        {{ $t['time_current_human'] }}
                                    </span>
                                </div>
                            </div>

                            <div class="rounded-md bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 px-3 py-2">
                                <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400">
                                    <span>
                                        <i class="fa-solid fa-clock-rotate-left mr-1"></i>
                                        {{ __('modulators.time_in_other_site_short', ['site' => __($t['opposite_site'])]) }}
                                    </span>
                                    <span class="font-mono">
                                        {{ $t['time_other_human'] }}
                                    </span>
                                </div>
                            </div>

                            <button type="button"
                                wire:click="requestSwitch({{ $t['id'] }})"
                                wire:loading.attr="disabled"
                                wire:target="requestSwitch({{ $t['id'] }})"
                                class="w-full inline-flex justify-center items-center text-white bg-amber-600 hover:bg-amber-700 focus:ring-4 focus:outline-none focus:ring-amber-300 font-semibold rounded-lg text-sm px-4 py-2.5 text-center transition disabled:opacity-60 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="requestSwitch({{ $t['id'] }})">
                                    <i class="fa-solid fa-right-left mr-1.5"></i>
                                    {{ __('modulators.switch_to') }} {{ __($t['opposite_site']) }}
                                </span>
                                <span wire:loading wire:target="requestSwitch({{ $t['id'] }})">
                                    <i class="fa-solid fa-spinner fa-spin mr-1.5"></i>
                                    {{ __('modulators.switching') }}
                                </span>
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="w-full bg-white rounded-lg shadow-2xl dark:border mt-6 dark:bg-gray-800 dark:border-gray-700">
        <div class="p-6 space-y-4 sm:p-8">
            <h2 class="text-lg font-bold leading-tight tracking-tight text-gray-900 dark:text-white">
                <i class="fa-solid fa-clock-rotate-left mr-1.5 text-amber-500"></i>
                {{ __('modulators.recent_switch_log') }}
            </h2>

            @if($recentEvents->isEmpty())
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('modulators.no_switch_events') }}
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left text-gray-600 dark:text-gray-300">
                        <thead class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="py-2 pr-4">{{ __('modulators.when') }}</th>
                                <th class="py-2 pr-4">{{ __('modulators.transponder') }}</th>
                                <th class="py-2 pr-4">{{ __('modulators.from') }}</th>
                                <th class="py-2 pr-4">{{ __('modulators.to') }}</th>
                                <th class="py-2 pr-4">{{ __('modulators.previous_duration') }}</th>
                                <th class="py-2 pr-4">{{ __('modulators.commands_sent') }}</th>
                                <th class="py-2 pr-4">{{ __('modulators.user') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentEvents as $ev)
                                <tr wire:key="ev-{{ $ev->id }}" class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="py-2 pr-4 whitespace-nowrap">{{ $ev->created_at?->format('Y-m-d H:i:s') }}</td>
                                    <td class="py-2 pr-4 font-semibold text-gray-900 dark:text-white">{{ $ev->transponder?->code ?? '—' }}</td>
                                    <td class="py-2 pr-4">
                                        <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200">
                                            {{ __('modulators.off') }} · {{ $ev->from_site ? __($ev->from_site) : '—' }}
                                        </span>
                                    </td>
                                    <td class="py-2 pr-4">
                                        <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-200">
                                            {{ __('modulators.on') }} · {{ __($ev->to_site) }}
                                        </span>
                                    </td>
                                    <td class="py-2 pr-4 font-mono">
                                        {{ $ev->previous_duration_human ?? '—' }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        @if($ev->commands_log)
                                            <pre class="m-0 p-2 rounded bg-gray-900 text-amber-200 text-[11px] leading-snug whitespace-pre-wrap font-mono">{{ $ev->commands_log }}</pre>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="py-2 pr-4">{{ $ev->user?->name ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
