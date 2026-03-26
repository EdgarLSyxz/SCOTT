@php
    $rows = collect($report->deviceStoreAvailabilities ?? [])
        ->sortBy(function ($item) {
            $protocol = strtoupper($item->device?->protocol ?? '');
            $order = $protocol === 'HLS' ? 0 : ($protocol === 'DASH' ? 1 : 2);
            return [$order, $item->device?->name ?? ''];
        })
        ->values();
    $total = $rows->count();
    $availableCount = $rows->where('is_available_in_store', true)->count();
    $notAvailableCount = $rows->where('is_available_in_store', false)->count();
    $withoutNotesCount = $rows->filter(fn($item) => blank($item->notes))->count();
    $coveragePct = $total > 0 ? round(($availableCount / $total) * 100) : 0;
@endphp

<style>
    .report-table thead th {
        position: relative;
        z-index: 30;
    }
</style>

<div class="mt-6 space-y-5">

    <div class="rounded-2xl overflow-hidden shadow-md border border-gray-200 dark:border-gray-700">

        <div class="px-4 py-4 flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gray-200 dark:bg-white/10 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-store text-gray-700 dark:text-white text-lg"></i>
                </div>
                <div>
                    <h3 class="text-gray-500 dark:text-white font-bold text-base sm:text-lg leading-tight">{{ __('Availability report') }}</h3>
                    <p class="text-gray-400 dark:text-gray-300 text-xs sm:text-sm">{{ __('Quick overview of store publication status by device.') }}</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/20 border border-white/30 text-white text-xs font-semibold">
                <i class="fa-solid fa-list-check"></i>
                {{ $total }} {{ __('Devices') }}
            </span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 divide-y md:divide-y-0 divide-x-0 md:divide-x divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">

            <div class="p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-bag-shopping text-emerald-600 dark:text-emerald-400"></i>
                </div>
                <div>
                    <p class="text-2xl font-extrabold text-emerald-600 leading-none">{{ $availableCount }}</p>
                    <p class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400 mt-0.5">{{ __('Available') }}</p>
                </div>
            </div>

            <div class="p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-store-slash text-red-600 dark:text-red-400"></i>
                </div>
                <div>
                    <p class="text-2xl font-extrabold text-red-600 leading-none">{{ $notAvailableCount }}</p>
                    <p class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400 mt-0.5">{{ __('Not available') }}</p>
                </div>
            </div>

            <div class="p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-comment-slash text-slate-500 dark:text-slate-300"></i>
                </div>
                <div>
                    <p class="text-2xl font-extrabold text-slate-700 dark:text-slate-200 leading-none">{{ $withoutNotesCount }}</p>
                    <p class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400 mt-0.5">{{ __('No notes') }}</p>
                </div>
            </div>

            <div class="p-4 flex flex-col justify-center gap-1.5">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Coverage') }}</p>
                    <p class="text-xl font-extrabold text-slate-700 dark:text-slate-200 leading-none">{{ $coveragePct }}%</p>
                </div>
                <div class="w-full h-2.5 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                    <div class="h-2.5 rounded-full transition-all duration-700
                        {{ $coveragePct >= 80 ? 'bg-emerald-500' : ($coveragePct >= 50 ? 'bg-red-400' : 'bg-red-400') }}"
                        style="width: {{ $coveragePct }}%">
                    </div>
                </div>
            </div>

        </div>
    </div>

    @if ($notAvailableCount > 0)
        <div class="flex items-center gap-3 rounded-xl border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 px-4 py-3">
            <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-800/50 flex items-center justify-center flex-shrink-0 mt-0.5">
                <i class="fa-solid fa-triangle-exclamation text-amber-600 dark:text-amber-400 text-sm"></i>
            </div>
            <p class="text-sm text-amber-900 dark:text-amber-200 leading-snug">
                {{ __('The StarTV Stream app does not appear in the web version of the Samsung app store.') }}
            </p>
        </div>
    @endif

    <div class="relative overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <table class="report-table min-w-full text-xs sm:text-sm text-left">
            <thead>
                <tr>
                    <th class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 font-semibold w-[30%]">
                        <i class="fa-solid fa-hard-drive mr-2"></i>{{ __('Device') }}
                    </th>
                    <th class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 font-semibold text-center w-[30%]">
                        <i class="fa-solid fa-store mr-2"></i>{{ __('Status') }}
                    </th>
                    <th class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 font-semibold w-[40%]">
                        <i class="fa-solid fa-comment-dots mr-2"></i>{{ __('Notes') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $index => $item)
                    @php
                        $protocol = strtoupper($item->device?->protocol ?? '');
                        $prevProtocol = $index > 0 ? strtoupper($rows[$index - 1]->device?->protocol ?? '') : null;
                        $showGroupHeader = $prevProtocol !== $protocol && in_array($protocol, ['HLS', 'DASH']);
                    @endphp

                    @if ($showGroupHeader)
                        <tr class="{{ $protocol === 'HLS' ? 'relative z-20' : '' }}">
                            <td colspan="3" class="px-4 py-2 relative z-40 border-b border-gray-200 dark:border-gray-700">
                                <span class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-300">
                                    @if ($protocol === 'HLS')
                                        <i class="fa-solid fa-tv"></i> HLS {{ __('devices') }}
                                    @else
                                        <i class="fa-solid fa-computer"></i> DASH {{ __('devices') }}
                                    @endif
                                </span>
                            </td>
                        </tr>
                    @endif

                    <tr class="transition-all duration-200 align-middle border-l-4
                        {{ $item->is_available_in_store
                            ? 'border-l-emerald-400 bg-white dark:bg-gray-800 hover:bg-emerald-50/50 dark:hover:bg-emerald-900/10'
                            : 'border-l-red-400 bg-white dark:bg-gray-800 hover:bg-red-50/50 dark:hover:bg-red-900/10'
                        }}">
                        <td class="px-4 py-3.5 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-4">
                                <div class="relative flex-shrink-0">
                                    <img src="{{ $item->device?->image }}" alt="{{ $item->device?->name }}"
                                        class="w-11 h-11 object-contain rounded-xl p-1">
                                    <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full border-2 border-white dark:border-gray-800
                                        {{ $item->is_available_in_store ? 'bg-emerald-400' : 'bg-red-400' }}">
                                    </span>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-800 dark:text-white truncate">{{ $item->device?->name ?? __('Unknown device') }}</p>
                                    <div class="flex flex-wrap items-center gap-1.5 mt-1.5">

                                        @if ($protocol === 'HLS')
                                            <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-semibold text-blue-700 bg-blue-100 dark:bg-blue-900/40 dark:text-blue-300 rounded-full">
                                                <i class="fa-solid fa-tv mr-1"></i>HLS
                                            </span>
                                        @elseif ($protocol === 'DASH')
                                            <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-semibold text-indigo-700 bg-indigo-100 dark:bg-indigo-900/40 dark:text-indigo-300 rounded-full">
                                                <i class="fa-solid fa-computer mr-1"></i>DASH
                                            </span>
                                        @endif

                                        @if (strtoupper($item->device?->drm ?? '') === 'VERIMATRIX')
                                            <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-semibold text-pink-700 bg-pink-100 dark:bg-pink-900/40 dark:text-pink-300 rounded-full">
                                                <i class="fa-solid fa-certificate mr-1"></i>Verimatrix
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-semibold text-purple-700 bg-purple-100 dark:bg-purple-900/40 dark:text-purple-300 rounded-full">
                                                <i class="fa-brands fa-google mr-1"></i>Widevine
                                            </span>
                                        @endif

                                        @if (!empty($item->device?->store_url))
                                            <a href="{{ $item->device->store_url }}" target="_blank" rel="noopener noreferrer"
                                                class="inline-flex items-center px-2 py-0.5 text-[11px] font-semibold text-primary-700 bg-primary-100 dark:bg-primary-900/30 dark:text-primary-300 rounded-full hover:underline">
                                                <i class="fa-solid fa-arrow-up-right-from-square mr-1"></i>{{ __('Open store') }}
                                            </a>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-semibold text-gray-500 bg-gray-100 dark:bg-gray-700 dark:text-gray-400 rounded-full">
                                                <i class="fa-solid fa-link-slash mr-1"></i>{{ __('Not configured') }}
                                            </span>
                                        @endif

                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="px-4 py-3.5 border-b border-gray-200 dark:border-gray-700 text-center align-middle">
                            @if ($item->is_available_in_store)
                                <span class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 ring-1 ring-emerald-300 dark:ring-emerald-700">
                                    <i class="fa-solid fa-circle-check text-sm"></i>{{ __('Available') }}
                                </span>
                            @else
                                <span class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 ring-1 ring-red-300 dark:ring-red-700">
                                    <i class="fa-solid fa-circle-xmark text-sm"></i>{{ __('Not available') }}
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5 border-b border-gray-200 dark:border-gray-700 {{ blank($item->notes) ? 'text-center align-middle' : 'align-top' }}">
                            @if (blank($item->notes))
                                <span class="text-gray-400 dark:text-gray-500 italic text-xs">{{ __('No additional notes.') }}</span>
                            @else
                                <p class="text-gray-700 dark:text-gray-200 whitespace-pre-line leading-relaxed">{{ $item->notes }}</p>
                            @endif
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-14 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center gap-2">
                                <i class="fa-solid fa-circle-info text-2xl text-gray-400"></i>
                                <span>{{ __('No devices were registered in this report.') }}</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
