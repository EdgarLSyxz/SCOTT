<divwire:poll.600000ms="refreshWeather">
@php
$areaIsDTH = Auth::user()->area === 'DTH';
$areaColor = fn(string $classes) => $areaIsDTH ? str_replace('amber', 'secondary', $classes) : str_replace('amber', 'primary', $classes);
@endphp
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

        function scottUpdateLocalClock() {
            document.querySelectorAll('[data-clock-ms]').forEach(function (el) {
                const baseMs = parseInt(el.getAttribute('data-clock-ms'), 10);
                if (isNaN(baseMs)) return;
                if (!el.hasAttribute('data-clock-loaded-at')) {
                    el.setAttribute('data-clock-loaded-at', String(Date.now()));
                }
                const loadedAt = parseInt(el.getAttribute('data-clock-loaded-at'), 10);
                const current = baseMs + (Date.now() - loadedAt);
                const tz = el.getAttribute('data-clock-tz') || 'UTC';
                const d = new Date(current);
                const pad = (n) => String(n).padStart(2, '0');
                try {
                    el.textContent = new Intl.DateTimeFormat('sv-SE', {
                        timeZone: tz,
                        year: 'numeric', month: '2-digit', day: '2-digit',
                        hour: '2-digit', minute: '2-digit', second: '2-digit',
                        hour12: false,
                    }).format(d).replace(',', '');
                } catch (e) {
                    el.textContent = d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate())
                        + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
                }
            });
        }

        document.addEventListener('livewire:init', () => {
            scottUpdateLiveCounters();
            setInterval(scottUpdateLiveCounters, 1000);
            scottUpdateWeatherLegend();
            setInterval(scottUpdateWeatherLegend, 30000);
            scottUpdateLocalClock();
            setInterval(scottUpdateLocalClock, 1000);
        });
    </script>

    <div class="bg-gradient-to-br from-white to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-lg shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700">
        <div class="p-6 space-y-6 sm:p-8">

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-right-left mr-1.5"></i>
                    {{ __('modulators.modulators') }}
                </h1>
                <div class="flex flex-col md:items-end gap-2">
                    <p class="text-sm font-light leading-tight text-gray-500 dark:text-gray-400">
                        {{ __('modulators.modulators_subtitle') }}
                        <span class="font-semibold {{ $areaColor('text-amber-600') }}">{{ __($siteZacatecas) }}</span>
                        {{ __('modulators.and') }}
                        <span class="font-semibold {{ $areaColor('text-amber-600') }}">{{ __($siteToluca) }}</span>.
                        {{ __('modulators.golden_rule') }}
                    </p>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row items-center justify-center gap-3">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                    <span class="inline-flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300">
                            <i class="fa-solid fa-clock"></i>
                        </span>
                        <span>
                            <span class="block text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">
                                {{ __('modulators.timezone_local_time') }}
                            </span>
                            <span class="block font-mono text-base font-bold text-gray-900 dark:text-white leading-tight"
                                  data-clock-ms="{{ $timezone['now_ms'] }}"
                                  data-clock-tz="{{ $timezone['name'] }}">{{ $timezone['now'] }}</span>
                        </span>
                    </span>

                    <span class="hidden lg:block h-8 w-px bg-gray-300 dark:bg-gray-600"></span>

                    <span class="inline-flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg {{ $areaColor('bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300') }}">
                            <i class="fa-solid fa-earth-americas"></i>
                        </span>
                        <span>
                            <span class="block text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">
                                {{ __('modulators.timezone_label') }}
                            </span>
                            <span class="block text-sm font-bold text-gray-900 dark:text-white leading-tight">
                                {{ $timezone['gmt'] }}
                                <span class="font-normal text-gray-500 dark:text-gray-400">· {{ $timezone['name'] }}</span>
                                @if($timezone['abbr'])
                                    <span class="ml-1 text-[10px] font-bold px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200">{{ $timezone['abbr'] }}</span>
                                @endif
                            </span>
                        </span>
                    </span>
                </div>
            </div>

            <div>
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <i class="fa-solid fa-cloud-sun"></i>
                            {{ __('modulators.weather_section_title') }}
                        </h3>
                        <p class="text-xs text-gray-700 dark:text-gray-300">
                            {{ __('modulators.weather_section_subtitle') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-700 dark:text-gray-300 inline-flex items-center gap-1.5"
                              data-weather-last-updated
                              @if($weatherLastUpdatedAt)
                                  data-weather-last-updated-iso="{{ $weatherLastUpdatedAt }}"
                              @endif>
                            <i class="fa-solid fa-clock"></i>
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
                                class="inline-flex items-center gap-1.5 text-white bg-sky-600 hover:bg-sky-700 focus:ring-4 focus:outline-none focus:ring-sky-300 font-semibold rounded-lg text-xs px-3 py-2 disabled:opacity-60 disabled:cursor-not-allowed">
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
                        <div class="rounded-lg border border-gray-200 bg-white dark:bg-gray-800 dark:border-gray-700 p-4 shadow-sm">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-xs uppercase tracking-wider text-gray-700 dark:text-gray-300 font-semibold">
                                        {{ $siteName }}
                                    </div>
                                    <div class="mt-1 flex items-baseline gap-2">
                                        @if($payload && $payload['current']['temperature'] !== null)
                                            <span class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                                                {{ number_format((float) $payload['current']['temperature'], 1) }}°C
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                <i class="fa-solid {{ $payload['current']['icon'] }} mr-1"></i>
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
                                        @php
        $uvNow = $payload['current']['uv_index'] ?? null;
        $uvNowLevel = \App\Services\WeatherService::uvLevelStatic($uvNow);
        $uvNowColors = [
            'low' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
            'moderate' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
            'high' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-200',
            'very_high' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200',
            'extreme' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-200',
            'unknown' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
        ];
        $uvNowLabels = [
            'low' => 'modulators.weather_sun_uv_low',
            'moderate' => 'modulators.weather_sun_uv_moderate',
            'high' => 'modulators.weather_sun_uv_high',
            'very_high' => 'modulators.weather_sun_uv_very_high',
            'extreme' => 'modulators.weather_sun_uv_extreme',
            'unknown' => 'modulators.weather_sun_uv_unknown',
        ];
        $radNow = $payload['current']['shortwave_radiation'] ?? null;
                                        @endphp
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
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full {{ $uvNowColors[$uvNowLevel] ?? $uvNowColors['unknown'] }}">
                                                <i class="fa-solid fa-sun"></i>
                                                {{ __('modulators.weather_sun_uv_index') }}: {{ $uvNow !== null ? number_format((float) $uvNow, 1) : '—' }}
                                                <span class="font-semibold">· {{ __($uvNowLabels[$uvNowLevel] ?? 'modulators.weather_sun_uv_unknown') }}</span>
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

                            @if(!$payload)
                                <div class="mt-3 text-xs {{ $areaColor('text-amber-700 dark:text-amber-300') }}">
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
                                    </div>

                                    @if(!$hasTimelineRain)
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
                if (!empty($h['label'])) {
                    $tooltipParts[] = $h['label'];
                }
                $tooltip = implode(' · ', $tooltipParts);
                                                    @endphp
                                                    <div class="flex-1 flex flex-col items-center justify-end group relative" title="{{ $tooltip }}">
                                                        <div class="w-full rounded-sm {{ $barColor }} hover:opacity-80" style="height: {{ $barHeight }}px"></div>
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

                                    <div class="flex items-center justify-end gap-2 mt-2 pt-2 border-t border-indigo-200 dark:border-indigo-900 text-[9px] uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-gray-200 dark:bg-gray-700"></span>{{ __('modulators.weather_timeline_legend_dry') }}</span>
                                        <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-sky-300 dark:bg-sky-700"></span>{{ __('modulators.weather_timeline_legend_light') }}</span>
                                        <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-blue-400 dark:bg-blue-600"></span>{{ __('modulators.weather_timeline_legend_moderate') }}</span>
                                        <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-blue-700 dark:bg-blue-400"></span>{{ __('modulators.weather_timeline_legend_heavy') }}</span>
                                    </div>
                                </div>

                                @php
        $sunSummary = $payload['sun_summary'] ?? null;
                                @endphp

                                @if($summary || $sunSummary)
                                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        @if($summary)
                                            @php
                $probLevel = $summary['max_probability_level'] ?? 'unknown';
                $probColors = [
                    'low' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
                    'moderate' => $areaColor('bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200'),
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
                                            <div class="rounded-md border border-blue-200 dark:border-blue-900 bg-blue-50/60 dark:bg-blue-900/20 p-3">
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
                                                    <div class="h-full bg-blue-500 dark:bg-blue-400 rounded-full" style="width: {{ $rainBarWidth }}%"></div>
                                                </div>
                                                <div class="mt-1 text-[10px] text-blue-700/80 dark:text-blue-300/80 text-right font-mono">
                                                    {{ $summary['max_probability'] !== null ? (int) $summary['max_probability'] . '%' : '—' }}
                                                </div>

                                                <div class="mt-2 grid grid-cols-2 gap-2 text-[11px]">
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

                                        @if($sunSummary)
                                            @php
                $sunUvLevel = $sunSummary['max_uv_level'] ?? 'unknown';
                $sunUvColors = [
                    'low' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
                    'moderate' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
                    'high' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-200',
                    'very_high' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200',
                    'extreme' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-200',
                    'unknown' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
                ];
                $sunUvLabels = [
                    'low' => 'modulators.weather_sun_uv_low',
                    'moderate' => 'modulators.weather_sun_uv_moderate',
                    'high' => 'modulators.weather_sun_uv_high',
                    'very_high' => 'modulators.weather_sun_uv_very_high',
                    'extreme' => 'modulators.weather_sun_uv_extreme',
                    'unknown' => 'modulators.weather_sun_uv_unknown',
                ];
                $sunUvLabel = __($sunUvLabels[$sunUvLevel] ?? 'modulators.weather_sun_uv_unknown');
                $sunUvColor = $sunUvColors[$sunUvLevel] ?? $sunUvColors['unknown'];
                $sunUvValue = $sunSummary['max_uv_index'] !== null ? number_format((float) $sunSummary['max_uv_index'], 1) : '—';
                $sunUvBarWidth = $sunSummary['max_uv_index'] !== null
                    ? min(100, (int) round(((float) $sunSummary['max_uv_index'] / 11.0) * 100))
                    : 0;
                $sunRadiation = $sunSummary['total_radiation_mj'] !== null ? number_format((float) $sunSummary['total_radiation_mj'], 1) . ' MJ/m²' : '—';
                $sunSeconds = (int) ($sunSummary['total_sunshine_seconds'] ?? 0);
                $sunHours = $sunSeconds > 0 ? number_format($sunSeconds / 3600, 1) . ' h' : '—';
                $sunFirst = $sunSummary['first_sunrise'] ? \Carbon\Carbon::parse($sunSummary['first_sunrise'])->format('H:i') : '—';
                $sunLast = $sunSummary['last_sunset'] ? \Carbon\Carbon::parse($sunSummary['last_sunset'])->format('H:i') : '—';
                                            @endphp
                                            <div class="rounded-md border border-amber-200 dark:border-amber-900 bg-amber-50/60 dark:bg-amber-900/20 p-3">
                                                <div class="flex items-center justify-between gap-2 mb-2">
                                                    <div class="text-[11px] uppercase tracking-wider text-amber-700 dark:text-amber-300 font-semibold flex items-center gap-1.5">
                                                        <i class="fa-solid fa-sun"></i>
                                                        {{ __('modulators.weather_sun_summary') }}
                                                    </div>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $sunUvColor }}">
                                                        {{ $sunUvLabel }}
                                                    </span>
                                                </div>

                                                <div class="h-1.5 w-full bg-amber-100 dark:bg-amber-900/40 rounded-full overflow-hidden">
                                                    <div class="h-full bg-amber-500 dark:bg-amber-400 rounded-full" style="width: {{ $sunUvBarWidth }}%"></div>
                                                </div>
                                                <div class="mt-1 text-[10px] text-amber-700/80 dark:text-amber-300/80 text-right font-mono">
                                                    {{ __('modulators.weather_sun_uv_index') }}: {{ $sunUvValue }}
                                                </div>

                                                <div class="mt-2 grid grid-cols-2 gap-2 text-[11px]">
                                                        <div class="flex flex-col">
                                                        <span class="text-gray-500 dark:text-gray-400 uppercase tracking-wider text-[10px]">{{ __('modulators.weather_sun_radiation') }}</span>
                                                        <span class="font-mono font-bold text-amber-800 dark:text-amber-200">{{ $sunRadiation }}</span>
                                                    </div>
                                                    <div class="flex flex-col">
                                                        <span class="text-gray-500 dark:text-gray-400 uppercase tracking-wider text-[10px]">{{ __('modulators.weather_sun_sunshine') }}</span>
                                                        <span class="font-mono font-bold text-amber-800 dark:text-amber-200">{{ $sunHours }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
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
            $daySun = $day['sun'] ?? [];
            $dayUv = $daySun['uv_index_max'] ?? null;
            $dayUvLevel = \App\Services\WeatherService::uvLevelStatic($dayUv !== null ? (float) $dayUv : null);
            $dayRad = $daySun['shortwave_radiation_sum'] ?? null;
            $daySunshineSec = $daySun['sunshine_duration_seconds'] ?? null;
            $dayDaylightSec = $daySun['daylight_duration_seconds'] ?? null;
            $daySunrise = $daySun['sunrise'] ?? null;
            $daySunset = $daySun['sunset'] ?? null;
                                        @endphp
                                        <div class="rounded-md border p-2.5 flex flex-col h-[340px] {{ $intensityColor }}">
                                            <div class="flex items-center justify-between">
                                                <div class="text-[11px] uppercase tracking-wider text-sky-700 dark:text-sky-300 font-semibold">
                                                    {{ $dayLabel }}
                                                </div>
                                                <i class="fa-solid {{ $day['icon'] }} text-sky-500 w-4 h-4 text-[14px] leading-none inline-flex items-center justify-center shrink-0"></i>
                                            </div>
                                            <div class="text-xs text-gray-700 dark:text-gray-200 font-mono mt-1">
                                                <span class="text-red-600 dark:text-red-300">{{ __('modulators.weather_max') }} {{ $day['temp_max'] !== null ? number_format((float) $day['temp_max'], 0) : '—' }}°</span>
                                                <span class="mx-1 text-gray-400">/</span>
                                                <span class="text-blue-600 dark:text-blue-300">{{ __('modulators.weather_min') }} {{ $day['temp_min'] !== null ? number_format((float) $day['temp_min'], 0) : '—' }}°</span>
                                            </div>

                                            <div class="mt-2 mb-2 pt-2 border-t border-current/10 space-y-1.5 flex-1 min-h-0 flex flex-col">
                                                <div class="text-[9px] uppercase tracking-wider text-blue-700 dark:text-blue-300 font-semibold flex items-center gap-1.5">
                                                    <i class="fa-solid fa-cloud-rain w-3.5 h-3.5 text-[12px] leading-none inline-flex items-center justify-center shrink-0"></i>
                                                    {{ __('modulators.weather_rain_section_title') }}
                                                </div>
                                                <div class="flex items-center justify-between text-[10px]">
                                                    <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                                                        <i class="fa-solid fa-droplet text-blue-500 w-3.5 h-3.5 text-[12px] leading-none inline-flex items-center justify-center shrink-0"></i>
                                                        {{ __('modulators.weather_rain_probability') }}
                                                    </span>
                                                    <span class="font-mono font-semibold text-blue-700 dark:text-blue-300">
                                                        {{ $dayProb !== null ? (int) $dayProb . '%' : '—' }}
                                                    </span>
                                                </div>
                                                <div class="flex items-center justify-between text-[10px]">
                                                    <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                                                        <i class="fa-solid fa-droplet text-blue-500 w-3.5 h-3.5 text-[12px] leading-none inline-flex items-center justify-center shrink-0"></i>
                                                        {{ __('modulators.weather_rain_amount') }}
                                                    </span>
                                                    <span class="font-mono font-semibold text-blue-800 dark:text-blue-200">
                                                        {{ $dayPrecip !== null ? number_format((float) $dayPrecip, 1) . ' mm' : '—' }}
                                                    </span>
                                                </div>
                                                @php
            $hasHours = $dayHours !== null && $dayHours > 0;
            $peakHour = $peak ? substr((string) ($peak['hour'] ?? ''), 0, 2) : null;
            $peakProb = $peak && $peak['probability'] !== null ? (int) $peak['probability'] : null;
                                                @endphp
                                                <div class="flex items-center justify-between text-[10px]">
                                                    <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                                                        <i class="fa-solid fa-clock text-blue-500 w-3.5 h-3.5 text-[12px] leading-none inline-flex items-center justify-center shrink-0"></i>
                                                        {{ __('modulators.weather_rain_hours') }}
                                                    </span>
                                                    <span class="font-mono font-semibold text-blue-800 dark:text-blue-200">
                                                        {{ $hasHours ? number_format((float) $dayHours, 1) . ' h' : '—' }}
                                                    </span>
                                                </div>

                                                <div class="flex items-center justify-between text-[10px]">
                                                    <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                                                        <i class="fa-solid fa-arrow-up text-blue-600 w-3.5 h-3.5 text-[12px] leading-none inline-flex items-center justify-center shrink-0"></i>
                                                        {{ __('modulators.weather_peak_label') }}
                                                    </span>
                                                    <span class="font-mono font-semibold text-blue-800 dark:text-blue-200">
                                                        @if($peak)
                                                            {{ $peakHour }}:00 · {{ number_format((float) $peak['precipitation'], 1) }} mm
                                                            @if($peakProb !== null) · {{ $peakProb }}% @endif
                                                        @else
                                                            —
                                                        @endif
                                                    </span>
                                                </div>

                                                @if(count($windows) > 0)
                                                    <div class="space-y-0.5 overflow-y-auto min-h-0 flex-1 pr-1">
                                                        @foreach($windows as $win)
                                                            <div class="text-[10px] font-mono text-blue-700 dark:text-blue-300 inline-flex items-center gap-1 mr-2">
                                                                <i class="fa-solid {{ $win['icon'] }} w-3.5 h-3.5 text-[12px] leading-none inline-flex items-center justify-center shrink-0"></i>
                                                                {{ $win['start_hour'] }}–{{ $win['end_hour'] }}
                                                                · {{ number_format((float) $win['precipitation_sum'], 1) }} mm
                                                                @if(!empty($win['probability_max']))
                                                                    · {{ (int) $win['probability_max'] }}%
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="space-y-0.5 pr-1" aria-hidden="true"></div>
                                                @endif

                                                @php
            $daySunriseStr = $daySunrise ? \Carbon\Carbon::parse($daySunrise)->format('H:i') : null;
            $daySunsetStr = $daySunset ? \Carbon\Carbon::parse($daySunset)->format('H:i') : null;
            $dayDaylightStr = $dayDaylightSec !== null ? sprintf('%dh %02dm', intdiv((int) $dayDaylightSec, 3600), (intdiv((int) $dayDaylightSec, 60) % 60)) : null;
            $daySunshineStr = $daySunshineSec !== null ? sprintf('%dh %02dm', intdiv((int) $daySunshineSec, 3600), (intdiv((int) $daySunshineSec, 60) % 60)) : null;
            $dayRadMj = $dayRad !== null ? number_format(((float) $dayRad) / 1000, 1) . ' MJ/m²' : null;
                                                @endphp
                                                @if($dayUv !== null || $dayRadMj || $daySunriseStr || $dayDaylightStr)
                                                    <div class="mt-2 pt-2 border-t border-current/10 space-y-1">
                                                        <div class="text-[9px] uppercase tracking-wider text-amber-700 dark:text-amber-300 font-semibold flex items-center gap-1.5">
                                                                <i class="fa-solid fa-sun w-3.5 h-3.5 text-[12px] leading-none inline-flex items-center justify-center shrink-0"></i>
                                                                {{ __('modulators.weather_sun_section_title') }}
                                                            </div>
                                                            @if($dayUv !== null)
                                                                <div class="flex items-center justify-between text-[10px]">
                                                                    <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                                                                        <i class="fa-solid fa-sun text-amber-500 w-3.5 h-3.5 text-[12px] leading-none inline-flex items-center justify-center shrink-0"></i>
                                                                        {{ __('modulators.weather_sun_uv_index') }}
                                                                    </span>
                                                                    <span class="font-mono font-semibold text-amber-800 dark:text-amber-200">
                                                                        {{ number_format((float) $dayUv, 1) }}
                                                                    </span>
                                                                </div>
                                                            @endif
                                                            @if($dayRadMj)
                                                                <div class="flex items-center justify-between text-[10px]">
                                                                    <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                                                                        <i class="fa-solid fa-solar-panel text-amber-500 w-3.5 h-3.5 text-[12px] leading-none inline-flex items-center justify-center shrink-0"></i>
                                                                        {{ __('modulators.weather_sun_radiation') }}
                                                                    </span>
                                                                    <span class="font-mono font-semibold text-amber-800 dark:text-amber-200">{{ $dayRadMj }}</span>
                                                                </div>
                                                            @endif
                                                            @if($daySunriseStr || $daySunsetStr)
                                                                <div class="flex items-center justify-between text-[10px]">
                                                                    <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                                                                        <i class="fa-solid fa-cloud-sun text-amber-500 w-3.5 h-3.5 text-[12px] leading-none inline-flex items-center justify-center shrink-0"></i>
                                                                        {{ __('modulators.weather_sun_sunrise') }} / {{ __('modulators.weather_sun_sunset') }}
                                                                    </span>
                                                                    <span class="font-mono font-semibold text-amber-800 dark:text-amber-200">
                                                                        {{ $daySunriseStr ?? '—' }} · {{ $daySunsetStr ?? '—' }}
                                                                    </span>
                                                                </div>
                                                            @endif
                                                            @if($dayDaylightStr)
                                                                <div class="flex items-center justify-between text-[10px]">
                                                                    <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                                                                        <i class="fa-solid fa-clock text-amber-500 w-3.5 h-3.5 text-[12px] leading-none inline-flex items-center justify-center shrink-0"></i>
                                                                        {{ __('modulators.weather_sun_daylight') }}
                                                                    </span>
                                                                    <span class="font-mono font-semibold text-amber-800 dark:text-amber-200">{{ $dayDaylightStr }}</span>
                                                                </div>
                                                            @endif
                                                            @if($daySunshineStr)
                                                                <div class="flex items-center justify-between text-[10px]">
                                                                    <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                                                                        <i class="fa-solid fa-sun text-amber-500 w-3.5 h-3.5 text-[12px] leading-none inline-flex items-center justify-center shrink-0"></i>
                                                                        {{ __('modulators.weather_sun_sunshine_duration') }}
                                                                    </span>
                                                                    <span class="font-mono font-semibold text-amber-800 dark:text-amber-200">{{ $daySunshineStr }}</span>
                                                                </div>
                                                            @endif
                                                    </div>
                                                @endif
                                            </div>

                                            @if($intensity !== 'none')
                                                <div class="mt-auto pt-2 text-center">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-600 text-white text-[9px] font-bold uppercase tracking-wider">
                                                        <i class="fa-solid {{ $windows[0]['icon'] ?? 'fa-cloud-rain' }}"></i>
                                                        {{ $intensityLabel }}
                                                    </span>
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
        </div>
    </div>

        <div class="w-full bg-gradient-to-br from-white to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-lg shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700 mt-6 p-6">

            @if($transponders->isEmpty())
                <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-600 p-8 text-center text-gray-500 dark:text-gray-400">
                    <i class="fa-solid fa-circle-exclamation text-2xl mb-2"></i>
                    <p>{{ __('modulators.no_transponders_yet') }}</p>
                </div>
            @else
                <div x-data="{ filter: 'all', grouped: false }">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 px-4 py-3 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3">
                        <div class="flex flex-col gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 inline-flex items-center gap-1.5">
                                <i class="fa-solid fa-layer-group {{ $areaColor('text-amber-500') }}"></i>
                                {{ __('modulators.filter_title') }}
                            </span>
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button"
                                    @click="filter = 'all'"
                                    :class="filter === 'all' ? '{{ $areaColor('bg-amber-600 text-white border-amber-600 shadow') }}' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 {{ $areaColor('hover:border-amber-400') }}'"
                                    class="inline-flex items-center gap-1.5 rounded-lg border-2 px-3 py-1.5 text-xs font-bold">
                                    <i class="fa-solid fa-border-all"></i>
                                    {{ __('modulators.filter_all') }}
                                    <span class="ml-0.5 rounded-full bg-black/10 px-1.5 text-[10px]">{{ $transponders->count() }}</span>
                                </button>

                                @foreach([$siteZacatecas => $countZacatecas, $siteToluca => $countToluca] as $siteKey => $siteCount)
                                    <button type="button"
                                            data-site="{{ $siteKey }}"
                                            @click="filter = $el.dataset.site"
                                            :class="filter === $el.dataset.site ? '{{ $areaColor('bg-amber-600 text-white border-amber-600 shadow') }}' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 {{ $areaColor('hover:border-amber-400') }}'"
                                            class="inline-flex items-center gap-1.5 rounded-lg border-2 px-3 py-1.5 text-xs font-bold">
                                        <i class="fa-solid fa-tower-broadcast"></i>
                                        {{ __($siteKey) }}
                                        <span class="ml-0.5 rounded-full bg-black/10 px-1.5 text-[10px]">{{ $siteCount }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <button type="button"
                                @click="grouped = !grouped"
                                :class="grouped ? '{{ $areaColor('border-amber-500 bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-200') }}' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 {{ $areaColor('hover:border-amber-400') }}'"
                                class="inline-flex items-center gap-2 rounded-lg border-2 px-3 py-1.5 text-xs font-bold">
                                <i class="fa-solid fa-filter"></i>
                                {{ __('modulators.filter_group_toggle') }}
                                <span class="relative inline-flex h-4 w-7 items-center rounded-full"
                                    :class="grouped ? '{{ $areaColor('bg-amber-600') }}' : 'bg-gray-300 dark:bg-gray-600'">
                                <span class="inline-block h-3 w-3 transform rounded-full bg-white"
                                        :class="grouped ? 'translate-x-3.5' : 'translate-x-0.5'"></span>
                                </span>
                            </button>
                            @if($canManagePin)
                                @php $pinSettingsLength = (int) config('modulators.switch_pin_length', 4); @endphp
                                <div x-data="window.pinSettingsModal({{ $pinSettingsLength }}, {
                                        invalidTitle: @js(__('modulators.pin_invalid')),
                                        lengthText: @js(__('modulators.pin_length_text', ['length' => $pinSettingsLength])),
                                        mismatchText: @js(__('modulators.pin_confirmation_mismatch')),
                                    })">
                                    <button type="button"
                                            @click="open = true"
                                            class="inline-flex items-center gap-1.5 rounded-lg border-2 px-3 py-1.5 text-xs font-bold transition bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 {{ $areaColor('hover:border-amber-400') }}">
                                        <i class="fa-solid fa-key"></i>
                                        {{ __('modulators.pin_settings_button') }}
                                    </button>

                                    <div x-show="open" x-cloak style="display: none;"
                                         class="fixed inset-0 z-[100] flex items-center justify-center p-4"
                                         @keydown.escape.window="close()">
                                        <div x-show="open" x-cloak style="display: none;"
                                             x-transition:enter="ease-out duration-150"
                                             x-transition:enter-start="opacity-0"
                                             x-transition:enter-end="opacity-100"
                                             x-transition:leave="ease-in duration-100"
                                             x-transition:leave-start="opacity-100"
                                             x-transition:leave-end="opacity-0"
                                             class="absolute inset-0 bg-gray-900/70 backdrop-blur-sm"
                                             @click="close()"></div>

                                        <div x-show="open" x-cloak style="display: none;"
                                             x-transition:enter="ease-out duration-200"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             x-transition:leave="ease-in duration-150"
                                             x-transition:leave-start="opacity-100 scale-100"
                                             x-transition:leave-end="opacity-0 scale-95"
                                             class="relative w-full max-w-sm rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-2xl overflow-hidden text-left">

                                            <div class="px-5 py-4 {{ $areaColor('bg-amber-600') }} text-white flex items-center gap-2">
                                                <div class="flex-shrink-0">
                                                    <i class="fa-solid fa-key text-2xl"></i>
                                                </div>
                                                <div class="flex flex-col items-start">
                                                    <span class="font-bold">Actualizar PIN de conmutación</span>
                                                    <p class="text-[11px] text-white/90">
                                                        Ingresa {{ $pinSettingsLength }} dígitos numéricos para el nuevo PIN.
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="p-5 space-y-3">
                                                <div>
                                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1">
                                                        {{ __('modulators.pin_current_label') }}
                                                    </label>
                                                    <input type="password" inputmode="numeric" pattern="[0-9]*"
                                                           maxlength="{{ $pinSettingsLength }}"
                                                           x-model="currentPin" @input="sanitize('currentPin')"
                                                           autocomplete="off" :disabled="busy"
                                                           class="w-full rounded-lg border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white px-3 py-2 text-center text-lg font-bold tracking-[0.4em] disabled:opacity-60 {{ $areaColor('focus:ring-4 focus:ring-amber-300 focus:border-amber-500 dark:focus:ring-amber-700') }}">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1">
                                                        {{ __('modulators.pin_new_label') }}
                                                    </label>
                                                    <input type="password" inputmode="numeric" pattern="[0-9]*"
                                                           maxlength="{{ $pinSettingsLength }}"
                                                           x-model="newPin" @input="sanitize('newPin')"
                                                           autocomplete="off" :disabled="busy"
                                                           class="w-full rounded-lg border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white px-3 py-2 text-center text-lg font-bold tracking-[0.4em] disabled:opacity-60 {{ $areaColor('focus:ring-4 focus:ring-amber-300 focus:border-amber-500 dark:focus:ring-amber-700') }}">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1">
                                                        {{ __('modulators.pin_new_confirm_label') }}
                                                    </label>
                                                    <input type="password" inputmode="numeric" pattern="[0-9]*"
                                                           maxlength="{{ $pinSettingsLength }}"
                                                           x-model="newPinConfirmation" @input="sanitize('newPinConfirmation')"
                                                           autocomplete="off" :disabled="busy"
                                                           class="w-full rounded-lg border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white px-3 py-2 text-center text-lg font-bold tracking-[0.4em] disabled:opacity-60 {{ $areaColor('focus:ring-4 focus:ring-amber-300 focus:border-amber-500 dark:focus:ring-amber-700') }}">
                                                </div>
                                            </div>

                                            <div class="px-5 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end gap-2">
                                                <button type="button"
                                                        @click="close()"
                                                        :disabled="busy"
                                                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-60">
                                                    <i class="fa-solid fa-xmark"></i>
                                                    {{ __('modulators.modal_cancel') }}
                                                </button>
                                                <button type="button"
                                                        @click="submit()"
                                                        :disabled="busy"
                                                        class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-bold text-white disabled:opacity-60 {{ $areaColor('bg-amber-600 hover:bg-amber-700') }}">
                                                    <i class="fa-solid fa-floppy-disk" x-show="!busy"></i>
                                                    <i class="fa-solid fa-spinner fa-spin" x-show="busy"></i>
                                                    <span x-show="!busy">{{ __('modulators.pin_settings_save') }}</span>
                                                    <span x-show="busy">Actualizando...</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div x-show="!grouped" class="mt-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
                            @foreach($transponders as $t)
                                <div wire:key="flat-tp-{{ $t['id'] }}"
                                     data-site="{{ $t['active_site'] }}"
                                     x-show="filter === 'all' || filter === $el.dataset.site"
                                     class="h-full">
                                    @include('livewire.admin.modulators.partials.ku-card', ['t' => $t])
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div x-show="grouped" x-cloak class="mt-4 space-y-5">
                        @foreach([
                                ['site' => $siteZacatecas, 'items' => $zacatecasTransponders, 'label' => __('modulators.group_zacatecas')],
                                ['site' => $siteToluca, 'items' => $tolucaTransponders, 'label' => __('modulators.group_toluca')],
                            ] as $group)
                                <section wire:key="grp-{{ md5($group['site']) }}"
                                         data-site="{{ $group['site'] }}"
                                         x-show="filter === 'all' || filter === $el.dataset.site">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">
                                            <i class="fa-solid fa-tower-broadcast"></i>
                                        </span>
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ $group['label'] }}</h3>
                                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">
                                            {{ $group['items']->count() }}
                                        </span>
                                        <span class="flex-1 h-px bg-gray-200 dark:bg-gray-700"></span>
                                    </div>

                                    @if($group['items']->isEmpty())
                                        <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-600 p-5 text-center text-xs text-gray-500 dark:text-gray-400">
                                            {{ __('modulators.group_empty') }}
                                        </div>
                                    @else
                                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
                                            @foreach($group['items'] as $t)
                                                <div wire:key="grp-{{ md5($group['site']) }}-tp-{{ $t['id'] }}" class="h-full">
                                                    @include('livewire.admin.modulators.partials.ku-card', ['t' => $t])
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </section>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

    <div class="w-full bg-gradient-to-br from-white to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-lg shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700 mt-6">
        <div class="p-6 space-y-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>{{ __('modulators.recent_switch_log') }}</span>
                </h3>
                <span class="inline-flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                    <i class="fa-solid fa-earth-americas"></i>
                    {{ __('modulators.timezone_all_times_in') }}
                    <strong class="font-bold text-gray-700 dark:text-gray-200">{{ $timezone['gmt'] }} {{ $timezone['name'] }}</strong>
                </span>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 relative shadow-2xl rounded-lg overflow-hidden border-t {{ $areaColor('border-amber-300/50') }} dark:border-none">
            @if($recentEvents->isEmpty())
                <div class="p-8 text-center">
                    <p class="flex items-center text-gray-600 dark:text-gray-400 justify-center">
                        <i class="fa-solid fa-circle-info mr-2"></i>
                        {{ __('modulators.no_switch_events') }}
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-gray-600 dark:text-gray-400">
                        <thead class="text-xs dark:text-white uppercase dark:bg-gray-600">
                            <tr>
                                <th class="px-4 py-3 text-left"><i class="fa-solid fa-calendar mr-1.5 text-xs"></i>{{ __('modulators.when') }}</th>
                                <th class="px-4 py-3 text-left"><i class="fa-solid fa-satellite-dish mr-1.5 text-xs"></i>{{ __('modulators.transponder') }}</th>
                                <th class="px-4 py-3 text-left"><i class="fa-solid fa-right-left mr-1.5 text-xs"></i>{{ __('modulators.modulators') }}</th>
                                <th class="px-4 py-3 text-left"><i class="fa-solid fa-clock mr-1.5 text-xs"></i>{{ __('modulators.previous_duration') }}</th>
                                <th class="px-4 py-3 text-left"><i class="fa-solid fa-user mr-1.5 text-xs"></i>{{ __('modulators.user') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentEvents as $ev)
                                <tr wire:key="ev-{{ $ev->id }}" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-600 text-black dark:text-white">
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $ev->created_at?->format('Y-m-d H:i:s') }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">{{ $ev->transponder?->code ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center justify-center w-20 text-xs font-semibold px-2 py-0.5 rounded-xl bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200 truncate">
                                                {{ $ev->from_site ? __($ev->from_site) : '—' }}
                                            </span>
                                            <i class="fa-solid fa-arrow-right text-gray-400"></i>
                                            <span class="inline-flex items-center justify-center w-20 text-xs font-semibold px-2 py-0.5 rounded-xl bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-200 truncate">
                                                {{ __($ev->to_site) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-gray-700 dark:text-gray-300">
                                        {{ $ev->previous_duration_human ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap flex items-center space-x-3">
                                        <button class="flex text-sm rounded-full shadow-2xl cursor-default">
                                            <img class="h-6 w-6 rounded-full object-cover" src="{{ $ev->user?->profile_photo_url }}"
                                                alt="{{ $ev->user?->name }}" />
                                        </button>
                                        <span class="text-gray-900 dark:text-white truncate block max-w-[250px]">{{ $ev->user?->name ?? '—' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
