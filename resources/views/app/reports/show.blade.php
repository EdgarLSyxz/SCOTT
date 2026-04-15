<x-app-layout :breadcrumbs="[
        [
            'name' => __('Home'),
            'icon' => 'fa-solid fa-house',
            'route' => route('dashboard'),
        ],
        [
            'name' => __('Reports'),
            'icon' => 'fa-solid fa-paste',
            'route' => route('reports.index'),
        ],
        [
            'name' => __('Report'),
            'icon' => 'fa-solid fa-circle-info',
        ],
    ]">

    @php
        $brand = (auth()->user()?->area === 'OTT') ? 'primary' : 'secondary';
    @endphp

    <div class="px-6 mx-auto h-auto">
        <div class="w-full bg-white dark:bg-gray-800 p-6 rounded-lg shadow-2xl mx-auto mb-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <div
                    class="flex flex-col sm:flex-row items-start sm:items-center gap-4 bg-white dark:bg-gray-800 px-4 py-3 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 w-full sm:w-auto">
                    <div class="flex items-center gap-4">
                        <i class="fa-solid fa-file-alt text-gray-800 dark:text-gray-100 text-2xl"></i>
                        <span
                            class="block sm:inline-block text-lg sm:text-xl font-semibold text-gray-900 dark:text-white leading-tight truncate max-w-[200px] sm:max-w-[520px] overflow-hidden"
                            title="{{ $report->category }}">
                            {{ __($report->category) }}
                        </span>
                    </div>
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between sm:justify-end gap-2 w-full sm:w-auto">
                        <div class="flex flex-nowrap sm:flex-wrap justify-between sm:justify-start items-center gap-2">
                            @php
                                $areaIcon = null;
                                if ($report->area === 'DTH') {
                                    $areaIcon = 'fa-satellite-dish';
                                } elseif ($report->area === 'OTT') {
                                    $areaIcon = 'fa-cube';
                                }
                                $areaBadgeClass = 'text-gray-800 bg-gray-200 dark:bg-gray-700 dark:text-gray-200';
                                if ($report->area === 'DTH') {
                                    $areaBadgeClass = 'text-secondary-800 bg-secondary-200 dark:bg-secondary-800 dark:text-secondary-200';
                                } elseif ($report->area === 'OTT') {
                                    $areaBadgeClass = 'text-primary-800 bg-primary-200 dark:bg-primary-800 dark:text-primary-200';
                                }
                            @endphp

                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md md:rounded-lg {{ $areaBadgeClass }} text-[10px] font-medium uppercase tracking-wide md:px-3 md:py-1.5 md:text-xs md:mr-2 flex-shrink-0" aria-label="Area {{ $report->area ?? '' }}">
                                <i class="fa-solid {{ $areaIcon }} text-[10px] md:text-xs leading-none" aria-hidden="true"></i>
                                <span class="leading-none">{{ $report->area ?? '' }}</span>
                            </span>

                            @if ($report->type === 'Momentary')
                                <span class="inline-flex items-center text-[10px] font-medium text-red-800 bg-red-200 dark:bg-red-800 dark:text-red-200 px-2 py-0.5 rounded-md shadow-sm gap-1 md:px-3 md:py-1 md:rounded-lg md:gap-2 md:text-xs flex-shrink-0">
                                    <i class="fa-solid fa-triangle-exclamation text-[10px] md:text-xs"></i>
                                    <span>{{ __($report->type) }}</span>
                                </span>
                            @elseif ($report->type === 'Hourly')
                                <span class="inline-flex items-center text-[10px] font-medium text-green-800 bg-green-200 dark:bg-green-800 dark:text-green-200 px-2 py-0.5 rounded-md shadow-sm gap-1 md:px-3 md:py-1 md:rounded-lg md:gap-2 md:text-xs flex-shrink-0">
                                    <i class="fa-solid fa-clock text-[10px] md:text-xs"></i>
                                    <span>{{ __($report->type) }}</span>
                                </span>
                            @else
                                <span class="inline-flex items-center text-[10px] font-medium text-blue-800 bg-blue-200 dark:bg-blue-800 dark:text-blue-200 px-2 py-0.5 rounded-md shadow-sm gap-1 md:px-3 md:py-1 md:rounded-lg md:gap-2 md:text-xs flex-shrink-0">
                                    <i class="fa-solid fa-forward text-[10px] md:text-xs"></i>
                                    <span>{{ __($report->type) }}</span>
                                </span>
                            @endif
                            <span class="text-xs md:text-sm font-medium text-gray-600 dark:text-gray-300 mx-0 md:mx-2 whitespace-nowrap truncate max-w-[90px] md:max-w-[110px]">
                                {{ __('Folio') }} #{{ $report->id }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row items-center gap-2 justify-center sm:justify-start w-full sm:w-auto space-y-2 sm:space-y-0">
                    <a href="{{ route('reports.index') }}"
                        class="inline-flex w-full sm:w-auto items-center justify-center gap-2 px-4 py-2 mr-0 md:mr-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-200 dark:hover:bg-gray-600 shadow-2xl">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span class="text-sm font-medium">{{ __('Go back') }}</span>
                    </a>

                    @if ($report->type === 'Momentary' && $report->status === 'Revision' && Auth::check() && $report->canBeEditedBy(Auth::user()))
                        <a href="{{ route('reports.edit', ['report' => $report->id]) }}" class="inline-flex w-full sm:w-auto items-center justify-center gap-2 px-4 py-2 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200 hover:bg-blue-200 dark:hover:bg-blue-800 border border-blue-200 dark:border-blue-700 rounded-lg">
                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                            <span class="text-sm font-medium">{{ __('Edit') }}</span>
                        </a>
                    @endif
                </div>
            </div>
            <div
                class="p-5 rounded-xl shadow-md transition-transform hover:scale-[1.02]
                bg-gradient-to-br from-pink-500 via-orange-400 to-red-500
                dark:from-blue-900 dark:via-indigo-800 dark:to-purple-900
                text-white dark:text-gray-100 ring-1 ring-white/20 dark:ring-gray-700 hover:shadow-[0_8px_25px_rgba(0,0,0,0.3)]">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                    <div class="flex items-center gap-4 w-auto">
                        <img src="{{ optional($report->reportedBy)->profile_photo_url ?? '' }}" alt="{{ optional($report->reportedBy)->name ?? __('User') }}"
                            class="w-12 h-12 rounded-full shadow-2xl">
                        <div>
                            <h4 class="text-sm font-semibold">{{ __('Reported by') }}</h4>
                            <p class="text-base font-bold">{{ optional($report->reportedBy)->name ?? __('Unknown') }}</p>
                            <p class="text-xs opacity-80">
                                {{ $report->created_at->format('d/m/Y h:i A') }}
                                @if ($report->updated_at && $report->updated_at->gt($report->created_at))
                                    - {{ $report->updated_at->format('d/m/Y h:i A') }}
                                    <br>
                                    <small>
                                        ({{ __('Last updated') }}: {{ $report->updated_at->diffForHumans() }})
                                    </small>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                        <div
                            class="flex items-center gap-3 bg-white/20 dark:bg-gray-800 px-4 py-3 rounded-lg shadow-md w-full sm:w-auto">
                            <div
                                class="flex items-center justify-center w-10 h-10 bg-white text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded-full shadow-md">
                                @if ($report->type === 'Momentary')
                                    <i
                                        class="fa-solid {{ $report->status === 'Resolved' ? 'fa-circle-check' : 'fa-magnifying-glass' }} text-lg"></i>
                                @elseif ($report->type === 'Hourly')
                                    <i class="fa-solid fa-folder text-lg"></i>
                                @else
                                    <i class="fa-solid fa-forward text-lg"></i>
                                @endif
                            </div>
                            <div>
                                <h4 class="text-sm">{{ __('Status') }}</h4>
                                <p class="text-sm font-bold uppercase">{{ __($report->status) }}</p>
                            </div>
                        </div>
                        @if ($report->type === 'Momentary')
                            <div
                                class="flex items-center gap-3 bg-white/20 dark:bg-gray-800 px-4 py-3 rounded-lg shadow-md w-full">
                                <div
                                    class="flex items-center justify-center w-10 h-10 bg-white text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded-full shadow-md flex-shrink-0">
                                    <i class="fa-solid fa-gear text-lg"></i>
                                </div>
                                <div class="w-full">
                                    <h4 class="text-sm whitespace-normal break-words">{{ __('Under review by') }}</h4>
                                    <p class="text-sm font-bold whitespace-normal break-words">
                                        {{ __($report->reviewed_by) }}
                                        @if ($report->duration)
                                            <span class="block sm:inline text-xs dark:text-gray-400">
                                                {{ __('Resolved') }}
                                                {{ $report->updated_at->diffForHumans($report->created_at) }}
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            @if(!empty($sla) && ($sla['enabled'] ?? false))
                                <div class="flex items-center gap-3 bg-white/20 dark:bg-gray-800 px-4 py-2 rounded-lg shadow-md w-full">
                                    <div class="flex items-center justify-center w-10 h-10 bg-white text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded-full shadow-md">
                                        <i class="fa-solid fa-traffic-light text-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm text-white dark:text-gray-300">{{ __('SLA') }}</h4>
                                        <p class="text-sm font-bold text-white dark:text-gray-100 flex items-center uppercase">
                                            {{ $sla['label'] }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            @if ($report->type === 'Momentary')
                <div x-data="{ open: false }"
                    class="mt-6 border border-gray-300 dark:border-gray-700 rounded-xl overflow-hidden shadow-lg">
                    <button @click="open = !open"
                        class="text-base sm:text-lg font-semibold text-gray-800 dark:text-white cursor-pointer px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between w-full bg-white dark:bg-gray-800 border-b dark:border-gray-700 space-y-2 sm:space-y-0">
                        <div
                            class="flex flex-col sm:flex-row items-center justify-center sm:justify-start gap-2 w-full sm:w-auto text-center sm:text-left">
                            <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"
                                class="fa-solid text-gray-800 dark:text-white"></i>

                            <span
                                class="bg-gray-50 dark:bg-gray-700 border dark:border-white rounded-full px-4 py-2 text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2 max-w-full sm:max-w-xs w-full sm:w-auto overflow-hidden justify-center sm:justify-start text-center sm:text-left">
                                <i class="fa-solid fa-layer-group"></i>
                                <span class="truncate block w-full" title="{{ $report->category }}">
                                    {{ $report->category }}
                                </span>
                            </span>
                        </div>
                        <span
                            class="bg-{{ $brand }}-100 text-{{ $brand }}-800 text-sm font-medium py-1 px-3 rounded-full text-center sm:text-left w-full sm:w-auto">
                            {{ $report->reportDetails->count() }}
                            {{ $report->reportDetails->count() === 1 ? __('Channel') : __('Channels') }}
                        </span>
                    </button>

                    <div x-show="open" x-collapse
                        class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 p-6 bg-white dark:bg-gray-800">
                        @foreach ($report->reportDetails->sortBy(fn($detail) => $detail->channel->number) as $detail)
                            <div
                                class="relative overflow-visible flex flex-col px-5 py-3 bg-white border border-gray-300 dark:bg-gray-800 dark:border-gray-700 rounded-xl space-y-4">
                                @if ($detail->description)
                                    <div x-data="{ openModal: false }" class="absolute -top-3 -right-3 h-6 w-6"
                                        :class="{ 'z-[60]': openModal, 'z-50': !openModal }">
                                        <button @click.stop="openModal = true; $event.stopImmediatePropagation()"
                                            type="button"
                                            class="flex items-center justify-center w-full h-full rounded-full text-sm text-gray-500 dark:text-gray-300">
                                            <i class="fa-solid fa-circle-info text-base"></i>
                                        </button>
                                        <div x-show="openModal" x-transition:enter="ease-out duration-200"
                                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                            x-transition:leave="ease-in duration-150"
                                            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 p-4"
                                            x-cloak>
                                            <div @click.away="openModal = false; $event.stopPropagation()"
                                                x-show="openModal" x-transition:enter="ease-out duration-200"
                                                x-transition:enter-start="opacity-0 scale-95"
                                                x-transition:enter-end="opacity-100 scale-100"
                                                x-transition:leave="ease-in duration-150"
                                                x-transition:leave-start="opacity-100 scale-100"
                                                x-transition:leave-end="opacity-0 scale-95"
                                                class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl w-full max-w-sm p-6 relative">
                                                <button @click.stop="openModal = false"
                                                    class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                                    <i class="fa-solid fa-xmark text-lg"></i>
                                                </button>
                                                <h2
                                                    class="text-lg font-semibold text-gray-900 dark:text-white text-center mb-4">
                                                    {{ __('Description') }}
                                                </h2>
                                                <p
                                                    class="text-gray-700 dark:text-gray-300 text-sm text-center break-words">
                                                    {{ $detail->description }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="flex items-center gap-2 w-full">
                                    <div class="w-1/3 flex-shrink-0">
                                        <img src="{{ $detail->channel->image }}" alt="{{ $detail->channel->name }}"
                                            title="{{ $detail->channel->number }} {{ strtoupper($detail->channel->name) }} ({{ strtoupper($detail->channel->origin) }})"
                                            class="w-10 h-10 object-contain object-center">
                                    </div>
                                    <div class="w-2/3 flex flex-col justify-center text-end">
                                        <p
                                            class="text-base font-semibold text-gray-900 dark:text-white leading-tight truncate leading-tight">
                                            {{ $detail->channel->number }} {{ strtoupper($detail->channel->name) }} ({{ strtoupper($detail->channel->origin) }})
                                        </p>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate leading-tight">
                                            @if(($report->area ?? null) !== 'DTH')
                                                {{ $detail->stage->name }} ({{ $detail->protocol }})
                                            @else
                                                {{ $detail->stage->name }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            <div class="flex-none mt-3">
                                <div
                                    class="flex justify-center items-end gap-3 px-5 py-3 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg">
                                    @php
                                        $mediaLabel = __($detail->media ?? 'N/A');
                                    @endphp
                                        <div class="flex justify-center items-center tooltip" title="{{ $mediaLabel }}">
                                            <i class="fa-solid fa-exclamation-triangle text-yellow-400 mr-1.5"></i>
                                            <span class="text-gray-500 dark:text-gray-300">{{ $mediaLabel }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            @if ($report->type === 'Functions' && $report->category === 'Chromecast')
                <div class="mt-6 border border-gray-300 dark:border-gray-700 rounded-lg shadow-2xl p-6 bg-white dark:bg-gray-800">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                        <i class="fa-brands fa-chromecast"></i>
                        Chromecast
                    </h3>
                    @if (empty($report->chromecast?->description))
                        <div class="flex items-center gap-2 p-4 bg-green-50 border border-green-300 dark:border-green-700 dark:bg-gray-700 rounded-lg shadow">
                            <i class="fa-solid fa-circle-check text-green-500 dark:text-green-400 text-2xl"></i>
                            <span class="text-base font-semibold text-gray-900 dark:text-white">
                                {{ __('No issues reported for Chromecast.') }}
                            </span>
                        </div>
                    @else
                        <div class="flex items-center gap-2 p-4 bg-yellow-50 border border-yellow-300 dark:border-yellow-700 dark:bg-gray-700 rounded-lg shadow">
                            <i class="fa-solid fa-circle-info text-yellow-500 dark:text-yellow-400 text-2xl"></i>
                            <span class="text-base font-semibold text-gray-900 dark:text-white">
                                {{ $report->chromecast->description }}
                            </span>
                        </div>
                    @endif
                </div>
            @elseif ($report->type === 'Functions' && $report->category === 'Speed Profiles')
                <x-report-profile-table :report="$report" />
            @elseif ($report->type === 'Functions' && $report->category === 'StarTV Stream Availability')
                <x-report-device-store-table :report="$report" />
            @else
                @php
                    $fixedCategories = [];
                    if ($report->type === 'Hourly') {
                        $fixedCategories = ['CDN TELMEX', 'CDN CEF+', 'STINGRAY'];
                    } elseif ($report->type === 'Functions') {
                        $fixedCategories = ['RESTART', 'CUTV', 'EPG', 'PC'];
                    }

                    $dynamicCategories = $report->reportDetails->pluck('subcategory')->unique()->toArray();

                    $allCategories = collect($fixedCategories)->merge($dynamicCategories)->unique()->values()->toArray();
                @endphp
                @if ($report->type === 'Hourly' || $report->type === 'Functions')
                    @foreach ($allCategories as $fixedCategory)
                        <div x-data="{ open: false }"
                            class="mt-6 border border-gray-300 dark:border-gray-700 rounded-lg shadow-2xl">
                            <h3 @click="open = !open"
                                class="text-base sm:text-lg font-semibold text-gray-800 dark:text-white cursor-pointer px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between border-b dark:border-gray-700 space-y-2 sm:space-y-0">
                                <div
                                    class="flex flex-col sm:flex-row items-center justify-center sm:justify-start gap-2 w-full sm:w-auto">
                                    <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"
                                        class="fa-solid text-gray-800 dark:text-white"></i>
                                    <span
                                        class="bg-gray-50 dark:bg-gray-700 border dark:border-white rounded-full px-4 py-2 text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2 max-w-full sm:max-w-xs w-fit overflow-hidden">
                                        <i class="fa-solid fa-layer-group"></i>
                                        <span class="truncate" title="{{ $fixedCategory }}">
                                            {{ $fixedCategory }}
                                        </span>
                                    </span>
                                </div>
                                <span
                                    class="bg-{{ $brand }}-100 text-{{ $brand }}-800 text-sm font-medium py-1 px-3 rounded-full text-center sm:text-left w-full sm:w-auto">
                                    @if (
                                        in_array($fixedCategory, $allCategories) &&
                                        $report->reportDetails->where('subcategory', $fixedCategory)->count() > 0
                                    )
                                        {{ $report->reportDetails->where('subcategory', $fixedCategory)->count() }}
                                        {{ $report->reportDetails->where('subcategory', $fixedCategory)->count() === 1 ? __('Channel') : __('Channels') }}
                                    @else
                                        <span class="text-gray-500 dark:text-gray-400 italic">
                                            {{ __('All clear in this category') }}
                                        </span>
                                    @endif
                                </span>
                            </h3>
                            <div x-show="open" x-collapse
                                class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 p-6 bg-white dark:bg-gray-800">
                                @if ($report->reportDetails->where('subcategory', $fixedCategory)->count() > 0)
                                    @foreach ($report->reportDetails->where('subcategory', $fixedCategory)->sortBy(fn($detail) => $detail->channel->number) as $detail)
                                        <div
                                            class="relative flex flex-col px-5 py-3 bg-white border border-gray-300 dark:bg-gray-800 dark:border-gray-700 rounded-xl shadow-2xl space-y-4">
                                            @if ($detail->description)
                                                <div x-data="{ openModal: false }" class="absolute -top-3 -right-3 h-6 w-6"
                                                    :class="{ 'z-[60]': openModal, 'z-50': !openModal }">
                                                    <button
                                                        @click.stop="openModal = true; $event.stopImmediatePropagation()"
                                                        type="button"
                                                        class="flex items-center justify-center w-full h-full rounded-full text-sm text-gray-500 dark:text-gray-300">
                                                        <i class="fa-solid fa-circle-info text-base"></i>
                                                    </button>
                                                    <div x-show="openModal" x-transition:enter="ease-out duration-200"
                                                        x-transition:enter-start="opacity-0"
                                                        x-transition:enter-end="opacity-100"
                                                        x-transition:leave="ease-in duration-150"
                                                        x-transition:leave-start="opacity-100"
                                                        x-transition:leave-end="opacity-0"
                                                        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 p-4"
                                                        x-cloak>
                                                        <div @click.away="openModal = false; $event.stopPropagation()"
                                                            x-show="openModal" x-transition:enter="ease-out duration-200"
                                                            x-transition:enter-start="opacity-0 scale-95"
                                                            x-transition:enter-end="opacity-100 scale-100"
                                                            x-transition:leave="ease-in duration-150"
                                                            x-transition:leave-start="opacity-100 scale-100"
                                                            x-transition:leave-end="opacity-0 scale-95"
                                                            class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl w-full max-w-sm p-6 relative">
                                                            <button @click.stop="openModal = false"
                                                                class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                                                <i class="fa-solid fa-xmark text-lg"></i>
                                                            </button>
                                                            <h2
                                                                class="text-lg font-semibold text-gray-900 dark:text-white text-center mb-4">
                                                                {{ __('Description') }}
                                                            </h2>
                                                            <p
                                                                class="text-gray-700 dark:text-gray-300 text-sm text-center break-words">
                                                                {{ $detail->description }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="flex items-start">
                                                <div class="flex items-center gap-2 w-full">
                                                    <div class="w-1/3 flex-shrink-0">
                                                        <img src="{{ $detail->channel->image }}"
                                                            alt="{{ strtoupper($detail->channel->name) }} ({{ strtoupper($detail->channel->origin) }})"
                                                            class="w-10 h-10 object-contain object-center">
                                                    </div>
                                                    <div class="w-2/3 flex flex-col justify-center text-end">
                                                        <p
                                                            class="text-base font-semibold text-gray-900 dark:text-white leading-tight">
                                                            {{ $detail->channel->number }} {{ strtoupper($detail->channel->name) }} ({{ strtoupper($detail->channel->origin) }})
                                                        </p>
                                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                            {{ $detail->stage->name }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div
                                            class="flex justify-around items-center gap-3 px-5 py-3 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg">
                                            <div class="flex flex-col items-center tooltip"
                                                title="{{ $detail->media === 'VIDEO' || $detail->media === 'AUDIO/VIDEO' ? __('The channel does not have video') : __('The channel has video') }}">
                                                <i
                                                    class="fa-solid mt-2 {{ $detail->media === 'VIDEO' || $detail->media === 'AUDIO/VIDEO' ? 'fa-video-slash text-red-500' : 'fa-video text-green-500' }} text-xl"></i>
                                                <span class="text-[10px] mt-2 text-gray-500 dark:text-gray-300">VIDEO</span>
                                            </div>
                                            <div class="flex flex-col items-center tooltip"
                                                title="{{ $detail->media === 'AUDIO' || $detail->media === 'AUDIO/VIDEO' ? __('The channel does not have audio') : __('The channel has audio') }}">
                                                <i
                                                    class="fa-solid mt-2 {{ $detail->media === 'AUDIO' || $detail->media === 'AUDIO/VIDEO' ? 'fa-volume-xmark text-red-500' : 'fa-volume-up text-green-500' }} text-xl"></i>
                                                <span class="text-[10px] mt-2 text-gray-500 dark:text-gray-300">AUDIO</span>
                                            </div>
                                            <div class="flex flex-col items-center tooltip"
                                                title="{{ $detail->protocol === 'DASH' || $detail->protocol === 'HLS/DASH' ? __('Not working on Web Client (DASH)') : __('Working on Web Client (DASH)') }}">
                                                <i
                                                    class="fa-solid mt-2 fa-computer {{ $detail->protocol === 'DASH' || $detail->protocol === 'HLS/DASH' ? 'text-red-500' : 'text-green-500' }} text-xl"></i>
                                                <span class="text-[10px] mt-2 text-gray-500 dark:text-gray-300">DASH</span>
                                            </div>
                                            <div class="flex flex-col items-center tooltip"
                                                title="{{ $detail->protocol === 'HLS' || $detail->protocol === 'HLS/DASH' ? __('Not working on Set Up Box (HLS)') : __('Working on Set Up Box (HLS)') }}">
                                                <i
                                                    class="fa-solid mt-2 fa-tv {{ $detail->protocol === 'HLS' || $detail->protocol === 'HLS/DASH' ? 'text-red-500' : 'text-green-500' }} text-xl"></i>
                                                <span class="text-[10px] mt-2 text-gray-500 dark:text-gray-300">HLS</span>
                                            </div>
                                        </div>
                                    @if ($fixedCategory === 'CUTV' && $detail->reportContentLosses->isNotEmpty())
                                        <div x-data="{ showLosses: false }" class="mt-5 w-full text-center">
                                            <div class="flex justify-center">
                                                <button @click="showLosses = !showLosses"
                                                    class="text-[10px] lg:text-[11px] text-{{ $brand }}-600 dark:text-{{ $brand }}-400 font-medium flex justify-center items-center gap-1 hover:underline">
                                                    <i class="fa-solid fa-clock text-xs"></i>
                                                    {{ __('View content loss intervals') }}
                                                    <i :class="showLosses ? 'fa-chevron-up' : 'fa-chevron-down'"
                                                        class="fa-solid ml-1 text-xs transition-transform duration-200"></i>
                                                </button>
                                            </div>

                                            <div x-show="showLosses" x-collapse class="mt-4 overflow-x-auto">
                                                <div
                                                    class="inline-block min-w-full align-middle border border-gray-200 dark:border-gray-700 lg:rounded-lg overflow-hidden bg-white dark:bg-gray-800 shadow-sm">
                                                    <table
                                                        class="min-w-full text-[10px] lg:text-[11px] text-gray-700 dark:text-gray-300">
                                                        <thead>
                                                            <tr class="bg-gray-100 dark:bg-gray-700">
                                                                <th class="py-2 px-3 text-center">
                                                                    {{ __('Start time') }}</th>
                                                                <th class="py-2 px-3 text-center">
                                                                    {{ __('End time') }}</th>
                                                                <th class="py-2 px-3 text-center">
                                                                    {{ __('Duration') }}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($detail->reportContentLosses as $loss)
                                                                @php
                                                                    $start = \Carbon\Carbon::parse(
                                                                        $loss->start_time,
                                                                    );
                                                                    $end = \Carbon\Carbon::parse($loss->end_time);
                                                                    $diff = $start->diff($end);
                                                                    $days = $diff->format('%a');
                                                                    $hours = $diff->format('%H');
                                                                    $minutes = $diff->format('%I');

                                                                    $duration =
                                                                        ($days > 0 ? "{$days}d " : '') .
                                                                        "{$hours}h {$minutes}m";
                                                                @endphp
                                                                <tr
                                                                    class="border-b border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600">
                                                                    <td class="py-2 px-3 text-center">
                                                                        {{ $start->format('d/m/Y H:i') }}</td>
                                                                    <td class="py-2 px-3 text-center">
                                                                        {{ $end->format('d/m/Y H:i') }}</td>
                                                                    <td
                                                                        class="py-2 px-3 text-center font-semibold">
                                                                        {{ $duration }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                    @endforeach
                                @else
                                    <div
                                        class="col-span-full flex flex-col items-center p-6 bg-green-50 border border-green-300 dark:border-green-700 dark:bg-gray-700 rounded-lg shadow-2xl">
                                        <i
                                            class="fa-solid fa-circle-check text-green-500 dark:text-green-400 text-3xl mb-3"></i>
                                        <span
                                            class="block text-center text-base font-semibold text-gray-900 dark:text-white">
                                            {{ __('No issues reported in this category') }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            @endif
        </div>
        @if ($report->type === 'Momentary')
            <div class="w-full bg-white dark:bg-gray-800 p-6 rounded-lg shadow-2xl mx-auto mb-4 mt-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-comments"></i>
                    {{ __('Comments') }}
                </h2>
                <div class="space-y-4 max-h-[500px] overflow-y-auto">
                    @forelse ($comments as $comment)
                        <div class="border-l-4 border-primary-500 bg-gray-50 dark:bg-gray-700 px-4 py-3 rounded-lg">
                            <div class="flex justify-between items-start gap-2 mb-2">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $comment->user->profile_photo_url }}" alt="{{ $comment->user->name }}"
                                        class="w-8 h-8 rounded-full object-cover">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $comment->user->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $comment->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </div>
                            @php
                                $body = trim($comment->body ?? '');
                                $bodySent = $body !== '' ? mb_strtoupper(mb_substr($body, 0, 1)) . mb_substr(mb_strtolower($body), 1) : $body;
                            @endphp
                            <p class="text-sm text-gray-700 dark:text-gray-300 break-words mt-4">{{ $bodySent }}</p>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-8 text-gray-500 dark:text-gray-400">
                            <i class="fa-solid fa-comments text-3xl mb-2"></i>
                            <p class="text-sm">{{ __('No comments yet') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
