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
                    @foreach([$siteZacatecas, $siteToluca] as $siteKey)
                        @php
                            $payload = $weatherBySite[$siteKey] ?? null;
                            $siteName = $payload['label'] ?? __($siteKey);
                            $summary = $payload['rainfall_summary'] ?? null;
                            $isRainingNow = $payload && (($payload['current']['precipitation'] ?? 0) > 0 || ($payload['current']['rain'] ?? 0) > 0);
                            $timelineHours = $payload['hourly_timeline']['hours'] ?? [];
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
                                    @if($payload)
                                        <div class="mt-2 flex flex-wrap items-center gap-2 text-[11px]">
                                            @if($isRainingNow)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200 font-semibold">
                                                    <i class="fa-solid fa-cloud-showers-heavy"></i>
                                                    {{ __('modulators.weather_rain_now') }}
                                                </span>
                                            @endif
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                                <i class="fa-solid fa-wind"></i>
                                                {{ $payload['current']['wind_speed'] !== null ? number_format((float) $payload['current']['wind_speed'], 1) . ' km/h' : '—' }}
                                            </span>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                                <i class="fa-solid fa-droplet"></i>
                                                {{ $payload['current']['humidity'] !== null ? (int) $payload['current']['humidity'] . '%' : '—' }}
                                            </span>
                                        </div>
                                    @endif
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
                                @php
                                    $maxTimelineMm = 0.0;
                                    foreach ($timelineHours as $h) {
                                        if (($h['precipitation'] ?? 0) > $maxTimelineMm) {
                                            $maxTimelineMm = (float) $h['precipitation'];
                                        }
                                    }
                                    $hasTimelineRain = $maxTimelineMm > 0;
                                @endphp

                                <div class="mt-4 rounded-md border border-indigo-200 dark:border-indigo-900 bg-indigo-50/40 dark:bg-indigo-900/20 p-3">
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <div class="text-[11px] uppercase tracking-wider text-indigo-700 dark:text-indigo-300 font-semibold flex items-center gap-1.5">
                                            <i class="fa-solid fa-timeline"></i>
                                            {{ __('modulators.weather_timeline_title') }}
                                        </div>
                                        <div class="flex items-center gap-2 text-[9px] uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                            <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-gray-200 dark:bg-gray-700"></span>{{ __('modulators.weather_timeline_legend_dry') }}</span>
                                            <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-sky-300 dark:bg-sky-700"></span>{{ __('modulators.weather_timeline_legend_light') }}</span>
                                            <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-blue-400 dark:bg-blue-600"></span>{{ __('modulators.weather_timeline_legend_moderate') }}</span>
                                            <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-blue-700 dark:bg-blue-400"></span>{{ __('modulators.weather_timeline_legend_heavy') }}</span>
                                        </div>
                                    </div>

                                    @if(! $hasTimelineRain)
                                        <div class="text-xs text-emerald-700 dark:text-emerald-300 flex items-center gap-2 py-2">
                                            <i class="fa-solid fa-sun"></i>
                                            {{ __('modulators.weather_timeline_no_rain') }}
                                        </div>
                                    @else
                                        <div class="relative">
                                            <div class="flex items-end gap-[2px] h-16">
                                                @foreach($timelineHours as $h)
                                                    @php
                                                        $mm = (float) ($h['precipitation'] ?? 0);
                                                        $ratio = $maxTimelineMm > 0 ? ($mm / $maxTimelineMm) : 0;
                                                        $barHeight = $mm > 0 ? max(8, (int) round($ratio * 64)) : 3;
                                                        $intensity = $h['rain_intensity'] ?? 'none';
                                                        $barColors = [
                                                            'none' => 'bg-gray-200 dark:bg-gray-700',
                                                            'light' => 'bg-sky-300 dark:bg-sky-700',
                                                            'moderate' => 'bg-blue-400 dark:bg-blue-600',
                                                            'heavy' => 'bg-blue-700 dark:bg-blue-400',
                                                        ];
                                                        $barColor = $barColors[$intensity] ?? $barColors['none'];
                                                        $hourLabel = substr((string) $h['hour'], 0, 2);
                                                        $probText = $h['probability'] !== null ? (int) $h['probability'] . '%' : '';
                                                        $tooltipParts = [$hourLabel . ':00'];
                                                        if ($mm > 0) {
                                                            $tooltipParts[] = number_format($mm, 1) . ' mm';
                                                        }
                                                        if ($probText) {
                                                            $tooltipParts[] = $probText;
                                                        }
                                                        if (! empty($h['label'])) {
                                                            $tooltipParts[] = $h['label'];
                                                        }
                                                        $tooltip = implode(' · ', $tooltipParts);
                                                    @endphp
                                                    <div class="flex-1 flex flex-col items-center justify-end group relative" title="{{ $tooltip }}">
                                                        <div class="w-full rounded-sm {{ $barColor }} transition-all hover:opacity-80" style="height: {{ $barHeight }}px"></div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div class="flex items-center mt-1 text-[9px] text-gray-400 dark:text-gray-500 font-mono">
                                                @php
                                                    $hourCount = count($timelineHours);
                                                    $showEvery = max(1, (int) ceil($hourCount / 8));
                                                @endphp
                                                @for($i = 0; $i < $hourCount; $i++)
                                                    @if($i % $showEvery === 0)
                                                        <span class="flex-1 text-left">{{ substr((string) ($timelineHours[$i]['hour'] ?? ''), 0, 2) }}</span>
                                                    @else
                                                        <span class="flex-1"></span>
                                                    @endif
                                                @endfor
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @if($summary)
                                    @php
                                        $probLevel = $summary['max_probability_level'] ?? 'unknown';
                                        $probColors = [
                                            'low' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
                                            'moderate' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
                                            'high' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
                                            'unknown' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
                                        ];
                                        $probLabels = [
                                            'low' => 'modulators.weather_rain_prob_low',
                                            'moderate' => 'modulators.weather_rain_prob_moderate',
                                            'high' => 'modulators.weather_rain_prob_high',
                                            'unknown' => 'modulators.weather_rain_prob_unknown',
                                        ];
                                        $probLabel = __($probLabels[$probLevel] ?? 'modulators.weather_rain_prob_unknown');
                                        $probColor = $probColors[$probLevel] ?? $probColors['unknown'];
                                        $rainBarWidth = min(100, (int) ($summary['max_probability'] ?? 0));
                                    @endphp
                                    <div class="mt-3 rounded-md border border-blue-200 dark:border-blue-900 bg-blue-50/60 dark:bg-blue-900/20 p-3">
                                        <div class="flex items-center justify-between gap-2 mb-2">
                                            <div class="text-[11px] uppercase tracking-wider text-blue-700 dark:text-blue-300 font-semibold flex items-center gap-1.5">
                                                <i class="fa-solid fa-cloud-rain"></i>
                                                {{ __('modulators.weather_rain_summary') }}
                                            </div>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $probColor }}">
                                                {{ $probLabel }}
                                            </span>
                                        </div>

                                        <div class="h-1.5 w-full bg-blue-100 dark:bg-blue-900/40 rounded-full overflow-hidden">
                                            <div class="h-full bg-blue-500 dark:bg-blue-400 rounded-full transition-all" style="width: {{ $rainBarWidth }}%"></div>
                                        </div>
                                        <div class="mt-1 text-[10px] text-blue-700/80 dark:text-blue-300/80 text-right font-mono">
                                            {{ $summary['max_probability'] !== null ? (int) $summary['max_probability'] . '%' : '—' }}
                                        </div>

                                        <div class="mt-2 grid grid-cols-3 gap-2 text-[11px]">
                                            <div class="flex flex-col">
                                                <span class="text-gray-500 dark:text-gray-400 uppercase tracking-wider text-[10px]">{{ __('modulators.weather_rain_total_mm') }}</span>
                                                <span class="font-mono font-bold text-blue-800 dark:text-blue-200">
                                                    {{ number_format((float) $summary['total_precipitation_mm'], 1) }} mm
                                                </span>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-gray-500 dark:text-gray-400 uppercase tracking-wider text-[10px]">{{ __('modulators.weather_rain_total_hours') }}</span>
                                                <span class="font-mono font-bold text-blue-800 dark:text-blue-200">
                                                    {{ number_format((float) $summary['total_precipitation_hours'], 1) }} h
                                                </span>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-gray-500 dark:text-gray-400 uppercase tracking-wider text-[10px]">{{ __('modulators.weather_rainy_days') }}</span>
                                                <span class="font-mono font-bold text-blue-800 dark:text-blue-200">
                                                    {{ $summary['rainy_days'] }} / {{ count($payload['daily']) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    @foreach($payload['daily'] as $idx => $day)
                                        @php
                                            $dayLabels = [
                                                __('modulators.weather_today'),
                                                __('modulators.weather_tomorrow'),
                                                __('modulators.weather_day_after'),
                                            ];
                                            $dayLabel = $dayLabels[$idx] ?? '';
                                            $intensity = $day['rain_intensity'] ?? 'none';
                                            $intensityColors = [
                                                'none' => 'border-sky-200 dark:border-sky-900 bg-sky-50/60 dark:bg-sky-900/20',
                                                'light' => 'border-blue-200 dark:border-blue-900 bg-blue-50 dark:bg-blue-900/30',
                                                'moderate' => 'border-blue-300 dark:border-blue-800 bg-blue-100 dark:bg-blue-900/40',
                                                'heavy' => 'border-blue-500 dark:border-blue-700 bg-blue-200 dark:bg-blue-900/60',
                                            ];
                                            $intensityColor = $intensityColors[$intensity] ?? $intensityColors['none'];
                                            $intensityLabels = [
                                                'none' => 'modulators.weather_rain_intensity_none',
                                                'light' => 'modulators.weather_rain_intensity_light',
                                                'moderate' => 'modulators.weather_rain_intensity_moderate',
                                                'heavy' => 'modulators.weather_rain_intensity_heavy',
                                            ];
                                            $intensityLabel = __($intensityLabels[$intensity] ?? 'modulators.weather_rain_intensity_none');
                                            $dayProb = $day['precipitation_probability_max'] ?? null;
                                            $dayPrecip = $day['precipitation_sum'] ?? null;
                                            $dayHours = $day['precipitation_hours'] ?? null;
                                            $peak = $day['peak_hour'] ?? null;
                                            $windows = $day['rain_windows'] ?? [];
                                        @endphp
                                        <div class="rounded-md border p-2.5 {{ $intensityColor }}">
                                            <div class="flex items-center justify-between">
                                                <div class="text-[11px] uppercase tracking-wider text-sky-700 dark:text-sky-300 font-semibold">
                                                    {{ $dayLabel }}
                                                </div>
                                                <i class="fa-solid {{ $day['icon'] }} text-sky-500 text-base"></i>
                                            </div>
                                            <div class="text-xs text-gray-700 dark:text-gray-200 font-mono mt-1">
                                                <span class="text-red-600 dark:text-red-300">{{ __('modulators.weather_max') }} {{ $day['temp_max'] !== null ? number_format((float) $day['temp_max'], 0) : '—' }}°</span>
                                                <span class="mx-1 text-gray-400">/</span>
                                                <span class="text-blue-600 dark:text-blue-300">{{ __('modulators.weather_min') }} {{ $day['temp_min'] !== null ? number_format((float) $day['temp_min'], 0) : '—' }}°</span>
                                            </div>

                                            <div class="mt-2 pt-2 border-t border-current/10 space-y-1.5">
                                                <div class="flex items-center justify-between text-[10px]">
                                                    <span class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-300">
                                                        <i class="fa-solid fa-droplet text-blue-500"></i>
                                                        {{ __('modulators.weather_rain_probability') }}
                                                    </span>
                                                    <span class="font-mono font-semibold
                                                        @if(($dayProb ?? 0) >= 60) text-blue-700 dark:text-blue-300
                                                        @elseif(($dayProb ?? 0) >= 20) text-amber-700 dark:text-amber-300
                                                        @else text-gray-600 dark:text-gray-400
                                                        @endif">
                                                        {{ $dayProb !== null ? (int) $dayProb . '%' : '—' }}
                                                    </span>
                                                </div>
                                                <div class="flex items-center justify-between text-[10px]">
                                                    <span class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-300">
                                                        <i class="fa-solid fa-raindrops text-blue-500"></i>
                                                        {{ __('modulators.weather_rain_amount') }}
                                                    </span>
                                                    <span class="font-mono font-semibold text-blue-800 dark:text-blue-200">
                                                        {{ $dayPrecip !== null ? number_format((float) $dayPrecip, 1) . ' mm' : '—' }}
                                                    </span>
                                                </div>
                                                @if($dayHours !== null && $dayHours > 0)
                                                    <div class="flex items-center justify-between text-[10px]">
                                                        <span class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-300">
                                                            <i class="fa-solid fa-clock text-blue-500"></i>
                                                            {{ __('modulators.weather_rain_hours') }}
                                                        </span>
                                                        <span class="font-mono font-semibold text-blue-800 dark:text-blue-200">
                                                            {{ number_format((float) $dayHours, 1) }} h
                                                        </span>
                                                    </div>
                                                @endif

                                                @if($peak)
                                                    @php
                                                        $peakHour = substr((string) ($peak['hour'] ?? ''), 0, 2);
                                                        $peakProb = $peak['probability'] !== null ? (int) $peak['probability'] : null;
                                                    @endphp
                                                    <div class="flex items-center justify-between text-[10px]">
                                                        <span class="inline-flex items-center gap-1 text-gray-600 dark:text-gray-300">
                                                            <i class="fa-solid fa-arrow-up text-blue-600"></i>
                                                            {{ __('modulators.weather_peak_label') }}
                                                        </span>
                                                        <span class="font-mono font-semibold text-blue-800 dark:text-blue-200">
                                                            {{ $peakHour }}:00 · {{ number_format((float) $peak['precipitation'], 1) }} mm
                                                            @if($peakProb !== null) · {{ $peakProb }}% @endif
                                                        </span>
                                                    </div>
                                                @endif

                                                @if(count($windows) > 0)
                                                    <div class="pt-1">
                                                        <div class="text-[9px] uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                                            {{ __('modulators.weather_windows_label') }}
                                                        </div>
                                                        <div class="space-y-0.5">
                                                            @foreach($windows as $win)
                                                                <div class="text-[10px] font-mono text-blue-700 dark:text-blue-300 inline-flex items-center gap-1 mr-2">
                                                                    <i class="fa-solid {{ $win['icon'] }}"></i>
                                                                    {{ $win['start_hour'] }}–{{ $win['end_hour'] }}
                                                                    · {{ number_format((float) $win['precipitation_sum'], 1) }} mm
                                                                    @if(! empty($win['probability_max']))
                                                                        · {{ (int) $win['probability_max'] }}%
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif

                                                @if($intensity !== 'none')
                                                    <div class="text-center mt-1">
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-600 text-white text-[9px] font-bold uppercase tracking-wider">
                                                            <i class="fa-solid {{ $windows[0]['icon'] ?? 'fa-cloud-rain' }}"></i>
                                                            {{ $intensityLabel }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
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
