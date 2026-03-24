<div class="mt-6">
    <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white flex items-center gap-2 mb-3">
        <i class="fa-solid fa-store"></i>
        <span class="truncate">{{ __('Startv Stream store availability') }}</span>
    </h3>

    <div class="relative overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm mt-6">
        <table class="min-w-full text-[11px] sm:text-sm text-left whitespace-nowrap">
            <thead class="sticky top-0 z-10 bg-white dark:bg-gray-800">
                <tr>
                    <th class="px-3 py-2 border-r border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 w-[280px]">
                        <i class="fa-solid fa-mobile-screen-button mr-2"></i>
                        {{ __('Device') }}
                    </th>
                    <th class="px-3 py-2 border-r border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 w-[150px] text-center">
                        <i class="fa-solid fa-power-off mr-2"></i>
                        {{ __('Active') }}
                    </th>
                    <th class="px-3 py-2 border-r border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 w-[180px] text-center">
                        <i class="fa-solid fa-store mr-2"></i>
                        {{ __('In store') }}
                    </th>
                    <th class="px-3 py-2 border-r border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 w-[220px]">
                        <i class="fa-solid fa-link mr-2"></i>
                        {{ __('Store URL') }}
                    </th>
                    <th class="px-3 py-2 text-gray-700 dark:text-gray-200">
                        <i class="fa-solid fa-comment-dots mr-2"></i>
                        {{ __('Notes') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report->deviceStoreAvailabilities->sortBy(fn($item) => $item->device->name ?? '') as $item)
                    <tr class="bg-white dark:bg-gray-800 hover:bg-primary-50 dark:hover:bg-gray-600 transition-all duration-200">
                        <td class="px-3 py-2 border-r border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <img src="{{ $item->device?->image }}" alt="{{ $item->device?->name }}" class="w-8 h-8 object-contain rounded shadow-sm">
                                <div>
                                    <p class="font-semibold text-gray-800 dark:text-white">{{ $item->device?->name ?? __('Unknown device') }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-300">
                                        {{ $item->device?->protocol ?? __('N/A') }} / {{ $item->device?->drm ?? __('N/A') }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-2 border-r border-gray-200 dark:border-gray-700 text-center">
                            @if ($item->is_active)
                                <span class="inline-flex items-center justify-center px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800 gap-1">
                                    <i class="fa-solid fa-circle-check"></i>{{ __('Active') }}
                                </span>
                            @else
                                <span class="inline-flex items-center justify-center px-2 py-1 rounded text-xs font-semibold bg-red-100 text-red-800 gap-1">
                                    <i class="fa-solid fa-circle-xmark"></i>{{ __('Inactive') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-2 border-r border-gray-200 dark:border-gray-700 text-center">
                            @if ($item->is_available_in_store)
                                <span class="inline-flex items-center justify-center px-2 py-1 rounded text-xs font-semibold bg-emerald-100 text-emerald-800 gap-1">
                                    <i class="fa-solid fa-bag-shopping"></i>{{ __('Available') }}
                                </span>
                            @else
                                <span class="inline-flex items-center justify-center px-2 py-1 rounded text-xs font-semibold bg-yellow-100 text-yellow-800 gap-1">
                                    <i class="fa-solid fa-store-slash"></i>{{ __('Not available') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-2 border-r border-gray-200 dark:border-gray-700">
                            @if (!empty($item->device?->store_url))
                                <a href="{{ $item->device->store_url }}" target="_blank" class="text-primary-600 hover:underline break-all">
                                    {{ $item->device->store_url }}
                                </a>
                            @else
                                <span class="text-gray-400">{{ __('Not configured') }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-gray-700 dark:text-gray-200 whitespace-normal">
                            {{ $item->notes ?: __('No additional notes.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
