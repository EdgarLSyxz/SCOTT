<div wire:ignore.self>
    @php
        $areaIsDTH = Auth::user()?->area === 'DTH';
        $areaColor = fn(string $classes) => $areaIsDTH ? str_replace('amber', 'secondary', $classes) : str_replace('amber', 'primary', $classes);
    @endphp
    @if ($open && $activeDocumentName)
        <div class="fixed inset-0 z-[9999] overflow-y-auto"
            aria-labelledby="solar-modal-title-global" role="dialog" aria-modal="true"
            x-data="{
                focusToday() { const el = document.getElementById('solar-today-section'); if (el) { el.scrollIntoView({ behavior: 'smooth', block: 'start' }); } },
                query: '',
                matches(text, number) {
                    const q = this.query.trim().toLowerCase();
                    if (!q) return true;
                    return (text || '').toLowerCase().includes(q) || String(number || '').toLowerCase().includes(q);
                },
                countVisible() {
                    const total = document.querySelectorAll('[data-channel-row]').length;
                    if (!this.query.trim()) return null;
                    let visible = 0;
                    document.querySelectorAll('[data-channel-row]').forEach(el => {
                        if (el.offsetParent !== null) visible++;
                    });
                    return { visible, total };
                },
                searchCount: 0,
                updateSearchCount() { this.searchCount = this.countVisible(); }
            }"
            x-init="$nextTick(() => updateSearchCount()); $watch('query', () => updateSearchCount()); $wire.on('solar-focus-today', () => focusToday())"
            @keydown.escape.window="if(query.length){query=''}else{$wire.closeModal()}">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-900/60 dark:bg-black/70 transition-opacity" @click="$wire.closeModal()"></div>

                <div class="relative inline-block w-full max-w-4xl p-6 my-8 text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-2xl rounded-2xl"
                    @click.stop>
                    <div class="mb-6">
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-3 mb-1">
                                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-gradient-to-br {{ $areaIsDTH ? 'from-secondary-400 to-secondary-600' : 'from-primary-400 to-primary-600' }} text-white shadow-md {{ $areaIsDTH ? 'shadow-secondary-500/30' : 'shadow-primary-500/30' }} shrink-0">
                                        <i class="fa-solid fa-sun text-sm"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <h3 id="solar-modal-title-global" class="text-lg font-bold text-gray-900 dark:text-white leading-tight truncate">
                                            {{ __('Affected channels') }}
                                        </h3>
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">
                                            {{ __('Solar interference window') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <div class="relative group" x-data="{ focused: false }">
                                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-gray-400 dark:text-gray-500 {{ $areaColor('group-focus-within:text-amber-500') }}"></i>
                                    <x-input type="search"
                                            x-model="query"
                                            @focus="focused = true"
                                            @blur="focused = false"
                                            @keydown.escape="query = ''"
                                            placeholder="{{ __('Search by channel number or name...') }}"
                                            aria-label="{{ __('Search by channel number or name') }}"
                                            class="w-80 sm:w-96 pl-10 pr-9 py-2 text-xs rounded-xl border" />
                                    <button x-show="query"
                                        x-cloak
                                        type="button"
                                        @click="query = ''"
                                        aria-label="{{ __('Clear search') }}"
                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 inline-flex items-center justify-center w-5 h-5 rounded-full bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-500 dark:text-gray-300 transition-colors">
                                        <i class="fa-solid fa-xmark text-[10px]"></i>
                                    </button>
                                    <div x-show="query && searchCount"
                                        x-cloak
                                        x-transition.opacity
                                        class="absolute right-0 top-full mt-1.5 px-2 py-1 rounded-md text-[10px] font-medium bg-gray-900 dark:bg-gray-700 text-white shadow-lg whitespace-nowrap z-10">
                                        <span x-text="searchCount ? `${searchCount.visible}/${searchCount.total}` : ''"></span>
                                        {{ __('Matches') }}
                                    </div>
                                </div>
                                <button type="button" @click="$wire.closeModal()"
                                    aria-label="{{ __('Close') }}"
                                    class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 transition-colors">
                                    <i class="fa-solid fa-xmark text-sm"></i>
                                </button>
                            </div>
                        </div>

                        @if ($windowStart && $windowEnd && $totalDays > 0)
                            @php
                                $startLabel = ucwords(\Carbon\Carbon::parse($windowStart)->translatedFormat('d M Y'), ' ');
                                $endLabel = ucwords(\Carbon\Carbon::parse($windowEnd)->translatedFormat('d M Y'), ' ');
                                $statusStyles = [
                                    'upcoming' => [
                                        'gradient' => 'from-blue-500/10 via-blue-400/5 to-transparent',
                                        'border' => 'border-blue-200/70 dark:border-blue-800/40',
                                        'bar' => 'bg-gradient-to-r from-blue-500 to-cyan-400',
                                        'glow' => 'shadow-blue-500/20',
                                        'badge' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-200 ring-1 ring-blue-200/60 dark:ring-blue-700/40',
                                        'icon' => 'fa-clock',
                                        'label' => __('Upcoming'),
                                        'marker' => 'bg-blue-500',
                                    ],
                                    'active' => [
                                        'gradient' => $areaColor('from-amber-50 to-amber-100/40 dark:from-amber-900/30 dark:to-amber-900/10'),
                                        'border' => $areaColor('border-amber-400 dark:border-amber-500'),
                                        'bar' => $areaIsDTH
                                            ? 'bg-gradient-to-r from-secondary-500 via-secondary-400 to-secondary-500'
                                            : 'bg-gradient-to-r from-primary-500 via-primary-400 to-primary-500',
                                        'glow' => $areaColor('shadow-amber-500/30'),
                                        'badge' => $areaIsDTH
                                            ? 'bg-secondary-500 hover:bg-secondary-600 text-white ring-1 ring-secondary-600/30 shadow-sm shadow-secondary-500/30'
                                            : 'bg-primary-500 hover:bg-primary-600 text-white ring-1 ring-primary-600/30 shadow-sm shadow-primary-500/30',
                                        'icon' => 'fa-bolt',
                                        'label' => __('In progress'),
                                        'marker' => $areaIsDTH ? 'bg-secondary-300 dark:bg-secondary-200' : 'bg-primary-300 dark:bg-primary-200',
                                        'markerDot' => $areaIsDTH ? 'bg-secondary-200 dark:bg-secondary-300' : 'bg-primary-200 dark:bg-primary-300',
                                        'pulse' => true,
                                    ],
                                    'finished' => [
                                        'gradient' => 'from-emerald-500/10 via-emerald-400/5 to-transparent',
                                        'border' => 'border-emerald-200/70 dark:border-emerald-800/40',
                                        'bar' => 'bg-gradient-to-r from-emerald-500 to-teal-400',
                                        'glow' => 'shadow-emerald-500/20',
                                        'badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-200 ring-1 ring-emerald-200/60 dark:ring-emerald-700/40',
                                        'icon' => 'fa-circle-check',
                                        'label' => __('Finished'),
                                        'marker' => 'bg-emerald-500',
                                    ],
                                ];
                                $current = $statusStyles[$progressStatus] ?? $statusStyles['upcoming'];
                                $markerPercent = $progressStatus === 'finished' ? 100 : ($progressStatus === 'upcoming' ? 0 : $progressPercent);
                            @endphp
                            <div class="relative overflow-hidden rounded-2xl border {{ $current['border'] }} bg-gradient-to-br {{ $current['gradient'] }} {{ $progressStatus === 'active' ? 'shadow-lg' : 'dark:bg-gray-900/40 shadow-sm hover:shadow-md' }} transition-shadow duration-300"
                                aria-label="{{ __('Solar interference progress') }}">
                                <div class="px-5 pt-4 pb-4">
                                    <div class="flex items-center justify-between gap-3 mb-3">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold {{ $current['badge'] }} shrink-0">
                                                <i class="fa-solid {{ $current['icon'] }} text-[10px]"></i>
                                                {{ $current['label'] }}
                                            </span>
                                            <span class="text-[11px] text-gray-500 dark:text-gray-300 inline-flex items-center gap-1.5 min-w-0 truncate">
                                                <i class="fa-regular fa-calendar text-gray-400 dark:text-gray-400 shrink-0"></i>
                                                <span class="truncate">{{ $startLabel }}</span>
                                                <i class="fa-solid fa-arrow-right text-[8px] text-gray-400 dark:text-gray-400 shrink-0"></i>
                                                <span class="truncate">{{ $endLabel }}</span>
                                            </span>
                                        </div>
                                        <div class="flex items-baseline gap-1 shrink-0">
                                            <span class="text-xl font-bold tracking-tight text-gray-900 dark:text-white tabular-nums">
                                                {{ $progressPercent }}<span class="text-sm text-gray-500 dark:text-gray-400 font-semibold">%</span>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="relative w-full h-3 bg-gray-200/80 dark:bg-gray-700/80 rounded-full overflow-visible shadow-inner"
                                        role="progressbar"
                                        aria-valuemin="0"
                                        aria-valuemax="100"
                                        aria-valuenow="{{ $progressPercent }}">
                                        <div class="absolute inset-y-0 left-0 {{ $current['bar'] }} transition-all duration-700 ease-out rounded-full shadow-sm {{ $current['glow'] }}"
                                            style="width: {{ $progressPercent }}%"></div>
                                        @if ($progressStatus === 'active')
                                            <div class="absolute top-1/2 -translate-y-1/2 translate-x-[-50%] w-4 h-4 rounded-full {{ $current['markerDot'] ?? '' }} ring-2 ring-white {{ $areaColor('dark:ring-primary-400') }} shadow-md {{ $current['marker'] }} transition-all duration-700 ease-out"
                                                style="left: {{ $progressPercent }}%;"></div>
                                        @endif
                                    </div>

                                    <div class="flex items-center justify-between mt-3 text-[11px]">
                                        <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                                            <span class="font-bold tabular-nums text-gray-900 dark:text-white">{{ $elapsedDays }}</span>
                                            <span class="text-gray-400 dark:text-gray-500">/</span>
                                            <span class="font-semibold tabular-nums text-gray-700 dark:text-gray-200">{{ $totalDays }}</span>
                                            <span class="text-gray-500 dark:text-gray-400">{{ __('Days') }}</span>
                                        </span>
                                        <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                                            @if ($progressStatus === 'upcoming')
                                                <i class="fa-regular fa-hourglass text-blue-500"></i>
                                                {{ trans_choice(':count Day to start|:count Days to start', $remainingDays, ['count' => $remainingDays]) }}
                                            @elseif ($progressStatus === 'finished')
                                                <i class="fa-solid fa-flag-checkered text-emerald-500"></i>
                                                {{ __('Interference window has ended.') }}
                                            @else
                                                <i class="fa-regular fa-hourglass-half {{ $areaColor('text-amber-500') }}"></i>
                                                {{ trans_choice(':count Day remaining|:count Days remaining', $remainingDays, ['count' => $remainingDays]) }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if ($loading && $loadedAt === 0)
                        <div class="py-12 flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                            <i class="fa-solid fa-spinner fa-spin text-2xl {{ $areaColor('text-amber-500') }} mb-2"></i>
                            <p class="text-sm">{{ __('Loading affected channels...') }}</p>
                        </div>
                    @else
                        @if ($todayHasData)
                            <div id="solar-today-section" class="mb-5 rounded-xl border-2 {{ $areaColor('border-amber-400 dark:border-amber-500') }} bg-gradient-to-br {{ $areaColor('from-amber-50 to-amber-100/40 dark:from-amber-900/30 dark:to-amber-900/10') }} shadow-lg overflow-hidden">
                                <div class="px-4 py-3 {{ $areaColor('bg-amber-500/10 dark:bg-amber-500/20') }} border-b {{ $areaColor('border-amber-400 dark:border-primary-400') }} flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-sun {{ $areaColor('text-amber-500') }} animate-pulse"></i>
                                        <h4 class="font-bold {{ $areaColor('text-amber-900 dark:text-amber-100') }}">
                                            {{ __('Today') }}
                                            <span class="font-normal {{ $areaColor('text-amber-700 dark:text-amber-300') }}">·
                                                {{ ucfirst(\Carbon\Carbon::parse($today)->translatedFormat('l, d \\d\\e F')) }}
                                            </span>
                                        </h4>
                                    </div>
                                </div>

                                <ul class="divide-y {{ $areaColor('divide-amber-200/60 dark:divide-primary-800/30') }}">
                                    @foreach ($todayEvents as $event)
                                        @php
                                            $resolved = $event['channel'];
                                            $timeRange = $event['start_time'].($event['end_time'] ? ' – '.$event['end_time'] : '');
                                            $duration = !empty($event['duration_seconds']) ? gmdate('H:i:s', (int) $event['duration_seconds']) : null;
                                            $logoUrl = !empty($resolved['image_url']) ? asset('storage/' . $resolved['image_url']) : null;
                                            $section = $event['section'] ?? null;
                                        @endphp
                                        <li x-show="matches('{{ addslashes($resolved['name'] ?? '') }}', '{{ $resolved['number'] ?? '' }}')"
                                            data-channel-row
                                            class="px-4 py-4 flex items-center gap-3 {{ $areaColor('hover:bg-amber-100/40 dark:hover:bg-amber-900/20') }} transition">
                                            @if ($logoUrl)
                                                <img src="{{ $logoUrl }}"
                                                    alt="{{ $resolved['name'] }}"
                                                    class="w-10 h-10 object-contain shrink-0"
                                                    onerror="this.style.display='none'">
                                            @else
                                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-md bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-300 shrink-0">
                                                    <i class="fa-solid fa-tv text-sm"></i>
                                                </span>
                                            @endif
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-bold text-gray-900 dark:text-white truncate">
                                                    <span class="text-rose-600 dark:text-rose-300 mr-1">{{ $resolved['number'] }}</span>
                                                    {{ $resolved['name'] }}
                                                </p>
                                                <p class="text-xs {{ $areaColor('text-amber-800 dark:text-amber-300') }} truncate">
                                                    <i class="fa-solid {{ $section === 'satellite' ? 'fa-satellite-dish' : ($section === 'teleport' ? 'fa-tower-broadcast' : 'fa-map') }} mr-1"></i>
                                                    {{ $event['label'] }}
                                                </p>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <p class="text-sm font-mono font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                                    {{ $timeRange }}
                                                </p>
                                                @if ($duration)
                                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                                        <i class="fa-regular fa-clock mr-1"></i>
                                                        {{ __('Duration') }}: {{ $duration }}
                                                    </p>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <div class="mb-5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/30 px-4 py-4 flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400 shrink-0">
                                    <i class="fa-solid fa-moon"></i>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ __('No affected channels today') }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ __('Showing the upcoming and recent events for context.') }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        <div class="pr-2 -mr-2">
                            <h5 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2 px-1 flex items-center gap-2">
                                <i class="fa-solid fa-calendar-week"></i>
                                {{ __('Upcoming & recent events') }}
                            </h5>

                            @php
                                $displayedDates = $orderedDates ?? [];
                            @endphp

                            @forelse ($displayedDates as $date)
                                @if ($date === $today)
                                    @continue
                                @endif
                                @php
                                    $dayEvents = $groupedEvents[$date] ?? [];
                                    if (empty($dayEvents)) {
                                        continue;
                                    }
                                    $carbonDate = \Carbon\Carbon::parse($date);
                                    $isPast = $carbonDate->isPast();
                                    $uniqueChannels = collect($dayEvents)->pluck('channel_name')->filter()->unique()->count();
                                @endphp
                                <div class="mb-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/40 dark:bg-gray-900/30 overflow-hidden">
                                    <div class="flex items-center justify-between px-4 py-2 border-b border-gray-200 dark:border-gray-700 bg-white/50 dark:bg-gray-800/40">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-calendar-day text-gray-500 dark:text-gray-400 text-sm"></i>
                                            <h6 class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                                {{ ucfirst($carbonDate->translatedFormat('l, d \\d\\e F')) }}
                                            </h6>
                                            @if ($isPast)
                                                <span class="inline-flex items-center px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-wide rounded bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                                    {{ __('Past') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-wide rounded bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300">
                                                    {{ __('Upcoming') }}
                                                </span>
                                            @endif
                                        </div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ trans_choice(':count Channel|:count Channels', $uniqueChannels, ['count' => $uniqueChannels]) }}
                                        </span>
                                    </div>

                                    <ul class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                        @foreach ($dayEvents as $event)
                                            @php
                                                $resolved = $event['channel'];
                                                $timeRange = $event['start_time'].($event['end_time'] ? ' – '.$event['end_time'] : '');
                                                $duration = !empty($event['duration_seconds']) ? gmdate('H:i:s', (int) $event['duration_seconds']) : null;
                                                $logoUrl = !empty($resolved['image_url']) ? asset('storage/' . $resolved['image_url']) : null;
                                                $section = $event['section'] ?? null;
                                            @endphp
                                            <li x-show="matches('{{ addslashes($resolved['name'] ?? '') }}', '{{ $resolved['number'] ?? '' }}')"
                                                data-channel-row
                                                class="px-4 py-2.5 flex items-center gap-3 hover:bg-gray-100/50 dark:hover:bg-gray-800/40 transition">
                                                @if ($logoUrl)
                                                    <img src="{{ $logoUrl }}"
                                                        alt="{{ $resolved['name'] }}"
                                                        class="w-9 h-9 object-contain shrink-0"
                                                        onerror="this.style.display='none'">
                                                @else
                                                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-md bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-300 shrink-0">
                                                        <i class="fa-solid fa-tv text-xs"></i>
                                                    </span>
                                                @endif
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                        <span class="text-rose-600 dark:text-rose-300 mr-1">{{ $resolved['number'] }}</span>
                                                        {{ $resolved['name'] }}
                                                    </p>
                                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">
                                                        <i class="fa-solid {{ $section === 'satellite' ? 'fa-satellite-dish' : ($section === 'teleport' ? 'fa-tower-broadcast' : 'fa-map') }} mr-1"></i>
                                                        {{ $event['label'] }}
                                                    </p>
                                                </div>
                                                <div class="text-right shrink-0">
                                                    <p class="text-xs font-mono text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                                        {{ $timeRange }}
                                                    </p>
                                                    @if ($duration)
                                                        <p class="text-[10px] text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                                            <i class="fa-regular fa-hourglass mr-1"></i>
                                                            {{ $duration }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @empty
                                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                    <i class="fa-solid fa-circle-info mr-2"></i>
                                    {{ __('No upcoming events recorded.') }}
                                </div>
                            @endforelse
                        </div>
                    @endif

                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>
                            <i class="fa-solid fa-circle-info mr-1"></i>
                            {{ __('Times shown in UTC-6 (Ciudad de México).') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
