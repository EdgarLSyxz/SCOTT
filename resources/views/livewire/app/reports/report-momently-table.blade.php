<div class="w-full md:w-2/3 pt-6 md:pt-0 md:pl-6 lg:pt-0 lg:pl-6" wire:key="reports-table">
    <div class="bg-white dark:bg-gray-800 relative shadow-lg rounded-lg overflow-hidden">
        <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
            <div class="w-full md:w-1/2">
                <form class="flex items-center" onsubmit="event.preventDefault();">
                    <label for="simple-search" class="sr-only">Search</label>
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor"
                                viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <x-input type="text" id="simple-search" wire:model.live="search" autocomplete="off"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            placeholder="{{ __('Search') }}" required autofocus />
                    </div>
                </form>
            </div>

            @php
                $userArea = strtolower(trim(auth()->user()->area ?? ''));
                $historyBtnClasses = $userArea === 'dth'
                    ? 'text-white bg-secondary-700 hover:bg-secondary-800 focus:ring-4 focus:ring-secondary-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-secondary-600 dark:hover:bg-secondary-700 focus:outline-none dark:focus:ring-secondary-800'
                    : 'text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800';
            @endphp

            <div class="w-full md:w-auto flex items-center justify-end space-x-3">
                <a href="{{ route('reports.index') }}" class="{{ $historyBtnClasses }}">
                    <i class="fa-solid fa-folder mr-1"></i>
                    {{ __('Report history') }}
                </a>
            </div>
        </div>

        <div class="hidden">
            <span class="text-amber-800 bg-amber-200 dark:text-amber-200 dark:bg-amber-800"></span>
            <span class="bg-amber-500"></span>
            <span class="text-orange-800 bg-orange-200 dark:text-orange-200 dark:bg-orange-800"></span>
            <span class="bg-orange-500"></span>
            <span class="text-yellow-800 bg-yellow-200 dark:text-yellow-200 dark:bg-yellow-800"></span>
            <span class="bg-yellow-500"></span>
            <span class="text-red-800 bg-red-200 dark:text-red-200 dark:bg-red-800 animate-pulse"></span>
            <span class="bg-red-500 animate-pulse"></span>
            <span class="text-emerald-800 bg-emerald-200 dark:text-emerald-200 dark:bg-emerald-800"></span>
            <span class="bg-emerald-500"></span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-xs text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-600 dark:text-white">
                    <tr>
                        <th class="py-3 px-4 w-[240px] text-left whitespace-nowrap">
                            <i class="fa-solid fa-folder mr-1"></i> {{ __('Report') }}
                        </th>
                        <th class="py-3 px-4 w-[150px] text-left whitespace-nowrap">
                            <i class="fa-solid fa-layer-group mr-1"></i> {{ __('Channels') }}
                        </th>
                        @if(auth()->user() && auth()->user()->id === 1)
                            <th scope="col" class="px-4 py-3 w-[120px] text-left cursor-pointer inline-flex" wire:click="toggleAreaFilter">
                                <i class="fa-solid fa-building mr-1.5"></i>
                                <span class="text-gray-500 dark:text-white">
                                    @if ($areaFilter && $areaFilter !== 'all')
                                        {{ $areaFilter }}
                                    @else
                                        {{ __('All Areas') }}
                                    @endif
                                    <i class="ml-1 fa-solid fa-sort"></i>
                                </span>
                            </th>
                        @else
                            <th scope="col" class="px-4 py-3 w-[120px] text-left inline-flex">
                                <i class="fa-solid fa-building mr-1.5"></i>
                                <span class="text-gray-500 dark:text-white">{{ __('Area') }}</span>
                            </th>
                        @endif
                        <th class="py-3 px-4 w-[150px] text-left whitespace-nowrap">
                            <i class="fa-solid fa-user-group mr-1"></i> {{ __('Under review by') }}
                        </th>
                        <th wire:click="toggleOrder" class="py-3 px-4 w-[180px] text-left flex items-center cursor-pointer whitespace-nowrap">
                            <i class="fa-solid fa-calendar mr-1"></i> {{ __('It was reported ago') }}
                            <i class="fa-solid {{ $order === 'asc' ? 'fa-sort-up' : 'fa-sort-down' }} ml-1"></i>
                        </th>
                        @if($showSlaColumn)
                            <th class="py-3 px-4 w-[210px] text-left whitespace-nowrap">
                                <i class="fa-solid fa-traffic-light mr-1"></i> {{ __('SLA') }}
                            </th>
                        @endif
                        <th class="px-4 py-3 w-[80px] text-center">
                            <span class="sr-only">
                                <i class="fa-solid fa-sliders-h mr-1"></i> {{ __('Options') }}
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $report)
                        <tr wire:key="report-row-{{ $report->id }}-{{ (int) data_get($report, 'sla.last_activity_unix', 0) }}" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-600 text-black dark:text-white cursor-pointer"
                            wire:click="openReportDetails({{ $report->id }})">
                            <td
                                class="py-2 px-3 w-[240px] font-bold leading-tight truncate whitespace-nowrap overflow-hidden text-ellipsis">
                                {{ $report->category }}
                            </td>
                            <td class="px-3 py-2 w-[150px] whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    @foreach ($report->reportDetails->sortBy(fn($detail) => $detail->channel->number)->take(3) as $detail)
                                        <div class="relative w-8 h-8 overflow-hidden">
                                            <img class="w-full h-full object-contain object-center"
                                                src="{{ $detail->channel->image }}" alt="{{ $detail->channel->name }}"
                                                title="{{ $detail->channel->number }} {{ strtoupper($detail->channel->name) }} ({{ strtoupper($detail->channel->origin) }})">
                                        </div>
                                    @endforeach
                                    @if ($report->reportDetails->count() > 3)
                                        <span
                                            class="flex items-center justify-center w-8 h-8 text-xs font text-white bg-primary-700 rounded-full shadow-md">
                                            +{{ $report->reportDetails->count() - 3 }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-3 py-2 w-[120px] truncate whitespace-nowrap overflow-hidden">
                                <span
                                    class="inline-flex items-center px-2 py-1 text-sm font-medium rounded-full
                                        {{ $report->area === 'DTH'
                                    ? 'text-secondary-800 bg-secondary-200 dark:bg-secondary-800 dark:text-secondary-200'
                                    : ($report->area === 'OTT'
                                        ? 'text-primary-800 bg-primary-200 dark:bg-primary-800 dark:text-primary-200'
                                        : 'text-gray-800 bg-gray-200 dark:bg-gray-800 dark:text-gray-200') }}">
                                    @if($report->area === 'DTH')
                                        <i class="fa-solid fa-satellite-dish mr-1.5"></i>
                                    @elseif($report->area === 'OTT')
                                        <i class="fa-solid fa-cube mr-1.5"></i>
                                    @endif
                                    {{ $report->area ?? __('N/A') }}
                                </span>
                            </td>
                            <td class="py-2 px-3 w-[150px] whitespace-nowrap">
                                <span
                                    class="inline-flex items-center px-2 py-1 text-sm font-medium text-blue-800 bg-blue-200 dark:bg-blue-800 dark:text-blue-200 rounded-full">
                                    <i class="fa-solid fa-user mr-1.5"></i> {{ __($report->reviewed_by) }}
                                </span>
                            </td>
                            <td class="py-2 px-3 w-[180px] whitespace-nowrap">
                                <span
                                    class="inline-flex items-center px-2 py-1 text-sm font-medium text-blue-800 bg-blue-200 dark:bg-blue-800 dark:text-blue-200 rounded-full">
                                    <i class="fa-solid fa-clock mr-1.5 pt-[1px]"></i> {{ $report->formatted_date }}
                                </span>
                            </td>
                            @if($showSlaColumn)
                                <td class="py-2 px-3 w-[220px]">
                            @if(data_get($report, 'sla.enabled', false))
                                <div class="flex flex-col"
                                    x-data="slaLiveStatus({
                                        lastActivityUnix: {{ (int) ($report->sla['last_activity_unix'] ?? now()->timestamp) }},
                                        level1: {{ $report->sla['level_1_minutes'] ?? 30 }},
                                        level2: {{ $report->sla['level_2_minutes'] ?? 60 }},
                                        level3: {{ $report->sla['level_3_minutes'] ?? 90 }}
                                    })"
                                    x-init="init()">
                                    <span class="inline-flex self-start items-center whitespace-nowrap px-2 py-1 text-xs font-semibold rounded-full" :class="badge">
                                        <span class="inline-block flex-shrink-0 w-2 h-2 rounded-full mr-1.5" :class="dot"></span>
                                        <span x-text="statusLabel"></span>
                                    </span>
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 whitespace-nowrap">
                                        <i class="fa-regular fa-clock mr-0.5"></i>
                                        <span x-text="elapsed"></span>
                                    </span>
                                </div>
                                    @else
                                        <span class="text-[11px] text-gray-400 dark:text-gray-500">-</span>
                                    @endif
                                </td>
                            @endif
                            <td class="py-2 px-3 w-[80px] text-center whitespace-nowrap">
                                <i class="fa-solid fa-chevron-right text-gray-600 dark:text-gray-300"></i>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $showSlaColumn ? 7 : 6 }}" class="py-4 pt-10 text-center bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-300">
                                <i class="fa-solid fa-circle-info mr-1"></i>
                                {{ __('There are no reports available at this time.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $reports->links() }}
        </div>
    </div>
    @if ($showModal && $selectedReport)
        <div
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-start md:items-center overflow-y-auto">
            @if ($selectedReport)
                <div class="flex flex-col md:flex-row gap-0 md:gap-4 w-full md:w-auto max-h-[90vh] md:max-h-[90vh]">
                    @livewire('app.reports.report-detail-modal', ['reporteId' => $selectedReport->id], 'detail-' . $selectedReport->id)
                    @livewire('app.reports.report-comments-modal', ['reportId' => $selectedReport->id], 'comments-' . $selectedReport->id)
                </div>
            @endif
        </div>
    @endif
</div>

<script>
    function downloadM3U(url, number, name) {
        const content = url + "\n";
        let cleanName = (number ? number + '_' : '') + (name ? name : 'canal');
        cleanName = cleanName.replace(/[^a-zA-Z0-9-_]/g, '_');
        const filename = cleanName + '.m3u';
        const blob = new Blob([content], { type: "audio/x-mpegurl" });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        setTimeout(() => {
            URL.revokeObjectURL(a.href);
            document.body.removeChild(a);
        }, 100);
    }

    const SLA_TRANSLATIONS = {
        just_now: @json(__('Updated just now')),
        min_one: @json(__('Updated :count min ago', ['count' => ':count'])),
        min_other: @json(__('Updated :count min ago', ['count' => ':count'])),
        hour_one: @json(__('Updated :count hour ago', ['count' => ':count'])),
        hour_other: @json(__('Updated :count hours ago', ['count' => ':count'])),
        day_one: @json(__('Updated :count d ago', ['count' => ':count'])),
        day_other: @json(__('Updated :count days ago', ['count' => ':count'])),
        sla_normal: @json(__('Normal follow-up')),
        sla_attention: @json(__('It requires attention')),
        sla_delayed: @json(__('Delayed follow-up')),
    };

    function slaLiveStatus({ lastActivityUnix, level1, level2, level3 }) {
        return {
            badge: '',
            dot: '',
            statusLabel: '',
            elapsed: '',
            timer: null,
            init() {
                this.tick();
                this.timer = setInterval(() => this.tick(), 60000);
            },
            tick() {
                const now = Math.floor(Date.now() / 1000);
                const diff = Math.max(0, now - Number(lastActivityUnix || now));
                const mins = Math.floor(diff / 60);
                const hours = Math.floor(mins / 60);
                const days = Math.floor(hours / 24);

                if (mins >= level3) {
                    this.badge = 'text-red-800 bg-red-200 dark:text-red-200 dark:bg-red-800 animate-pulse';
                    this.dot = 'bg-red-500 animate-pulse';
                    this.statusLabel = SLA_TRANSLATIONS.sla_delayed;
                } else if (mins >= level2) {
                    this.badge = 'text-yellow-800 bg-yellow-200 dark:text-yellow-200 dark:bg-yellow-800';
                    this.dot = 'bg-yellow-500';
                    this.statusLabel = SLA_TRANSLATIONS.sla_attention;
                } else {
                    this.badge = 'text-emerald-800 bg-emerald-200 dark:text-emerald-200 dark:bg-emerald-800';
                    this.dot = 'bg-emerald-500';
                    this.statusLabel = SLA_TRANSLATIONS.sla_normal;
                }

                if (mins < 1) {
                    this.elapsed = SLA_TRANSLATIONS.just_now;
                } else if (mins < 60) {
                    this.elapsed = (mins === 1 ? SLA_TRANSLATIONS.min_one : SLA_TRANSLATIONS.min_other).replace(':count', mins);
                } else if (hours < 24) {
                    this.elapsed = (hours === 1 ? SLA_TRANSLATIONS.hour_one : SLA_TRANSLATIONS.hour_other).replace(':count', hours);
                } else {
                    this.elapsed = (days === 1 ? SLA_TRANSLATIONS.day_one : SLA_TRANSLATIONS.day_other).replace(':count', days);
                }
            }
        };
    }
</script>
