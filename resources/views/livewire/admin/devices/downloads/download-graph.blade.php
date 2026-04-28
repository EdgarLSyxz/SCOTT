<div>

@php
    $area = Auth::user()?->area;
    $selectRingClass = $area === 'OTT'
        ? 'focus-within:ring-2 focus-within:ring-primary-400 dark:focus-within:ring-primary-600'
        : ($area === 'DTH'
            ? 'focus-within:ring-2 focus-within:ring-secondary-400 dark:focus-within:ring-secondary-600'
            : 'focus-within:ring-2 focus-within:ring-primary-400 dark:focus-within:ring-primary-600');
@endphp

<style>
    @media (max-width: 640px) {
        #downloads-chart-panel #chart-loading:not(.hidden) {
            position: absolute !important;
            inset: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            z-index: 60 !important;
            background: transparent !important;
        }

        #downloads-chart-panel #chart-loading:not(.hidden) ~ * {
            display: none !important;
        }
    }
</style>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
        <div id="downloads-chart-panel" class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow p-3 md:p-4 flex flex-col min-h-[360px] md:min-h-[420px] relative">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-2 md:mb-4 gap-2 md:gap-4">
                <div class="flex-1 items-center min-w-0">
                    <h2 class="text-base md:text-lg font-semibold truncate text-gray-900 dark:text-gray-100">
                        <i class="fa-solid fa-download mr-2" aria-hidden="true"></i>
                        {{ __('Downloads - Yearly view') }}
                    </h2>
                    <p class="text-sm mt-1 truncate text-gray-600 dark:text-gray-400 mb-2 md:mb-0">
                        {{ __('Monthly downloads for the selected year.') }}
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 w-full md:w-auto">
                    @php
                        $spinnerFillClass = $area === 'OTT' ? 'fill-primary-600' : ($area === 'DTH' ? 'fill-secondary-600' : 'fill-blue-600');
                    @endphp
                    <div id="chart-loading" class="text-sm text-gray-500 hidden" aria-hidden="true">
                        <div role="status" class="flex items-center gap-2 mr-0.5">
                            <svg aria-hidden="true" class="w-6 h-6 text-gray-200 animate-spin dark:text-gray-600 {{ $spinnerFillClass }}" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                                <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                            </svg>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 w-full sm:w-auto">
                        @if(isset($devices) && $devices->count())
                            <div class="inline-flex items-center rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-700 px-1 py-0.5 shadow-sm {{ $selectRingClass }} w-full sm:w-auto">
                                <i class="fa-solid fa-hard-drive text-gray-400 text-[10px] mx-2" aria-hidden="true"></i>
                                <select id="select-device" wire:model="selectedDevice" wire:change="$set('selectedDevice', $event.target.value)" class="appearance-none bg-transparent border-0 pl-1 pr-3 text-[10px] font-medium text-gray-700 dark:bg-gray-700 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white focus:outline-none cursor-pointer w-full sm:w-[145px] focus:ring-0 focus:border-0 truncate leading-tight" aria-label="{{ __('Select device') }}">
                                    <option value="">{{ __('All devices') }}</option>
                                    @foreach($devices as $d)
                                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <label for="select-year" class="sr-only">{{ __('Year') }}</label>
                        <div class="inline-flex items-center rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-700 px-1 py-0.5 shadow-sm {{ $selectRingClass }} w-full sm:w-auto mt-2 md:mt-0">
                            <i class="fa-solid fa-calendar text-gray-400 text-[10px] mx-2" aria-hidden="true"></i>
                            <select id="select-year" wire:model="selectedYear" wire:change="$set('selectedYear', $event.target.value)" class="appearance-none bg-transparent border-0 pl-1 pr-3 text-[10px] font-medium text-gray-700 dark:bg-gray-700 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white focus:outline-none cursor-pointer w-full sm:w-[115px] focus:ring-0 focus:border-0 truncate leading-tight" aria-label="{{ __('Select year') }}">
                                <option value="all">{{ __('All years') }}</option>
                                @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        <label for="select-month" class="sr-only">{{ __('Month') }}</label>
                        <div class="inline-flex items-center rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-700 px-1 py-0.5 shadow-sm {{ $selectRingClass }} w-full sm:w-auto mt-2 md:mt-0">
                            <i class="fa-solid fa-calendar-days text-gray-400 text-[10px] mx-2" aria-hidden="true"></i>
                            <select id="select-month" class="appearance-none bg-transparent border-0 pl-1 pr-3 text-[10px] font-medium text-gray-700 dark:bg-gray-700 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white focus:outline-none cursor-pointer w-full sm:w-[120px] focus:ring-0 focus:border-0 truncate leading-tight" aria-label="{{ __('Select month') }}">
                                <option value="">{{ __('All months') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full flex-1 relative mt-2 md:mt-0" wire:ignore>
                <canvas id="monthlyDownloadsChart" class="w-full h-full block" role="img" aria-label="{{ __('Monthly downloads chart') }}"></canvas>
            </div>
        </div>

        <div class="flex flex-col space-y-3 md:space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-3 md:p-4 flex-1 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                        <i class="fa-solid fa-chart-pie"></i>{{ __(key: 'Year:') }}<span style="font-weight:600;">{{ (string) $selectedYear === 'all' ? __('All years') : ($selectedYear ?? date('Y')) }}</span>
                    </h3>

                    <div class="ml-0 sm:ml-2 mt-0 sm:mt-0 flex items-center gap-2">
                        <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" class="tooltip-wrapper relative inline-block">
                            <button type="button" id="exportPdfBtn" onclick="exportChartsPdf(event)"
                                class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 focus:outline-none">
                                <i class="fa-solid fa-file-pdf" aria-hidden="true"></i>
                            </button>
                            <div x-show="open" x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-75"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-75"
                                 class="tooltip origin-center absolute left-1/2 transform -translate-x-1/2 mt-2 w-max bg-gray-800 text-white dark:bg-white dark:text-gray-800 text-xs rounded px-2 py-1 shadow-lg z-50"
                                 role="tooltip" data-dynamic-tooltip="true">
                                {{ __('Export PDF') }}
                            </div>
                        </div>

                        <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" class="tooltip-wrapper relative inline-block">
                            <button type="button" id="exportCsvBtn" onclick="exportCsv()"
                                class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 focus:outline-none">
                                <i class="fa-solid fa-file-csv" aria-hidden="true"></i>
                            </button>
                            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 scale-75" x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-75"
                                class="tooltip origin-center absolute left-1/2 transform -translate-x-1/2 mt-2 w-max bg-gray-800 text-white dark:bg-white dark:text-gray-800 text-xs rounded px-2 py-1 shadow-lg z-50"
                                role="tooltip" data-dynamic-tooltip="true">
                                {{ __('Export CSV') }}
                            </div>
                        </div>

                        <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" class="tooltip-wrapper relative inline-block">
                            <button type="button" id="emailExportBtn" onclick="exportMonthlyByEmail(event)"
                                class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 focus:outline-none">
                                <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                            </button>
                            <div x-show="open" x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-75"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-75"
                                 class="tooltip origin-center absolute left-1/2 transform -translate-x-1/2 mt-2 w-max bg-gray-800 text-white dark:bg-white dark:text-gray-800 text-xs rounded px-2 py-1 shadow-lg z-50"
                                 role="tooltip" data-dynamic-tooltip="true">
                                {{ __('Send by email') }}
                            </div>
                        </div>

                        <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" class="tooltip-wrapper relative inline-block">
                            <button type="button" id="emailExportAllYearsBtn" onclick="exportMonthlyByEmail(event, true)"
                                class="inline-flex items-center px-3 py-1.5 bg-purple-600 text-white rounded-lg text-sm hover:bg-purple-700 focus:outline-none">
                                <i class="fa-solid fa-calendar" aria-hidden="true"></i>
                            </button>
                            <div x-show="open" x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-75"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-75"
                                 class="tooltip origin-center absolute left-1/2 transform -translate-x-1/2 mt-2 w-max bg-gray-800 text-white dark:bg-white dark:text-gray-800 text-xs rounded px-2 py-1 shadow-lg z-50"
                                 role="tooltip" data-dynamic-tooltip="true">
                                {{ __('Send all years by email') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3 flex items-end justify-center" wire:ignore>
                    <div class="w-[200px] h-[200px] sm:w-[220px] sm:h-[220px]">
                        <canvas id="pieDownloadsChart" class="w-full h-full" aria-label="{{ __('Downloads distribution chart') }}"></canvas>
                    </div>
                </div>

                @php
                    $devProtocol = $kpis['device_protocol_percent'] ?? null;
                @endphp

                @if(!empty($devProtocol) && is_array($devProtocol))
                    <div class="flex items-center justify-center gap-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-[#8B5CF6] text-white border border-gray-100 shadow">
                            <i class="fa-solid fa-tv mr-1.5"></i>
                            HLS: {{ $devProtocol['HLS'] }}%
                        </span>

                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-[#00A7C4] text-white border border-gray-100 shadow">
                            <i class="fa-solid fa-computer mr-1.5"></i>
                            DASH: {{ $devProtocol['DASH'] }}%
                        </span>
                    </div>
                @endif

                <div class="col-span-1 sm:col-span-2 md:col-span-3">
                    <div class="flex flex-col sm:flex-row items-center sm:items-center sm:justify-between gap-2">
                        <div class="flex-shrink-0 text-[11px] sm:text-xs text-gray-600 dark:text-gray-400 mr-2">{{ __('Top device this year:') }}</div>

                        @if(!empty($kpis['top_device']))
                            @php $topDeviceImage = $kpis['top_device']['image'] ?? null; @endphp
                            <div class="flex items-center gap-2 min-w-0">
                                @if($topDeviceImage)
                                    <img src="{{ $topDeviceImage }}" alt="{{ $kpis['top_device']['name'] ?? '' }}" class="w-5 h-5 sm:w-5 sm:h-5 object-contain object-center rounded flex-shrink-0" />
                                @else
                                    <div class="w-5 h-5 sm:w-5 sm:h-5 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid fa-hard-drive text-gray-400 text-[11px]"></i>
                                    </div>
                                @endif

                                <div class="min-w-0">
                                    <div class="text-[12px] sm:text-sm font-semibold text-gray-900 dark:text-gray-100 truncate" title="{{ $kpis['top_device']['name'] ?? '' }}">{{ $kpis['top_device']['name'] ?? '—' }}</div>
                                </div>
                            </div>
                        @else
                            <div class="text-xs sm:text-sm font-semibold text-gray-900 dark:text-gray-100">—</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-2.5 sm:p-3 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 text-center">
                <div class="py-1 sm:py-2">
                    <h4 class="text-[11px] sm:text-xs text-gray-600 dark:text-gray-400">{{ __('Top month') }}</h4>
                    <div class="text-base sm:text-lg font-bold mt-0.5 text-gray-900 dark:text-gray-100" id="kpi-top">{{ __($kpis['top']['month'] ?? '—') }}</div>
                </div>
                <div class="py-1 sm:py-2">
                    <h4 class="text-[11px] sm:text-xs text-gray-600 dark:text-gray-400">{{ __('Average per month') }}</h4>
                    <div class="text-base sm:text-lg font-bold mt-0.5 text-gray-900 dark:text-gray-100" id="kpi-average">{{ $kpis['average'] ?? 0 }}</div>
                </div>
                <div class="py-1 sm:py-2">
                    <h4 class="text-[11px] sm:text-xs text-gray-500">{{ __('Total per year') }}</h4>
                    <div class="text-base sm:text-lg font-bold mt-0.5 text-gray-900 dark:text-gray-100" id="kpi-total">{{ $kpis['total'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $__monthLabelsTranslated = [
        1 => __('January'),
        2 => __('February'),
        3 => __('March'),
        4 => __('April'),
        5 => __('May'),
        6 => __('June'),
        7 => __('July'),
        8 => __('August'),
        9 => __('September'),
        10 => __('October'),
        11 => __('November'),
        12 => __('December'),
    ];
@endphp

@once
    @push('js')
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script src="/js/downloads-graph.js"></script>
            <script>
                const downloadsPdfUrl = "{{ route('admin.downloads.history.pdf') }}";
                const downloadsDataUrl = "{{ route('admin.downloads.history.data') }}";
                const downloadsEmailUrl = "{{ route('admin.downloads.history.email') }}";
                const getMonthsUrl = "{{ route('admin.downloads.months') }}";
                const monthLabelsTranslated = @json($__monthLabelsTranslated, JSON_UNESCAPED_UNICODE);

                if (window && typeof Livewire !== 'undefined') {
                    try {
                        Livewire.on('downloads-updated', function(payload) {
                            window.__downloadsLatest = payload;
                            console.log('cached downloads-updated payload', payload);
                        });
                    } catch (e) {}
                }

                let monthsLoadAbort = null;
                let monthsLoadTimeout = null;

                async function loadMonthsForYear() {
                    const yearSelect = document.querySelector('#select-year');
                    const monthSelect = document.querySelector('#select-month');
                    if (!yearSelect || !monthSelect) return;
                    const selectedYear = String(yearSelect.value || '').toLowerCase();

                    if (monthsLoadAbort) {
                        monthsLoadAbort.abort();
                    }

                    if (monthsLoadTimeout) {
                        clearTimeout(monthsLoadTimeout);
                    }

                    monthsLoadTimeout = setTimeout(async () => {
                        try {
                            monthSelect.innerHTML = '<option value="">{{ __('All months') }}</option>';

                            if (selectedYear === 'all') {
                                for (let m = 1; m <= 12; m++) {
                                    const option = document.createElement('option');
                                    option.value = String(m);
                                    option.textContent = (monthLabelsTranslated[m] ?? String(m));
                                    monthSelect.appendChild(option);
                                }
                                return;
                            }

                                const params = new URLSearchParams();
                                params.append('year', yearSelect.value);
                                const deviceSelect = document.querySelector('#select-device');
                                if (deviceSelect && deviceSelect.value) {
                                    params.append('device_id', deviceSelect.value);
                                }

                            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                            const headers = tokenMeta ? { 'X-CSRF-TOKEN': tokenMeta.getAttribute('content') } : {};

                            monthsLoadAbort = new AbortController();

                            const resp = await fetch(getMonthsUrl + '?' + params.toString(), {
                                headers: { 'X-Requested-With': 'XMLHttpRequest', ...(headers || {}) },
                                signal: monthsLoadAbort.signal
                            });

                            if (resp.ok) {
                                const data = await resp.json();
                                if (data.months && Array.isArray(data.months)) {
                                    data.months.forEach(month => {
                                        const option = document.createElement('option');
                                        option.value = month.value;
                                        option.textContent = (monthLabelsTranslated[month.value] ?? month.label ?? month.value);
                                        monthSelect.appendChild(option);
                                    });
                                }
                                console.log('Loaded months:', data.months);
                            } else {
                                console.warn('Failed to load months');
                            }
                        } catch (err) {
                            if (err.name !== 'AbortError') {
                                console.error('Error loading months:', err);
                            }
                        }
                    }, 300);
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', () => {
                        loadMonthsForYear();
                        attachYearChangeListener();
                        attachDeviceChangeListener();
                    });
                } else {
                    loadMonthsForYear();
                    attachYearChangeListener();
                    attachDeviceChangeListener();
                }

                function attachYearChangeListener() {
                    const yearSelect = document.querySelector('#select-year');
                    if (yearSelect) {
                        yearSelect.removeEventListener('change', loadMonthsForYear);
                        yearSelect.addEventListener('change', loadMonthsForYear);
                    }
                }

                function attachDeviceChangeListener() {
                    const deviceSelect = document.querySelector('#select-device');
                    if (deviceSelect) {
                        deviceSelect.removeEventListener('change', loadMonthsForYear);
                        deviceSelect.addEventListener('change', loadMonthsForYear);
                    }
                }

                if (typeof Livewire !== 'undefined') {
                    try {
                        Livewire.hook('morph.updated', () => {
                            attachYearChangeListener();
                        });
                    } catch (e) {}
                }

                const yearSelect = document.querySelector('#select-year');
                if (yearSelect) {
                    yearSelect.addEventListener('change', loadMonthsForYear);
                }
                const deviceSelect = document.querySelector('#select-device');
                if (deviceSelect) {
                    deviceSelect.addEventListener('change', loadMonthsForYear);
                }

                function buildYearlyAggregates(preData) {
                    const rows = Array.isArray(preData?.download_rows) ? preData.download_rows : [];
                    let years = Array.isArray(preData?.years)
                        ? preData.years.map(y => parseInt(y, 10)).filter(y => Number.isFinite(y) && y > 0)
                        : [];

                    if (!years.length) {
                        years = Array.from(new Set(rows
                            .map(r => parseInt(r?.year, 10))
                            .filter(y => Number.isFinite(y) && y > 0)));
                    }

                    years.sort((a, b) => b - a);

                    const byYear = {};
                    years.forEach((y) => {
                        byYear[y] = {
                            monthly: Array(12).fill(0),
                            protocols: { HLS: 0, DASH: 0 },
                        };
                    });

                    rows.forEach((row) => {
                        const y = parseInt(row?.year, 10);
                        if (!Number.isFinite(y) || y <= 0) return;
                        if (!byYear[y]) {
                            byYear[y] = {
                                monthly: Array(12).fill(0),
                                protocols: { HLS: 0, DASH: 0 },
                            };
                            years.push(y);
                        }

                        const month = parseInt(row?.month, 10);
                        const count = parseInt(row?.count, 10) || 0;
                        const protocol = String(row?.protocol || '').trim().toUpperCase();

                        if (month >= 1 && month <= 12) {
                            byYear[y].monthly[month - 1] += count;
                        }

                        if (protocol === 'HLS' || protocol === 'DASH') {
                            byYear[y].protocols[protocol] += count;
                        }
                    });

                    years = Array.from(new Set(years)).sort((a, b) => b - a);
                    years = years.slice(0, 3);

                    const filteredByYear = {};
                    years.forEach((y) => {
                        filteredByYear[y] = byYear[y] || {
                            monthly: Array(12).fill(0),
                            protocols: { HLS: 0, DASH: 0 },
                        };
                    });

                    return { years, byYear: filteredByYear };
                }

                async function renderChartDataUrl(config, width = 1200, height = 520) {
                    if (typeof Chart === 'undefined') {
                        return null;
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;

                    const ctx = canvas.getContext('2d');
                    let chart = null;

                    try {
                        chart = new Chart(ctx, config);
                        await new Promise((resolve) => setTimeout(resolve, 60));
                        return canvas.toDataURL('image/png');
                    } catch (error) {
                        console.warn('Could not render chart for annual export:', error);
                        return null;
                    } finally {
                        if (chart) {
                            try { chart.destroy(); } catch (_) {}
                        }
                    }
                }

                async function buildGlobalYearBarChart(preData) {
                    const { years, byYear } = buildYearlyAggregates(preData);
                    const sortedYears = [...years].sort((a, b) => a - b);
                    const totals = sortedYears.map(y => {
                        const entry = byYear[y] || { monthly: Array(12).fill(0) };
                        return entry.monthly.reduce((sum, v) => sum + v, 0);
                    });
                    const grandTotal = totals.reduce((s, t) => s + t, 0);
                    const pcts = totals.map(t => grandTotal > 0 ? ((t / grandTotal) * 100).toFixed(1) + '%' : '0%');
                    const colors = ['rgba(99,102,241,0.85)', 'rgba(59,130,246,0.85)', 'rgba(6,182,212,0.85)'];
                    const borderColors = ['rgb(99,102,241)', 'rgb(59,130,246)', 'rgb(6,182,212)'];
                    const config = {
                        type: 'bar',
                        data: {
                            labels: sortedYears.map(String),
                            datasets: [{
                                label: 'Downloads',
                                data: totals,
                                backgroundColor: sortedYears.map((_, i) => colors[i % colors.length]),
                                borderColor: sortedYears.map((_, i) => borderColors[i % borderColors.length]),
                                borderWidth: 2,
                            }],
                        },
                        options: {
                            animation: false,
                            responsive: false,
                            scales: {
                                y: { beginAtZero: true, ticks: { font: { size: 14 } } },
                                x: { ticks: { font: { size: 18, weight: 'bold' } } },
                            },
                            plugins: {
                                legend: { display: false },
                                title: { display: false },
                            },
                        },
                    };
                    return await renderChartDataUrl(config, 1280, 520);
                }

                async function buildAllYearsCharts(preData) {
                    const { years, byYear } = buildYearlyAggregates(preData);
                    const chartsByYear = {};
                    const monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

                    for (const year of years) {
                        const entry = byYear[year] || { monthly: Array(12).fill(0), protocols: { HLS: 0, DASH: 0 } };

                        const monthlyConfig = {
                            type: 'bar',
                            data: {
                                labels: monthLabels,
                                datasets: [{
                                    label: `Downloads ${year}`,
                                    data: entry.monthly,
                                    backgroundColor: [
                                        'rgba(6, 182, 212, 0.85)',
                                        'rgba(59, 130, 246, 0.85)',
                                        'rgba(99, 102, 241, 0.85)',
                                        'rgba(139, 92, 246, 0.85)',
                                        'rgba(168, 85, 247, 0.85)',
                                        'rgba(236, 72, 153, 0.85)',
                                        'rgba(239, 68, 68, 0.85)',
                                        'rgba(249, 115, 22, 0.85)',
                                        'rgba(245, 158, 11, 0.85)',
                                        'rgba(34, 197, 94, 0.85)',
                                        'rgba(16, 185, 129, 0.85)',
                                        'rgba(14, 165, 233, 0.85)'
                                    ],
                                    borderColor: [
                                        'rgb(6, 182, 212)',
                                        'rgb(59, 130, 246)',
                                        'rgb(99, 102, 241)',
                                        'rgb(139, 92, 246)',
                                        'rgb(168, 85, 247)',
                                        'rgb(236, 72, 153)',
                                        'rgb(239, 68, 68)',
                                        'rgb(249, 115, 22)',
                                        'rgb(245, 158, 11)',
                                        'rgb(34, 197, 94)',
                                        'rgb(16, 185, 129)',
                                        'rgb(14, 165, 233)'
                                    ],
                                    borderWidth: 1,
                                }],
                            },
                            options: {
                                animation: false,
                                responsive: false,
                                scales: { y: { beginAtZero: true } },
                                plugins: { legend: { display: false } },
                            },
                        };

                        const protocolConfig = {
                            type: 'doughnut',
                            data: {
                                labels: ['HLS', 'DASH'],
                                datasets: [{
                                    data: [entry.protocols.HLS || 0, entry.protocols.DASH || 0],
                                    backgroundColor: ['#06B6D4', '#8B5CF6'],
                                }],
                            },
                            options: {
                                animation: false,
                                responsive: false,
                                plugins: { legend: { position: 'bottom' } },
                            },
                        };

                        const monthly = await renderChartDataUrl(monthlyConfig, 1280, 520);
                        const pie = await renderChartDataUrl(protocolConfig, 760, 520);

                        chartsByYear[String(year)] = { monthly, pie };
                    }

                    return chartsByYear;
                }

                async function exportChartsPdf(e, allYears = false) {
                    e && e.preventDefault();

                    const yearSelect = document.querySelector('#select-year');
                    const monthSelect = document.querySelector('#select-month');
                    const selectedAllYears = !!yearSelect && String(yearSelect.value || '').toLowerCase() === 'all';
                    const useAllYears = allYears || selectedAllYears;

                    const deviceSelect = document.querySelector('#select-device');
                    const params = new URLSearchParams();
                    if (useAllYears) {
                        params.append('all_years', '1');
                        params.append('year', 'all');
                    } else {
                        if (yearSelect) params.append('year', yearSelect.value);
                        if (monthSelect && monthSelect.value) params.append('month', monthSelect.value);
                    }
                    if (deviceSelect && deviceSelect.value) params.append('device_id', deviceSelect.value);

                    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                    const headers = tokenMeta ? { 'X-CSRF-TOKEN': tokenMeta.getAttribute('content') } : {};

                    console.log('Checking for cached Livewire payload...');

                    let preData = null;
                    try {
                        if (!useAllYears && window.__downloadsLatest && window.__downloadsLatest.year == (yearSelect?.value || '') && ( (!deviceSelect || !deviceSelect.value) || window.__downloadsLatest.device_id == (deviceSelect?.value || null) )) {
                            preData = window.__downloadsLatest;
                            console.log('Using cached Livewire payload for PDF export', preData);
                        }
                    } catch (e) { console.warn(e); }

                    if (!preData) {
                        console.log('Fetching data from: ' + downloadsDataUrl + '?' + params.toString());
                        try {
                            const resp = await fetch(downloadsDataUrl + '?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest', ...(headers || {}) } });
                            console.log('Data endpoint response status:', resp.status);
                            if (!resp.ok) throw new Error('Data fetch failed with status ' + resp.status);
                            preData = await resp.json();
                            console.log('Prefetched data received:', preData);
                        } catch (err) {
                            console.error('Error fetching historical data:', err);
                            const errMsg1 = (err && err.message) ? String(err.message) : '';
                            Swal.fire({ icon: 'error', title: @json(__('Error')), text: errMsg1 ? @json(__('Error fetching historical data from server:')) + ' ' + errMsg1 : @json(__('Error fetching historical data from server')) });
                            return;
                        }
                    }

                    if (!preData || typeof preData !== 'object') {
                        console.error('Invalid data response (not object):', preData);
                        Swal.fire({ icon: 'error', title: @json(__('Error')), text: @json(__('Invalid data response from server')) });
                        return;
                    }

                    if (!preData.download_rows || !Array.isArray(preData.download_rows)) {
                        console.warn('Warning: download_rows not present or not array', preData);
                    }

                    let monthlyData = null;
                    let pieData = null;
                    let chartsByYear = null;
                    if (!useAllYears) {
                        const monthlyCanvas = document.getElementById('monthlyDownloadsChart');
                        const pieCanvas = document.getElementById('pieDownloadsChart');
                        if (!monthlyCanvas || !pieCanvas) {
                            Swal.fire({ icon: 'error', title: @json(__('Error')), text: @json(__('Charts not ready')) });
                            return;
                        }

                        monthlyData = monthlyCanvas.toDataURL('image/png');
                        pieData = pieCanvas.toDataURL('image/png');
                    } else {
                        chartsByYear = await buildAllYearsCharts(preData);
                    }

                    let globalBarChart = null;
                    if (useAllYears) {
                        globalBarChart = await buildGlobalYearBarChart(preData);
                    }

                    const fd = new FormData();
                    if (monthlyData) fd.append('charts[monthly]', monthlyData);
                    if (pieData) fd.append('charts[pie]', pieData);
                    if (chartsByYear && typeof chartsByYear === 'object') {
                        Object.entries(chartsByYear).forEach(([yearKey, images]) => {
                            if (images?.monthly) {
                                fd.append(`charts_by_year[${yearKey}][monthly]`, images.monthly);
                            }
                            if (images?.pie) {
                                fd.append(`charts_by_year[${yearKey}][pie]`, images.pie);
                            }
                        });
                    }
                    if (globalBarChart) fd.append('chart_global_bar', globalBarChart);
                    if (useAllYears) {
                        fd.append('all_years', '1');
                        fd.append('year', 'all');
                    } else {
                        if (yearSelect) fd.append('year', yearSelect.value);
                        if (monthSelect && monthSelect.value) fd.append('month', monthSelect.value);
                    }
                    if (deviceSelect) fd.append('device_id', deviceSelect.value);
                    fd.append('data', JSON.stringify(preData));

                    console.log('Posting PDF with data, fields:', {
                        has_year: !!yearSelect?.value,
                        has_month: !!monthSelect?.value,
                        has_device_id: !!deviceSelect?.value,
                        all_years: useAllYears,
                        data_size: JSON.stringify(preData).length,
                        download_rows_count: preData.download_rows?.length || 0,
                    });

                    try {
                        const resp = await fetch(downloadsPdfUrl, { method: 'POST', body: fd, headers });
                        console.log('PDF endpoint response status:', resp.status);
                        if (!resp.ok) throw new Error('PDF generation failed with status ' + resp.status);
                        const blob = await resp.blob();
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        (function(){
                            const now = new Date();
                            const pad = (n) => String(n).padStart(2, '0');
                            const filename = `Download History - ${now.getFullYear()}${pad(now.getMonth()+1)}${pad(now.getDate())} ${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}.pdf`;
                            a.download = filename;
                        })();
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        URL.revokeObjectURL(url);
                        console.log('PDF downloaded successfully');
                    } catch (err) {
                        console.error('Error generating PDF:', err);
                        const errMsg2 = (err && err.message) ? String(err.message) : '';
                        Swal.fire({ icon: 'error', title: @json(__('Error')), text: errMsg2 ? @json(__('Error generating PDF:')) + ' ' + errMsg2 : @json(__('Error generating PDF')) });
                    }
                }

                async function exportMonthlyByEmail(e, allYears = false) {
                    e && e.preventDefault();

                    const yearSelect = document.querySelector('#select-year');
                    const monthSelect = document.querySelector('#select-month');
                    const selectedAllYears = !!yearSelect && String(yearSelect.value || '').toLowerCase() === 'all';
                    const useAllYears = allYears || selectedAllYears;
                    const deviceSelect = document.querySelector('#select-device');
                    const params = new URLSearchParams();
                    if (useAllYears) {
                        params.append('all_years', '1');
                        params.append('year', 'all');
                    } else {
                        if (yearSelect) params.append('year', yearSelect.value);
                        if (monthSelect && monthSelect.value) params.append('month', monthSelect.value);
                    }
                    if (deviceSelect && deviceSelect.value) params.append('device_id', deviceSelect.value);

                    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                    const headers = tokenMeta ? { 'X-CSRF-TOKEN': tokenMeta.getAttribute('content') } : {};

                    let preData = null;
                    try {
                        if (!useAllYears && window.__downloadsLatest && window.__downloadsLatest.year == (yearSelect?.value || '') && ( (!deviceSelect || !deviceSelect.value) || window.__downloadsLatest.device_id == (deviceSelect?.value || null) )) {
                            preData = window.__downloadsLatest;
                        }
                    } catch (e) { }

                    if (!preData) {
                        try {
                            const resp = await fetch(downloadsDataUrl + '?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest', ...(headers || {}) } });
                            if (!resp.ok) throw new Error('Data fetch failed with status ' + resp.status);
                            preData = await resp.json();
                        } catch (err) {
                            console.error('Error fetching data for email export:', err);
                            const errMsg3 = (err && err.message) ? String(err.message) : '';
                            Swal.fire({ icon: 'error', title: @json(__('Error')), text: errMsg3 ? @json(__('Error fetching data from server:')) + ' ' + errMsg3 : @json(__('Error fetching data from server')) });
                            return;
                        }
                    }

                    const monthlyCanvas = document.getElementById('monthlyDownloadsChart');
                    const pieCanvas = document.getElementById('pieDownloadsChart');
                    let monthlyData = null;
                    let pieData = null;
                    let chartsByYear = null;
                    if (!useAllYears && monthlyCanvas) {
                        try {
                            monthlyData = monthlyCanvas.toDataURL('image/png');
                        } catch (e) { console.warn('Could not capture monthly chart'); }
                    }
                    if (!useAllYears && pieCanvas) {
                        try {
                            pieData = pieCanvas.toDataURL('image/png');
                        } catch (e) { console.warn('Could not capture pie chart'); }
                    }
                    if (useAllYears) {
                        chartsByYear = await buildAllYearsCharts(preData);
                    }

                    let globalBarChartEmail = null;
                    if (useAllYears) {
                        globalBarChartEmail = await buildGlobalYearBarChart(preData);
                    }

                    const fd = new FormData();
                    fd.append('data', JSON.stringify(preData));
                    if (monthlyData) fd.append('charts[monthly]', monthlyData);
                    if (pieData) fd.append('charts[pie]', pieData);
                    if (chartsByYear && typeof chartsByYear === 'object') {
                        Object.entries(chartsByYear).forEach(([yearKey, images]) => {
                            if (images?.monthly) {
                                fd.append(`charts_by_year[${yearKey}][monthly]`, images.monthly);
                            }
                            if (images?.pie) {
                                fd.append(`charts_by_year[${yearKey}][pie]`, images.pie);
                            }
                        });
                    }
                    if (globalBarChartEmail) fd.append('chart_global_bar', globalBarChartEmail);
                    if (useAllYears) {
                        fd.append('all_years', '1');
                        fd.append('year', 'all');
                    } else {
                        if (yearSelect) fd.append('year', yearSelect.value);
                        if (monthSelect && monthSelect.value) fd.append('month', monthSelect.value);
                    }
                    if (deviceSelect && deviceSelect.value) fd.append('device_id', deviceSelect.value);

                    try {
                        const resp = await fetch(downloadsEmailUrl, { method: 'POST', body: fd, headers });
                        if (!resp.ok) {
                            const txt = await resp.text().catch(()=>null);
                            throw new Error('Email export failed: ' + (txt || resp.status));
                        }
                        const json = await resp.json().catch(()=>null);
                        Swal.fire({ icon: 'success', title: @json(__('Well done!')), text: (json && json.message) ? json.message : @json(__('Email sent successfully.')) });
                    } catch (err) {
                        console.error('Error sending email export:', err);
                        const errMsg4 = (err && err.message) ? String(err.message) : '';
                        Swal.fire({ icon: 'error', title: @json(__('Error')), text: errMsg4 ? @json(__('Error sending email export:')) + ' ' + errMsg4 : @json(__('Error sending email export')) });
                    }
                }

                function exportCsv() {
                    try {
                        const input = document.getElementById('downloads-datepicker-range');
                            let start = '';
                            let end = '';
                            if (input && input.value) {
                        const parts = input.value.split(' to ');
                            start = parts[0] ? parts[0].trim() : '';
                            end = parts[1] ? parts[1].trim() : start;
                        }
                        const base = '{{ route("admin.downloads.history.csv") }}';
                        const url = new URL(base, window.location.origin);
                        if (start) url.searchParams.set('start', start);
                        if (end) url.searchParams.set('end', end);
                        window.open(url.toString(), '_blank');
                    } catch (e) {
                        console.error('Export CSV failed', e);
                        alert('Could not start CSV export.');
                    }
                }
                // Dynamic tooltip positioning: keep tooltips within viewport
                function positionTooltipWithinViewport(parent, tooltip) {
                    if (!parent || !tooltip) return;
                    const pad = 8;
                    // Temporarily make visible to measure
                    const prevVisibility = tooltip.style.visibility;
                    const prevDisplay = tooltip.style.display;
                    tooltip.style.visibility = 'hidden';
                    tooltip.style.display = 'block';

                    const parentRect = parent.getBoundingClientRect();
                    const tooltipRect = tooltip.getBoundingClientRect();
                    const viewportWidth = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);

                    // center tooltip above parent by default
                    let desiredLeft = parentRect.left + (parentRect.width / 2) - (tooltipRect.width / 2);
                    const minLeft = pad;
                    const maxLeft = viewportWidth - pad - tooltipRect.width;
                    if (desiredLeft < minLeft) desiredLeft = minLeft;
                    if (desiredLeft > maxLeft) desiredLeft = maxLeft;

                    const leftRelative = desiredLeft - parentRect.left;
                    tooltip.style.left = `${leftRelative}px`;
                    tooltip.style.right = 'auto';
                    tooltip.style.transform = 'none';

                    // restore visibility/display
                    tooltip.style.display = prevDisplay || '';
                    tooltip.style.visibility = prevVisibility || '';
                }

                function bindDynamicTooltips() {
                    const wrappers = document.querySelectorAll('.tooltip-wrapper');
                    wrappers.forEach(wrap => {
                        const tooltip = wrap.querySelector('[role="tooltip"]');
                        if (!tooltip) return;
                        function onShow() {
                            // allow Alpine to toggle visibility first
                            setTimeout(() => positionTooltipWithinViewport(wrap, tooltip), 10);
                        }
                        wrap.addEventListener('mouseenter', onShow);
                        wrap.addEventListener('focusin', onShow);
                    });
                    window.addEventListener('resize', () => {
                        document.querySelectorAll('.tooltip-wrapper').forEach(wrap => {
                            const tooltip = wrap.querySelector('[role="tooltip"]');
                            if (tooltip && tooltip.offsetParent !== null) {
                                positionTooltipWithinViewport(wrap, tooltip);
                            }
                        });
                    });
                }

                document.addEventListener('DOMContentLoaded', bindDynamicTooltips);
            </script>
    @endpush
@endonce

@php
    $__initialDownloadsData = $monthlyData ?? array_fill(0, 12, 0);
    $__initialDownloadsKpis = $kpis ?? ['total' => 0, 'average' => 0, 'top' => ['month' => '—', 'value' => 0]];
@endphp

<script type="application/json" id="initialDownloadsData">{!! json_encode($__initialDownloadsData) !!}</script>
<script type="application/json" id="initialDownloadsKpis">{!! json_encode($__initialDownloadsKpis) !!}</script>
