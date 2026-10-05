<div wire:ignore.self>
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
                }
            }"
            x-init="$wire.on('solar-focus-today', () => focusToday())"
            @keydown.escape.window="if(query.length){query=''}else{$wire.closeModal()}">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-900/60 dark:bg-black/70 transition-opacity" @click="$wire.closeModal()"></div>

                <div class="relative inline-block w-full max-w-4xl p-6 my-8 text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-2xl rounded-2xl"
                    @click.stop>
                    <div class="flex items-center justify-between mb-5 gap-4">
                        <div class="min-w-0">
                            <h3 id="solar-modal-title-global" class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <i class="fa-solid fa-sun text-amber-500"></i>
                                {{ __('Affected channels') }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                <i class="fa-solid fa-file-pdf mr-1 text-red-500"></i>
                                {{ $activeDocumentName }}
                                · <i class="fa-solid fa-tv mr-1 text-rose-500"></i>
                                {{ number_format($totalChannels) }} {{ __(trans_choice('Channel|Channels', $totalChannels)) }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 dark:text-gray-500"></i>
                                <input type="search"
                                        x-model="query"
                                        placeholder="{{ __('Search channel or number...') }}"
                                        aria-label="{{ __('Search channel or number') }}"
                                        class="w-48 sm:w-64 pl-8 pr-8 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/40 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-amber-400/50 focus:border-amber-400" />
                                <button type="button"
                                        x-show="query.length"
                                        x-cloak
                                        @click="query=''"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                                    <i class="fa-solid fa-xmark text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    @if ($loading && $loadedAt === 0)
                        <div class="py-12 flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                            <i class="fa-solid fa-spinner fa-spin text-2xl text-amber-500 mb-2"></i>
                            <p class="text-sm">{{ __('Loading affected channels...') }}</p>
                        </div>
                    @else
                        @if ($todayHasData)
                            <div id="solar-today-section" class="mb-5 rounded-xl border-2 border-amber-400 dark:border-amber-500 bg-gradient-to-br from-amber-50 to-amber-100/40 dark:from-amber-900/30 dark:to-amber-900/10 shadow-lg overflow-hidden">
                                <div class="px-4 py-3 bg-amber-500/10 dark:bg-amber-500/20 border-b border-amber-300/40 dark:border-amber-700/40 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-sun text-amber-500 animate-pulse"></i>
                                        <h4 class="font-bold text-amber-900 dark:text-amber-100">
                                            {{ __('Today') }}
                                            <span class="font-normal text-amber-700 dark:text-amber-300">·
                                                {{ ucfirst(\Carbon\Carbon::parse($today)->translatedFormat('l, d \\d\\e F')) }}
                                            </span>
                                        </h4>
                                    </div>
                                </div>

                                <ul class="divide-y divide-amber-200/60 dark:divide-amber-800/30">
                                    @foreach ($todayEvents as $event)
                                        @php
                                            $resolved = $event['channel'];
                                            $timeRange = $event['start_time'].($event['end_time'] ? ' – '.$event['end_time'] : '');
                                            $duration = !empty($event['duration_seconds']) ? gmdate('H:i:s', (int) $event['duration_seconds']) : null;
                                            $logoUrl = !empty($resolved['image_url']) ? asset('storage/' . $resolved['image_url']) : null;
                                            $section = $event['section'] ?? null;
                                        @endphp
                                        <li x-show="matches('{{ addslashes($resolved['name'] ?? '') }}', '{{ $resolved['number'] ?? '' }}')"
                                            class="px-4 py-4 flex items-center gap-3 hover:bg-amber-100/40 dark:hover:bg-amber-900/20 transition">
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
                                                <p class="text-xs text-amber-800 dark:text-amber-300 truncate">
                                                    <i class="fa-solid {{ $section === 'satellite' ? 'fa-satellite' : ($section === 'teleport' ? 'fa-tower-broadcast' : 'fa-map') }} mr-1"></i>
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
                                                        <i class="fa-solid {{ $section === 'satellite' ? 'fa-satellite' : ($section === 'teleport' ? 'fa-tower-broadcast' : 'fa-map') }} mr-1"></i>
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

                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>
                            <i class="fa-solid fa-circle-info mr-1"></i>
                            {{ __('Times shown in UTC-6 (Ciudad de México).') }}
                        </span>
                        <button type="button" @click="$wire.closeModal()"
                            class="px-4 py-1.5 text-sm rounded-lg bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-medium">
                            {{ __('Close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
