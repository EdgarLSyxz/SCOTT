<div
    class="bg-white dark:bg-gray-900 p-4 sm:p-6 rounded-2xl shadow-2xl w-full max-w-6xl mx-auto mt-8 md:mt-0 flex flex-col md:min-h-[80vh] ml-0.5">
    @php
$userArea = Auth::user()?->area ?? ($selectedReport->area ?? null);
$isOtt = $userArea === 'OTT';
$isDth = $userArea === 'DTH';

$iconMainClass = $isOtt ? 'text-primary-200' : ($isDth ? 'text-secondary-200' : 'text-gray-800 dark:text-white');

$channelsBadgeClasses = $isOtt
    ? 'bg-primary-100 text-primary-800'
    : ($isDth ? 'bg-secondary-100 text-secondary-800' : 'bg-primary-100 text-primary-800');

$statIconBg = $isOtt ? 'bg-white text-primary-700 dark:bg-primary-700 dark:text-primary-200' : ($isDth ? 'bg-white text-secondary-700 dark:bg-secondary-700 dark:text-secondary-200' : 'bg-white text-gray-700 dark:bg-gray-700 dark:text-gray-300');

$btnPrimary = $isOtt ? 'py-2 px-4 bg-primary-600 hover:bg-primary-700' : ($isDth ? 'py-2 px-4 bg-secondary-600 hover:bg-secondary-700' : 'py-2 px-4 bg-primary-600 hover:bg-primary-700');

$discardHover = $isOtt ? 'hover:border-primary-600 hover:text-primary-600 dark:hover:text-primary-400 dark:hover:bg-gray-700' : ($isDth ? 'hover:border-secondary-600 hover:text-secondary-600 dark:hover:text-secondary-400 dark:hover:bg_gray-700' : 'hover:border-primary-600 hover:text-primary-600 dark:hover:text-primary-400 dark:hover:bg-gray-700');
    @endphp
    <div
        class="flex flex-col sm:flex-row sm:flex-wrap justify-between items-start gap-4 mb-6 overflow-x-auto">
        <div
            class="flex flex-wrap items-center gap-4 bg-white dark:bg-gray-800 px-4 py-2 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 w-full sm:w-auto min-w-0">
            <i class="fa-solid fa-file-alt text-gray-800 dark:text-gray-100 text-2xl"></i>
            <span
                class="text-xl font-semibold text-gray-900 dark:text-white leading-tight truncate max-w-[250px] sm:max-w-2xl overflow-hidden whitespace-nowrap">
                {{ $selectedReport->category }}
            </span>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between sm:justify-end gap-2 w-full sm:w-auto">
                <div class="flex flex-wrap justify-between sm:justify-start items-center gap-2">
                    @php $area = $selectedReport->area ?? null; @endphp
                    @php
$areaBadge = $area === 'DTH'
    ? 'text-secondary-800 bg-secondary-200 dark:bg-secondary-800 dark:text-secondary-200'
    : ($area === 'OTT'
        ? 'text-primary-800 bg-primary-200 dark:bg-primary-800 dark:text-primary-200'
        : 'text-gray-800 bg-gray-200 dark:bg-gray-700 dark:text-gray-200');
                    @endphp

                    <span
                        class="inline-flex items-center gap-1 px-2 py-1 mr-2 text-xs font-medium rounded-md {{ $areaBadge }} shadow-sm">
                        @if($area === 'DTH')
                            <i class="fa-solid fa-satellite-dish"></i>
                        @elseif($area === 'OTT')
                            <i class="fa-solid fa-cube"></i>
                        @else
                            <i class="fa-solid fa-layer-group"></i>
                        @endif
                        <span>{{ $area ?? __('N/A') }}</span>
                    </span>

                    @php
