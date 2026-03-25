@php
    $rows = collect($report->deviceStoreAvailabilities ?? [])->sortBy(fn($item) => $item->device->name ?? '')->values();
    $total = $rows->count();
    $availableCount = $rows->where('is_available_in_store', true)->count();
    $notAvailableCount = $rows->where('is_available_in_store', false)->count();
    $withoutNotesCount = $rows->filter(fn($item) => blank($item->notes))->count();
@endphp

<div class="mt-6 space-y-4">
    <div class="rounded-2xl border border-emerald-200 dark:border-emerald-700 bg-gradient-to-r from-emerald-50 via-white to-cyan-50 dark:from-gray-800 dark:via-gray-800 dark:to-gray-700 p-4 sm:p-5 shadow-sm">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-store text-emerald-600"></i>
                    <span>{{ __('Availability report') }}</span>
                </h3>
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 mt-1">
                    {{ __('Quick overview of store publication status by device.') }}
                </p>
            </div>
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/80 dark:bg-gray-900/40 border border-emerald-300 dark:border-emerald-600 text-emerald-800 dark:text-emerald-300 text-xs font-semibold">
                <i class="fa-solid fa-list-check"></i>
                {{ __('Total devices') }}: {{ $total }}
            </span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4">
            <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-3">
                <p class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Available') }}</p>
                <p class="text-xl font-bold text-emerald-600">{{ $availableCount }}</p>
            </div>
            <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-3">
                <p class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Not available') }}</p>
                <p class="text-xl font-bold text-amber-600">{{ $notAvailableCount }}</p>
            </div>
            <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-3">
                <p class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('No notes') }}</p>
                <p class="text-xl font-bold text-slate-700 dark:text-slate-200">{{ $withoutNotesCount }}</p>
            </div>
            <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-3">
                <p class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Coverage') }}</p>
                <p class="text-xl font-bold text-cyan-700 dark:text-cyan-300">
                    {{ $total > 0 ? round(($availableCount / $total) * 100) : 0 }}%
                </p>
            </div>
        </div>
    </div>

    @if ($notAvailableCount > 0)
        <div class="rounded-xl border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 px-4 py-3 text-amber-900 dark:text-amber-200 text-sm flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
            <span>{{ __('There are devices where the app is not currently available in store. Review each row for details.') }}</span>
        </div>
    @endif

    <div class="relative overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <table class="min-w-full text-xs sm:text-sm text-left">
            <thead class="bg-gray-50 dark:bg-gray-800/90">
                <tr>
                    <th class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 w-[320px]">
                        <i class="fa-solid fa-hard-drive mr-2"></i>
                        {{ __('Device') }}
                    </th>
                    <th class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 w-[180px] text-center">
                        <i class="fa-solid fa-store mr-2"></i>
                        {{ __('Status') }}
                    </th>
                    <th class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200">
                        <i class="fa-solid fa-comment-dots mr-2"></i>
                        {{ __('Notes') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $item)
                    <tr class="bg-white dark:bg-gray-800 odd:bg-white even:bg-gray-50/70 dark:odd:bg-gray-800 dark:even:bg-gray-700/40 hover:bg-primary-50 dark:hover:bg-gray-600 transition-all duration-200 align-top">
                        <td class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <img src="{{ $item->device?->image }}" alt="{{ $item->device?->name }}" class="w-10 h-10 object-contain rounded-lg p-1 shadow-sm">
                                <div>
                                    <p class="font-semibold text-gray-800 dark:text-white">{{ $item->device?->name ?? __('Unknown device') }}</p>
                                    <div class="flex flex-wrap gap-2 text-xs mt-2 ml-1">
                                        @if(strtoupper($item->device?->protocol) === 'HLS')
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-800 bg-blue-200 dark:bg-blue-800 dark:text-blue-200 rounded-full">
                                                <i class="fa-solid fa-tv mr-1.5"></i>
                                                {{ __('HLS') }}
                                            </span>
                                        @elseif(strtoupper($item->device?->protocol) === 'DASH')
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-800 bg-blue-200 dark:bg-blue-800 dark:text-blue-200 rounded-full">
                                                <i class="fa-solid fa-computer mr-1.5"></i>
                                                {{ __('DASH') }}
                                            </span>
                                        @endif
                                        @if(strtoupper($item->device?->drm) === 'VERIMATRIX')
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-pink-800 bg-pink-200 dark:bg-pink-800 dark:text-pink-200 rounded-full">
                                                <i class="fa-solid fa-certificate mr-1.5"></i>
                                                {{ __('Verimatrix') }}
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-pink-800 bg-pink-200 dark:bg-pink-800 dark:text-pink-200 rounded-full">
                                                <i class="fa-brands fa-google mr-1.5"></i>
                                                {{ __('Widevine') }}
                                            </span>
                                        @endif
                                        @if (!empty($item->device?->store_url))
                                            <a href="{{ $item->device->store_url }}" target="_blank" rel="noopener noreferrer"
                                                class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300 hover:underline">
                                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                                {{ __('Open store') }}
                                            </a>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                                <i class="fa-solid fa-link-slash"></i>
                                                {{ __('Not configured') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 text-center">
                            @if ($item->is_available_in_store)
                                <span class="inline-flex items-center justify-center px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 gap-2">
                                    <i class="fa-solid fa-bag-shopping"></i>{{ __('Available') }}
                                </span>
                            @else
                                <span class="inline-flex items-center justify-center px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 gap-2">
                                    <i class="fa-solid fa-store-slash"></i>{{ __('Not available') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 whitespace-normal max-w-[420px]">
                            @if (blank($item->notes))
                                <span class="text-gray-400 italic">{{ __('No additional notes.') }}</span>
                            @else
                                {{ $item->notes }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-10 text-center text-gray-500 dark:text-gray-300">
                            <i class="fa-solid fa-circle-info mr-2"></i>
                            {{ __('No devices were registered in this report.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
