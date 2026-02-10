<div class="bg-white dark:bg-gray-800 relative shadow-2xl rounded-lg overflow-hidden mb-6">
    <div class="flex flex-col gap-4 p-4 bg-white dark:bg-gray-800 md:flex-row md:items-center md:justify-between">
        <div class="w-full md:w-1/3">
            <form class="relative" onsubmit="event.preventDefault();">
                <label for="simple-search" class="sr-only">Search</label>
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                </div>
                <x-input type="text" id="simple-search" wire:model.live="search" autocomplete="off"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                    placeholder="{{ __('Search') }}" autofocus />
            </form>
        </div>
        <div
            class="w-full md:w-auto flex flex-col sm:flex-row sm:flex-wrap items-stretch lg:items-center justify-start md:justify-end gap-3">
            <script>
                (function () {
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

                    window.addEventListener('storage', function (e) {
                        if (e.key === 'color-theme') {
                            applyFlatpickrTheme(e.newValue || 'light');
                        }
                    });

                    window.addEventListener('color-theme-changed', function () {
                        applyFlatpickrTheme(resolveTheme());
                    });

                    var observer = new MutationObserver(function (mutations) {
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
                <x-input id="datepicker-range" x-ref="input" type="text" autocomplete="off" placeholder="{{ __('Select a range time') }}"
                    class="min-w-[16rem] w-full px-3 py-[9px] text-sm border border-gray-300 rounded-md dark:bg-gray-800 dark:border-gray-600 dark:text-white" />
            </div>
            <div class="relative w-full sm:w-auto">
                <button id="filterDropdownButton" data-dropdown-toggle="filterDropdown"
                    class="w-full sm:w-auto flex justify-center items-center gap-2 py-2 px-4 text-sm font-medium text-gray-900 bg-white border border-gray-300 rounded-md hover:bg-gray-100 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700">
                    <i class="fa-solid fa-filter"></i> {{ __('Filter') }}
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                </button>
                <div id="filterDropdown"
                    class="hidden absolute right-0 mt-2 w-72 p-4 bg-white border border-gray-200 rounded-lg shadow-lg dark:bg-gray-700 dark:border-gray-600 z-20">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" wire:click="toggleStatusFilter('Resolved')"
                            class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500">
                        <span class="text-sm text-gray-800 dark:text-gray-200">
                            {{ __('Show only resolved reports') }}
                        </span>
                    </label>
                    <label class="flex items-center space-x-2 mt-3">
                        <input type="checkbox" wire:click="toggleStatusFilter('Revision')"
                            class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500">
                        <span class="text-sm text-gray-800 dark:text-gray-200">
                            {{ __('Show only revision reports') }}
                        </span>
                    </label>
                </div>
            </div>
            <button wire:click="exportToExcel"
                class="w-full sm:w-auto flex items-center justify-center gap-2 py-2 px-4 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-100 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700">
                <i class="fa-solid fa-arrow-up-from-bracket"></i>
                {{ __('Export to Excel') }}
            </button>
            <button wire:click="resetFilters"
                class="w-full sm:w-auto flex items-center justify-center gap-2 py-2 px-4 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-100 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700">
                <i class="fa-solid fa-rotate-left"></i>
                {{ __('Reset table') }}
            </button>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs dark:text-white uppercase dark:bg-gray-600 shadow-2xl">
                <tr>
                    <th class="py-3 px-4 text-left whitespace-nowrap w-[100px]">
                        <i class="fa-solid fa-flag mr-1"></i>
                        {{ __('Folio') }}
                    </th>
                    <th class="py-3 px-4 text-left w-96">
                        <i class="fa-solid fa-folder mr-1"></i>
                        {{ __('Report') }}
                    </th>
                    @if(auth()->user() && auth()->user()->id === 1)
                        <th class="px-4 py-3 w-[120px] cursor-pointer inline-flex" wire:click="toggleAreaFilter">
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
                        <th class="px-4 py-3 w-[140px]">
                            <i class="fa-solid fa-building mr-1"></i>
                            <span class="text-gray-500 dark:text-white">{{ __('Area') }}</span>
                        </th>
                    @endif
                    <th class="py-3 px-4 text-left cursor-pointer w-[120px]"
                        wire:click="toggleTypeFilter">
                        <i class="fa-solid fa-list mr-1"></i>
                        <span class="text-gray-500 dark:text-white truncate">
                            @if ($typeFilter)
                                {{ __($typeFilter) }}
                            @else
                                {{ __('All Types') }}
                            @endif
                            <i class="ml-1 fa-solid fa-sort"></i>
                        </span>
                    </th>
                    <th class="py-3 px-4 text-left w-[120px] cursor-pointer"
                        wire:click="toggleStatusFilter">
                        <i class="fa-solid fa-circle-check mr-1"></i>
                        <span class="text-gray-500 dark:text-white truncate">
                            @if ($statusFilter)
                                {{ __($statusFilter) }}
                            @else
                                {{ __('All Status') }}
                            @endif
                            <i class="ml-1 fa-solid fa-sort"></i>
                        </span>
                    </th>
                    <th class="py-3 px-4 text-left cursor-pointer whitespace-nowrap w-[180px]"
                        wire:click="setOrder('created_at')">
                        <i class="fa-solid fa-calendar mr-1"></i>
                        {{ __('Datetime') }}
                        <button class="ml-1 text-gray-500 dark:text-white">
                            @if ($orderField === 'created_at')
                                <i
                                    class="fa-solid {{ $orderDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down' }}"></i>
                            @else
                                <i class="fa-solid fa-sort"></i>
                            @endif
                        </button>
                    </th>
                    <th class="py-3 px-4 text-left w-[350px] cursor-pointer"
                        wire:click="toggleUserFilter">
                        <i class="fa-solid fa-user mr-1"></i>
                        <span class="text-gray-500 dark:text-white truncate">
                            @if ($selectedUser)
                                {{ $selectedUser }}
                            @else
                                {{ __('All Users') }}
                            @endif
                            <i class="ml-1 fa-solid fa-sort"></i>
                        </span>
                    </th>
                    <th class="py-3 px-2 text-left">
                        <span class="sr-only">
                            <i class="fa-solid fa-sliders-h mr-2"></i>
                            {{ __('Options') }}
                        </span>
                    </th>
                </tr>
            </thead>
            <tbody>
                @if ($reports->isEmpty())
                    <tr>
                        <td colspan="8" class="bg-white dark:bg-gray-800 text-center py-6 pb-3">
                            <div class="text-gray-500 dark:text-gray-300">
                                <i class="fa-solid fa-circle-info mr-1"></i>
                                {{ __('No reports found with the current filters.') }}
                            </div>
                        </td>
                    </tr>
                @else
                    @foreach ($reports as $report)
                        <tr onclick="window.location='{{ route('reports.show', $report->id) }}'"
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-600 text-black dark:text-white cursor-pointer">
                            <td class="py-3 px-4 text-xs font-bold whitespace-nowrap">
                                {{ $report->id }}
                            </td>
                            <td title="{{ $report->category }}"
                                class="py-3 px-4 text-xs font-bold leading-tight truncate whitespace-nowrap overflow-hidden text-ellipsis sm:max-w-xs">
                                {{ __($report->category) }}
                            </td>
                            <td class="py-3 px-4 w-[120px]">
                                <span class="inline-flex items-center px-2 py-1 text-xs sm:text-sm font-medium rounded-full {{ $report->area === 'DTH'
                                    ? 'text-secondary-800 bg-secondary-200 dark:bg-secondary-800 dark:text-secondary-200'
                                    : ($report->area === 'OTT'
                                    ? 'text-primary-800 bg-primary-200 dark:bg-primary-800 dark:text-primary-200'
                                    : 'text-gray-800 bg-gray-200 dark:bg-gray-800 dark:text-gray-200') }}">
                                    @if($report->area === 'DTH')
                                        <i class="fa-solid fa-satellite-dish mr-1.5 pt-[1px]"></i>
                                    @elseif($report->area === 'OTT')
                                        <i class="fa-solid fa-cube mr-1.5 pt-[1px]"></i>
                                    @else
                                        <i class="fa-solid fa-layer-group mr-1.5 pt-[1px]"></i>
                                    @endif
                                    {{ $report->area ?? __('N/A') }}
                                </span>
                            </td>
                            <td class="py-3 px-4 w-[120px]">
                                @if ($report->type === 'Momentary')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs sm:text-sm font-medium rounded-full text-red-800 bg-red-200 dark:bg-red-800 dark:text-red-200 whitespace-nowrap">
                                        <i class="fa-solid fa-triangle-exclamation mr-1.5 pt-[1px]"></i>
                                        {{ __($report->type) }}
                                    </span>
                                @elseif ($report->type === 'Hourly')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs sm:text-sm font-medium rounded-full text-green-800 bg-green-200 dark:bg-green-800 dark:text-green-200 whitespace-nowrap">
                                        <i class="fa-solid fa-clock mr-1.5 pt-[1px]"></i>
                                        {{ __($report->type) }}
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs sm:text-sm font-medium rounded-full text-blue-800 bg-blue-200 dark:bg-blue-800 dark:text-blue-200 whitespace-nowrap">
                                        <i class="fa-solid fa-forward mr-1.5 pt-[1px]"></i>
                                        {{ __($report->type) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 w-[120px]">
                                @if ($report->type === 'Momentary')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs sm:text-sm font-medium rounded-full
                                            {{ $report->status === 'Resolved' ? 'text-green-800 bg-green-200 dark:bg-green-800 dark:text-green-200' : 'text-yellow-800 bg-yellow-200 dark:bg-yellow-800 dark:text-yellow-200' }} whitespace-nowrap">
                                        @if ($report->status === 'Resolved')
                                            <i class="fa-solid fa-circle-check mr-1.5 pt-[1px]"></i>
                                        @elseif ($report->status === 'Revision')
                                            <i class="fa-solid fa-magnifying-glass mr-1.5 pt-[1px]"></i>
                                        @endif
                                        {{ __($report->status) }}
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs sm:text-sm font-medium rounded-full text-gray-800 bg-gray-200 dark:bg-gray-700 dark:text-gray-200 whitespace-nowrap">
                                        <i class="fa-solid fa-folder mr-1.5 pt-[1px]"></i>
                                        {{ __($report->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs sm:text-sm font-medium text-blue-800 bg-blue-200 dark:bg-blue-800 dark:text-blue-200 rounded-full whitespace-nowrap">
                                    <i class="fa-solid fa-clock mr-1.5 pt-[1px]"></i>
                                    {{ $report->created_at->format('d/m/Y h:i A') }}
                                </span>
                            </td>
                            <td class="py-3 px-4 flex items-center w-[350px]">
                                <img src="{{ $report->reportedBy->profile_photo_url }}"
                                    alt="{{ $report->reportedBy->name }}"
                                    class="w-6 h-6 sm:w-8 sm:h-8 rounded-full mr-2 shadow-2xl">
                                <span class="text-xs truncate">
                                    {{ $report->reportedBy->name }}
                                </span>
                            </td>
                            <td class="px-2 sm:px-4 py-2 sm:py-3 text-end justify-end">
                                <i class="fa-solid fa-chevron-right text-gray-600 dark:text-gray-300"></i>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
    <div class="m-4">
        {{ $reports->links() }}
    </div>
</div>