$type = $selectedReport->type ?? '';
                    @endphp
                    @if($type === 'Momentary')
                        <span
                            class="inline-flex items-center gap-1 text-xs font-medium text-red-800 bg-red-200 dark:bg-red-800 dark:text-red-200 px-2 py-1 rounded-md shadow-sm">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span>{{ __($type) }}</span>
                        </span>
                    @elseif($type === 'Hourly')
                        <span
                            class="inline-flex items-center gap-1 text-xs font-medium text-green-800 bg-green-200 dark:bg-green-800 dark:text-green-200 px-2 py-1 rounded-md shadow-sm">
                            <i class="fa-solid fa-clock"></i>
                            <span>{{ __($type) }}</span>
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-1 text-xs font-medium text-blue-800 bg-blue-200 dark:bg-blue-800 dark:text-blue-200 px-2 py-1 rounded-md shadow-sm">
                            <i class="fa-solid fa-forward"></i>
                            <span>{{ __($type) }}</span>
                        </span>
                    @endif

                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400 ml-2">
                        {{ __('Folio') }} #{{ $selectedReport->id }}
                    </span>
                </div>
                <div class="hidden md:flex md:items-center">
                    <a href="{{ route('reports.show', ['report' => $selectedReport->id]) }}"
                        class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 border border-gray-200 dark:border-gray-600 ml-2 mr-2 transition">
                        <i class="fa-solid fa-eye mr-1"></i> {{ __('Show') }}
                    </a>
                    @if(Auth::check() && $selectedReport->canBeEditedBy(Auth::user()))
                        <a href="{{ route('reports.edit', ['report' => $selectedReport->id]) }}"
                            class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-lg bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200 hover:bg-blue-200 dark:hover:bg-blue-800 border border-blue-200 dark:border-blue-700 ml-2 transition">
                            <i class="fa-solid fa-pen-to-square mr-1"></i> {{ __('Edit') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
        <button wire:click="closeReportDetails"
            class="hidden sm:block text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white flex-shrink-0">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>
    </div>

    <div class="flex flex-col gap-4 md:hidden sm:block">
        <a href="{{ route('reports.show', ['report' => $selectedReport->id]) }}"
            class="inline-flex justify-center items-center px-2 py-2 text-xs text-center font-semibold rounded-lg bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-700 transition">
            <i class="fa-solid fa-eye mr-1"></i> {{ __('Show') }}
        </a>
        @if(Auth::check() && $selectedReport->canBeEditedBy(Auth::user()))
            <a href="{{ route('reports.edit', ['report' => $selectedReport->id]) }}"
                class="inline-flex justify-center items-center px-2 py-2 text-xs text-center font-semibold rounded-lg bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200 hover:bg-blue-200 dark:hover:bg-blue-800 border border-blue-200 dark:border-blue-700 mb-6 transition">
                <i class="fa-solid fa-pen-to-square mr-1"></i> {{ __('Edit') }}
            </a>
        @endif
    </div>

    <div
        class="p-4 sm:p-5 rounded-xl shadow-md transition-transform hover:scale-[1.02]
        bg-gradient-to-br from-pink-500 via-orange-400 to-red-500
        dark:from-blue-900 dark:via-indigo-800 dark:to-purple-900
        text-white dark:text-gray-100 ring-1 ring-white/20 dark:ring-gray-700 hover:shadow-[0_8px_25px_rgba(0,0,0,0.3)]">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                <div class="flex items-center gap-4 flex-1 min-w-0">
                <img src="{{ $selectedReport->reportedBy->profile_photo_url }}"
                    alt="{{ $selectedReport->reportedBy->name }}"
                    class="w-12 h-12 rounded-full shadow-2xl object-cover">
                <div>
                    <h4 class="text-sm font-semibold text-white dark:text-gray-300">
                        {{ __('Reported by') }}
                    </h4>
                    <p class="text-base font-bold text-white dark:text-gray-100">
                        {{ $selectedReport->reportedBy->name }}
                    </p>
                    <p class="text-xs text-gray-200 dark:text-gray-300 opacity-80">
                        {{ $selectedReport->created_at->format('d/m/Y h:i A') }}
                        @if ($selectedReport->updated_at && $selectedReport->updated_at->gt($selectedReport->created_at))
                            <br>
                            <small>
                                ({{ __('Last updated') }}: {{ $selectedReport->updated_at->diffForHumans() }})
                            </small>
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex gap-4 flex-wrap justify-end w-full md:w-auto">
                <div
                    class="flex items-center gap-3 bg-white/20 dark:bg-gray-800 px-4 py-3 rounded-lg shadow-md w-full sm:w-auto mb-4 sm:mb-0 ">
                    <div
                        class="flex items-center justify-center w-10 h-10 bg-white text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded-full shadow-md">
                        <i class="fa-solid fa-magnifying-glass text-lg"></i>
                    </div>
                    <div>
                        <h4 class="text-sm text-white dark:text-gray-300">
                            {{ __('Status') }}
                        </h4>
                        <p class="text-sm font-bold uppercase">
                            {{ __($selectedReport->status) }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3 bg-white/20 dark:bg-gray-800 px-4 py-3 rounded-lg shadow-md w-full sm:w-auto">
                    <div
                        class="flex items-center justify-center w-10 h-10 bg-white text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded-full shadow-md">
                        <i class="fa-solid fa-gear text-lg"></i>
                    </div>
                    <div>
                        <h4 class="text-sm text-white dark:text-gray-300">
                            {{ __('Under review by') }}
                        </h4>
                        <p class="text-sm font-bold text-white dark:text-gray-100">
                            {{ __($selectedReport->reviewed_by) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-5 flex flex-col flex-1 overflow-hidden min-h-0">
        <div class="flex flex-wrap items-center gap-2">
            <i class="fa-solid fa-layer-group text-xl text-gray-800 dark:text-white mr-3"></i>
            <span class="text-lg font-semibold text-gray-800 dark:text-white mr-1.5">
                {{ __('This report contains') }}
            </span>
            <span class="{{ $channelsBadgeClasses }} text-sm font-medium py-1 px-3 rounded-full ml-1.5">
                {{ isset($selectedReport) && $selectedReport ? $selectedReport->reportDetails->count() : 0 }}
                {{ isset($selectedReport) && $selectedReport->reportDetails->count() === 1 ? __('Channel') : __('Channels') }}
            </span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4 sm:gap-6 p-2 mt-3 flex-1 min-h-0 z-0"
            style="scrollbar-width: none; max-height: 24rem; overflow-y: auto;">
            @if (isset($selectedReport) && $selectedReport)
                @foreach ($selectedReport->reportDetails->sortBy(fn($detail) => $detail->channel->number) as $detail)
                    <div
                        class="relative overflow-visible flex flex-col px-3 py-3 sm:px-4 sm:py-4 bg-white border border-gray-300 dark:bg-gray-800 dark:border-gray-700 rounded-xl space-y-3 text-sm min-w-0 h-auto max-h-[190px]">
                        @if ($detail->description)
                            <div x-data="{ openModal: false }" class="absolute -top-3 -right-3 h-6 w-6"
                                :class="{ 'z-[60]': openModal, 'z-50': !openModal }">
                                <button @click.stop="openModal = true; $event.stopImmediatePropagation()" type="button"
                                    class="flex items-center justify-center w-full h-full rounded-full text-sm text-gray-500 dark:text-gray-300">
                                    <i class="fa-solid fa-circle-info text-base"></i>
                                </button>
                                <div x-show="openModal" x-transition:enter="ease-out duration-200"
                                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                    x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"
                                    class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 p-4" x-cloak>
                                    <div @click.away="openModal = false; $event.stopPropagation()" x-show="openModal"
                                        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-150"
                                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                        class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl w-full max-w-sm p-6 relative">
                                        <button @click.stop="openModal = false"
                                            class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                            <i class="fa-solid fa-xmark text-lg"></i>
                                        </button>
                                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white text-center mb-4">
                                            {{ __('Description') }}
                                        </h2>
                                        <p class="text-gray-700 dark:text-gray-300 text-sm text-center break-words">
                                            {{ $detail->description }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div @click.stop="downloadM3U('{{ $detail->channel->url }}', '{{ $detail->channel->number }}', '{{ $detail->channel->name }}');"
                            class="cursor-pointer">
                            <div class="flex items-start gap-2">
                                <div class="flex items-center gap-2 w-full min-w-0">
                                    <div class="w-10 h-10 flex-shrink-0">
                                        <img src="{{ $detail->channel->image }}" alt="{{ $detail->channel->name }}"
                                            title="{{ $detail->channel->number }} {{ $detail->channel->name }}"
                                            class="w-10 h-10 object-contain object-center">
                                    </div>
                                    <div class="flex-1 flex flex-col justify-center text-end min-w-0">
                                        <p class="text-base font-semibold text-gray-900 dark:text-white leading-tight truncate uppercase">
                                            {{ $detail->channel->number }} {{ $detail->channel->name }} ({{ $detail->channel->origin }})
                                        </p>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate leading-tight">
                                            {{ $detail->stage->name }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="flex justify-around items-center gap-3 px-5 py-3 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg mt-5">
                                <div class="flex flex-col items-center tooltip"
                                    title="{{ $detail->media === 'VIDEO' || $detail->media === 'AUDIO/VIDEO' ? __('The channel does not have video') : __('The channel has video') }}">
                                    <i
                                        class="fa-solid {{ $detail->media === 'VIDEO' || $detail->media === 'AUDIO/VIDEO' ? 'fa-video-slash text-red-500' : 'fa-video text-green-500' }} text-xl"></i>
                                    <span class="text-[10px] mt-1 text-gray-500 dark:text-gray-300">VIDEO</span>
                                </div>
                                <div class="flex flex-col items-center tooltip"
                                    title="{{ $detail->media === 'AUDIO' || $detail->media === 'AUDIO/VIDEO' ? __('The channel does not have audio') : __('The channel has audio') }}">
                                    <i
                                        class="fa-solid {{ $detail->media === 'AUDIO' || $detail->media === 'AUDIO/VIDEO' ? 'fa-volume-xmark text-red-500' : 'fa-volume-up text-green-500' }} text-xl"></i>
                                    <span class="text-[10px] mt-1 text-gray-500 dark:text-gray-300">AUDIO</span>
                                </div>
                                @if ($selectedReport->area !== 'DTH')
                                    <div class="flex flex-col items-center tooltip"
                                        title="{{ $detail->protocol === 'DASH' || $detail->protocol === 'HLS/DASH' ? __('Not working on Web Client (DASH)') : __('Working on Web Client (DASH)') }}">
                                        <i
                                            class="fa-solid fa-computer {{ $detail->protocol === 'DASH' || $detail->protocol === 'HLS/DASH' ? 'text-red-500' : 'text-green-500' }} text-xl"></i>
                                        <span class="text-[10px] mt-1 text-gray-500 dark:text-gray-300">DASH</span>
                                    </div>
                                    <div class="flex flex-col items-center tooltip"
                                        title="{{ $detail->protocol === 'HLS' || $detail->protocol === 'HLS/DASH' ? __('Not working on Set Up Box (HLS)') : __('Working on Set Up Box (HLS)') }}">
                                        <i
                                            class="fa-solid fa-tv {{ $detail->protocol === 'HLS' || $detail->protocol === 'HLS/DASH' ? 'text-red-500' : 'text-green-500' }} text-xl"></i>
                                        <span class="text-[10px] mt-1 text-gray-500 dark:text-gray-300">HLS</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        <div
            class="flex flex-col sm:flex-row flex-wrap justify-end items-stretch gap-3 sm:gap-4 w-full max-w-full my-1 pr-1 mt-auto">
            <button wire:click.prevent="markAsSolved()"
                class="{{ $btnPrimary }} text-white rounded-lg shadow font-bold text-base w-full sm:w-auto max-w-full min-w-0">
                <i class="fa-solid fa-circle-check mr-1"></i>
                {{ __('Mark as solved') }}
            </button>
            <button wire:click="closeReportDetails"
                class="flex justify-center items-center gap-2 py-2 px-4 text-base font-bold text-gray-700 bg-white rounded-lg border border-gray-400 {{ $discardHover }} dark:text-gray-300 dark:bg-gray-800 dark:border-gray-600 w-full sm:w-auto max-w-full min-w-0">
                <i class="fa-solid fa-xmark"></i>
                {{ __('Discard') }}
            </button>
        </div>
    </div>
</div>
