<div class="bg-white dark:bg-gray-800 relative shadow-2xl rounded-lg overflow-hidden mb-6">
    <div class="flex flex-col gap-4 p-4 bg-white dark:bg-gray-800 md:flex-row md:items-center md:justify-between">
        <div class="w-full md:w-1/3">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white truncate leading-tight">
                <i class="fa-solid fa-folder mr-1.5 text-gray-600 dark:text-gray-300"></i>
                {{ __('History of monthly downloads') }}
            </h2>
        </div>
        <div
            class="w-full md:w-auto flex flex-col sm:flex-row sm:flex-wrap items-stretch lg:items-center justify-start md:justify-end gap-3">
            <script>
                (function() {
                    var id = 'flatpickr-theme';
                    var darkHref = 'https://npmcdn.com/flatpickr/dist/themes/dark.css';
                    var lightHref = 'https://npmcdn.com/flatpickr/dist/themes/default.css';

                    function applyFlatpickrTheme(theme) {
                        var href = theme === 'dark' ? darkHref : lightHref;
                        var link = document.getElementById(id);
                        if (!link) {
                            link = document.createElement('link');
                            link.id = id;
                            link.rel = 'stylesheet';
                            link.href = href;
                            document.head.appendChild(link);
                        } else {
                            link.href = href;
                        }
                    }

                    function resolveTheme() {
                        var stored = localStorage.getItem('color-theme');
                        if (stored) return stored;
                        return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
                    }

                    applyFlatpickrTheme(resolveTheme());

                    window.addEventListener('storage', function(e) {
                        if (e.key === 'color-theme') {
                            applyFlatpickrTheme(e.newValue || 'light');
                        }
                    });

                    window.addEventListener('color-theme-changed', function() {
                        applyFlatpickrTheme(resolveTheme());
                    });

                    var observer = new MutationObserver(function(mutations) {
                        for (var i = 0; i < mutations.length; i++) {
                            if (mutations[i].attributeName === 'class' || mutations[i].attributeName === 'data-theme') {
                                applyFlatpickrTheme(resolveTheme());
                                break;
                            }
                        }
                    });
                    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class', 'data-theme'] });
                })();
            </script>
            <div x-data="{
                start: @entangle('startDate').defer,
                end: @entangle('endDate').defer,
                flatpickrInstance: null
            }" x-init="
                flatpickrInstance = flatpickr($refs.input, {
                    mode: 'range',
                    dateFormat: 'Y-m-d',
                    defaultDate: (start && end) ? [start, end] : (start ? [start] : []),
                    maxDate: 'today',
                    onChange: function(selectedDates, dateStr) {
                        let [start, end] = dateStr.split(' to ');
                        if (!end) end = start;
                        $wire.set('startDate', start);
                        $wire.set('endDate', end);
                        $refs.input.value = end ? start + ' to ' + end : start;
                    },
                    onReady: function(selectedDates, dateStr, instance) {
                        if (start && !end) {
                            instance.input.value = start;
                        } else if (start && end) {
                            instance.input.value = start + ' to ' + end;
                        }
                    },
                    onClose: function(selectedDates, dateStr, instance) {
                        setTimeout(function() {
                            let [start, end] = instance.input.value.split(' to ');
                            if (!start) return;
                            if (!end) end = start;
                            instance.input.value = end ? start + ' to ' + end : start;
                        }, 10);
                    }
                })
            "
            x-on:clear-datepicker-range.window="
                flatpickrInstance.clear();
                $refs.input.value = '';
            "
            class="w-full sm:w-auto">
            <x-input id="downloads-datepicker-range" x-ref="input" type="text" autocomplete="off" placeholder="{{ __('Select a range time') }}"
                class="min-w-[16rem] w-full px-3 py-[9px] text-sm border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500 block dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" />
        </div>
            <button wire:click="resetFilters"
                class="w-full sm:w-auto flex items-center justify-center gap-2 py-2 px-4 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-100 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700">
                <i class="fa-solid fa-rotate-left"></i>
                {{ __('Reset table') }}
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full table-fixed text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs dark:text-white uppercase dark:bg-gray-600 shadow-2xl">
                <tr>
                    <th class="py-3 px-4 text-left w-[160px]">
                        <i class="fa-solid fa-calendar-days mr-1.5"></i>
                        {{ __('Month') }}
                    </th>
                    <th class="py-3 px-4 text-left w-[160px]">
                        <i class="fa-solid fa-calendar mr-1.5"></i>
                        {{ __('Year') }}
                    </th>
                    <th class="py-3 px-4 text-left w-[160px] truncate leading-tight">
                        <i class="fa-solid fa-download mr-1.5"></i>
                        {{ __('Total downloads') }}
                    </th>
                    <th scope="col" class="px-4 py-3 w-[80px]">
                        <span class="sr-only">
                            <i class="fa-solid fa-sliders-h mr-1"></i>
                            {{ __('Options') }}
                        </span>
                    </th>
                </tr>
            </thead>
            <tbody>
                @if ($aggregates->isEmpty())
                    <tr>
                        <td colspan="4" class="bg-white dark:bg-gray-800 text-center py-6 pb-3">
                            <div class="text-gray-500 dark:text-gray-300">
                                <i class="fa-solid fa-circle-info mr-1"></i>
                                {{ __('No monthly downloads reports found with the current filters.') }}
                            </div>
                        </td>
                    </tr>
                @else
                    @foreach ($aggregates as $agg)
                        <tr wire:click="showMonthDetails({{ $agg->year }}, {{ $agg->month }})" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-600 text-black dark:text-white cursor-pointer group">
                            <td class="py-3 px-4 font-bold whitespace-nowrap w-[160px]">
                                @php
                                    $monthName = __(\Carbon\Carbon::createFromFormat('!m', $agg->month)->locale(app()->getLocale())->translatedFormat('F'));
                                    $monthName = mb_strtoupper(mb_substr($monthName, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($monthName, 1, null, 'UTF-8');
                                @endphp
                                {{ $monthName }}
                            </td>
                            <td class="py-3 px-4 font-bold whitespace-nowrap w-[160px]">
                                {{ $agg->year }}
                            </td>
                            <td class="py-3 px-4 w-[160px]">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-3 py-1 rounded-full text-sm font-bold">
                                    <i class="fa-solid fa-arrow-down text-green-500 dark:text-green-400 mr-1"></i>
                                        {{ $agg->total_count }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 flex items-center justify-end align-middle">
                                <span class="flex items-center h-full justify-center" style="height: 100%; min-height: 24px;">
                                    <i class="fa-solid fa-chevron-right transition-colors text-gray-300 group-hover:text-gray-700 dark:text-gray-500 dark:group-hover:text-gray-400"
                                        style="vertical-align: middle; font-size: 1.1em; line-height: 1;"></i>
                                </span>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
    <div class="m-4">
        {{ $aggregates->links() }}
    </div>

    @if($showDetailsModal)
        @php
            $userArea = Auth::user()?->area;
            $isDTH = $userArea === 'DTH';
            $accentColor = $isDTH ? 'secondary' : 'primary';
        @endphp
        <div class="fixed inset-0 z-50 flex items-start md:items-center justify-center bg-black/50 overflow-y-auto p-2 sm:p-4 backdrop-blur-sm" @keydown.escape="$wire.call('$set', 'showDetailsModal', false)">
            <div class="w-full max-w-3xl bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden my-auto">
                <div class="sticky top-0 flex items-center justify-between p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/80">
                    <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                        <div class="{{ $isDTH ? 'bg-secondary-100 dark:bg-secondary-900/50' : 'bg-primary-100 dark:bg-primary-900/50' }} w-8 h-8 sm:w-10 sm:h-10 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="{{ $isDTH ? 'text-secondary-600 dark:text-secondary-400' : 'text-primary-600 dark:text-primary-400' }} fa-solid fa-calendar-check text-sm sm:text-base"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Details') }}</div>
                            <h2 class="text-base sm:text-xl font-bold text-gray-900 dark:text-white truncate">
                                @php
                                    $detailsMonthName = __(\Carbon\Carbon::createFromFormat('!m', $detailsMonth)->locale(app()->getLocale())->translatedFormat('F'));
                                    $detailsMonthName = mb_strtoupper(mb_substr($detailsMonthName, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($detailsMonthName, 1, null, 'UTF-8');
                                @endphp
                                {{ $detailsMonthName }} {{ $detailsYear }}
                            </h2>
                        </div>
                    </div>
                    <button type="button" wire:click="$set('showDetailsModal', false)" aria-label="{{ __('Close') }}"
                        class="text-gray-400 hover:text-gray-900 dark:hover:text-white bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg w-8 h-8 sm:w-10 sm:h-10 inline-flex justify-center items-center transition-all duration-200 hover:scale-110 flex-shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                    </button>
                </div>
                <div class="p-4 sm:p-6">
                    @if(empty($detailsGrouped) || count($detailsGrouped) === 0)
                        <div class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 mb-4">
                                <i class="fa-solid fa-inbox text-3xl text-gray-400 dark:text-gray-500"></i>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 font-medium">{{ __('No detailed downloads for this month.') }}</p>
                        </div>
                    @else
                        <div class="max-h-[70vh] overflow-y-auto space-y-2 sm:space-y-3 pr-2 sm:pr-4 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600 scrollbar-track-transparent">
                            @foreach($detailsGrouped as $ts => $items)
                                @php
                                    $dt = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $ts);
                                    $totalDownloads = array_sum(array_column($items, 'count'));
                                @endphp
                                <div x-data="{ open: false }" class="group border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden transition-all duration-200" :class="open ? '{{ $isDTH ? 'border-secondary-300 dark:border-secondary-500 shadow-lg bg-gradient-to-br from-secondary-50 to-white dark:from-gray-700/50 dark:to-gray-800' : 'border-primary-300 dark:border-primary-500 shadow-lg bg-gradient-to-br from-primary-50 to-white dark:from-gray-700/50 dark:to-gray-800' }}' : 'hover:border-{{ $accentColor }}-300 dark:hover:border-{{ $accentColor }}-500'">
                                    <button @click="open = !open" class="w-full flex items-center justify-between px-3 sm:px-5 py-3 sm:py-4 bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-150 cursor-pointer" :class="open ? '{{ $isDTH ? 'bg-gradient-to-r from-secondary-50 to-transparent dark:from-gray-700 dark:to-gray-800' : 'bg-gradient-to-r from-primary-50 to-transparent dark:from-gray-700 dark:to-gray-800' }}' : ''">
                                        <div class="flex items-center gap-2 sm:gap-4 flex-1 min-w-0">
                                            <div class="{{ $isDTH ? 'bg-gradient-to-br from-secondary-100 to-secondary-50 dark:from-secondary-900/60 dark:to-secondary-800/40 group-hover:from-secondary-200 group-hover:to-secondary-100 dark:group-hover:from-secondary-800 dark:group-hover:to-secondary-700' : 'bg-gradient-to-br from-primary-100 to-primary-50 dark:from-primary-900/60 dark:to-primary-800/40 group-hover:from-primary-200 group-hover:to-primary-100 dark:group-hover:from-primary-800 dark:group-hover:to-primary-700' }} w-8 h-8 sm:w-10 sm:h-10 rounded-lg flex items-center justify-center flex-shrink-0 transition-colors duration-150">
                                                <i class="{{ $isDTH ? 'text-secondary-600 dark:text-secondary-400' : 'text-primary-600 dark:text-primary-400' }} fa-solid fa-clock text-xs sm:text-sm"></i>
                                            </div>
                                            <div class="min-w-0 flex items-center gap-2 sm:gap-3">
                                                <div class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white group-hover:{{ $isDTH ? 'text-secondary-600' : 'text-primary-600' }} dark:group-hover:{{ $isDTH ? 'text-secondary-300' : 'text-primary-300' }} transition-colors">
                                                    {{ $dt->format('d/m/Y') }}
                                                </div>
                                                <span class="text-[10px] sm:text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-200 dark:bg-gray-600 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-lg">{{ $dt->format('H:i A') }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                                            <span class="{{ $isDTH ? 'bg-gradient-to-r from-secondary-100 to-secondary-50 dark:from-secondary-900/60 dark:to-secondary-800/40 text-secondary-700 dark:text-secondary-300 group-hover:from-secondary-200 group-hover:to-secondary-100 dark:group-hover:from-secondary-800 dark:group-hover:to-secondary-700' : 'bg-gradient-to-r from-primary-100 to-primary-50 dark:from-primary-900/60 dark:to-primary-800/40 text-primary-700 dark:text-primary-300 group-hover:from-primary-200 group-hover:to-primary-100 dark:group-hover:from-primary-800 dark:group-hover:to-primary-700' }} inline-flex items-center gap-1 sm:gap-2 px-2 sm:px-4 py-1 sm:py-2 rounded-lg text-[10px] sm:text-xs font-bold transition-all duration-150" :class="open ? '{{ $isDTH ? 'from-secondary-200 to-secondary-100 dark:from-secondary-800 dark:to-secondary-700' : 'from-primary-200 to-primary-100 dark:from-primary-800 dark:to-primary-700' }}' : ''">
                                                <i class="fa-solid fa-download text-[10px] sm:text-xs"></i>
                                                <span>{{ $totalDownloads }}</span>
                                            </span>
                                            <i x-bind:class="(open ? 'fa-solid fa-chevron-up rotate-180' : 'fa-solid fa-chevron-down') + ' text-gray-400 dark:text-gray-500 transform transition-all duration-200 text-xs sm:text-sm group-hover:text-gray-600 dark:group-hover:text-gray-300'" aria-hidden="true"></i>
                                        </div>
                                    </button>
                                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700/30 px-3 sm:px-5 py-3 sm:py-4 space-y-1.5 sm:space-y-2">
                                        @foreach($items as $it)
                                        <div class="flex items-center justify-between px-2 sm:px-4 py-2 sm:py-3 rounded-lg transition-all duration-150 group/item" :class="'{{ $isDTH ? 'hover:bg-secondary-50 dark:hover:bg-gray-600/60' : 'hover:bg-primary-50 dark:hover:bg-gray-600/60' }}'">
                                                <div class="flex items-center gap-2 sm:gap-3 flex-1 min-w-0">
                                                    @if(!empty($it['image']))
                                                        <img src="{{ $it['image'] }}" alt="{{ $it['device_name'] }}" class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg object-contain object-center flex-shrink-0 p-0.5 sm:p-1" />
                                                    @else
                                                        <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg bg-gradient-to-br from-gray-200 to-gray-100 dark:from-gray-600 dark:to-gray-700 flex items-center justify-center flex-shrink-0 ring-1 ring-gray-300 dark:ring-gray-600 group-hover/item:from-gray-300 group-hover/item:to-gray-200 transition-all">
                                                            <i class="fa-solid fa-mobile-screen text-[10px] sm:text-xs text-gray-600 dark:text-gray-300"></i>
                                                        </div>
                                                    @endif
                                                    <div class="min-w-0 flex-1">
                                                        <span class="text-xs sm:text-sm font-semibold text-gray-900 dark:text-white truncate block group-hover/item:{{ $isDTH ? 'text-secondary-600' : 'text-primary-600' }} dark:group-hover/item:{{ $isDTH ? 'text-secondary-400' : 'text-primary-400' }} transition-colors">{{ $it['device_name'] }}</span>
                                                    </div>
                                                </div>
                                                <span class="inline-flex items-center gap-1 sm:gap-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 sm:px-3.5 py-1 sm:py-1.5 rounded-lg text-[10px] sm:text-xs font-semibold ml-2 sm:ml-3 flex-shrink-0 transition-colors duration-150 hover:bg-gray-200 dark:hover:bg-gray-600">
                                                    <i class="fa-solid fa-arrow-down text-[10px] sm:text-xs"></i>
                                                    <span>{{ $it['count'] }}</span>
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
