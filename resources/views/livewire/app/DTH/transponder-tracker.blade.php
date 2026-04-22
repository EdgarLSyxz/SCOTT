@php
    $area = Auth::user()?->area ?? 'OTT';
    $isDth = $area === 'DTH';
    $iconColor = $isDth ? 'text-secondary-600 dark:text-secondary-400' : 'text-primary-600 dark:text-primary-400';
    $focusRing = $isDth ? 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500 dark:focus:border-secondary-500' : 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500';
    $previewIconColor = $isDth ? 'text-secondary-500 dark:text-secondary-300' : 'text-primary-500 dark:text-primary-300';
    $previewTooltipBorder = $isDth ? 'border-secondary-200 dark:border-secondary-700' : 'border-primary-200 dark:border-primary-700';
    $previewTooltipBg = $isDth ? 'bg-secondary-50 dark:bg-secondary-900/80' : 'bg-primary-50 dark:bg-primary-900/80';
    $previewTooltipTitle = $isDth ? 'text-secondary-600 dark:text-secondary-300' : 'text-primary-600 dark:text-primary-300';
    $previewTooltipText = $isDth ? 'text-secondary-900 dark:text-secondary-100' : 'text-primary-900 dark:text-primary-100';
    $descriptionText = $isDth ? 'text-secondary-800 dark:text-secondary-200' : 'text-primary-800 dark:text-primary-200';
@endphp

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
    <div class="flex items-center justify-between px-4 py-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 tracking-wide">
            <i class="fa-solid fa-satellite-dish mr-2 {{ $iconColor }}"></i>{{ __('DTH Transponders') }}
        </h3>
        <span class="text-xs text-gray-400 dark:text-gray-500 md:block hidden">{{ __('Current status') }}</span>
    </div>

    <div class="p-4 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">
                    <i class="fa-solid fa-location-dot mr-1.5 mb-2"></i>
                    {{ __('UP-LINK Site') }}
                </label>
                <select wire:model.live="upLinkSite"
                    class="bg-gray-50 border border-gray-300 text-gray-900 rounded-md focus:ring-2 {{ $focusRing }} block w-full py-[11px] px-2 text-sm dark:bg-gray-700 dark:text-white transition-all truncate leading-tight">
                    @foreach($sites as $site)
                        <option value="{{ $site }}">{{ $site }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">
                    <i class="fa-solid fa-satellite-dish mr-1.5 mb-2"></i>
                    {{ __('Transponders list') }}
                </label>
                <div class="flex items-center gap-2 w-full">
                    <div class="w-full">
                        <x-input type="text" wire:model.defer="transponders" placeholder="{{ __('Example: KU01, KU03, KU05') }}"
                            class="rounded-md" />
                    </div>

                    <div class="w-auto flex justify-end ml-1">
                        <div
                            x-data="{ show: false }"
                            @mouseenter="show = true"
                            @mouseleave="show = false"
                            class="relative h-10 inline-flex items-center"
                        >
                            <div class="bg-gray-50 border border-gray-300 dark:bg-gray-700 dark:border-gray-600 rounded-md h-10 w-10 flex items-center justify-center cursor-default select-none">
                                <i class="fa-solid fa-circle-info text-gray-600 dark:text-gray-300 text-sm"></i>
                            </div>
                            <div
                                x-show="show"
                                x-transition:enter="transform transition duration-250 ease-out"
                                x-transition:enter-start="opacity-0 translate-x-1 -translate-y-1 scale-95"
                                x-transition:enter-end="opacity-100 translate-x-0 -translate-y-1/2 scale-100"
                                x-transition:leave="transform transition duration-180 ease-in"
                                x-transition:leave-start="opacity-100 translate-x-0 -translate-y-1/2 scale-100"
                                x-transition:leave-end="opacity-0 translate-x-1 -translate-y-1 scale-95"
                                class="absolute right-full mr-2 top-1/2 -translate-y-1/2 z-20 w-72 rounded-lg border {{ $previewTooltipBorder }} {{ $previewTooltipBg }} shadow-lg px-3 py-2"
                                style="display: none;"
                            >
                                <p class="text-xs {{ $previewTooltipTitle }} font-semibold mb-1">{{ __('Live description preview') }}</p>
                                <p class="text-sm {{ $previewTooltipText }} break-words">{{ $this->currentDescription }}</p>
                                <div class="absolute right-[-6px] top-1/2 -translate-y-1/2 w-3 h-3 rotate-45 {{ $previewTooltipBg }} border-t border-r {{ $previewTooltipBorder }}"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <x-button type="button" wire:click="saveRecord">
                <i class="fa-solid fa-floppy-disk mr-2"></i>{{ __('Save') }}
            </x-button>
        </div>

        @if($latestRecord)
            <div class="rounded-lg border border-gray-200 dark:border-gray-600 p-3 bg-gray-50 dark:bg-gray-700/40">
                <p class="text-xs text-gray-500 dark:text-gray-300">{{ __('Current status') }}</p>
                <p class="text-sm font-semibold {{ $descriptionText }} mt-1">{{ $latestRecord->description }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-300 mt-1">
                    {{ optional($latestRecord->recorded_at)->format('d/m/Y H:i:s') }}
                    @if($latestRecord->user)
                        · {{ $latestRecord->user->name }}
                    @endif
                </p>
            </div>
        @endif

        <div class="overflow-x-auto border border-gray-200 dark:border-gray-600 rounded-lg">
            <table class="min-w-[680px] w-full text-xs sm:text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-200 uppercase text-xs">
                    <tr>
                        <th class="px-3 py-2">
                            <i class="fa-solid fa-calendar mr-1.5"></i>
                            {{ __('Date') }}
                        </th>
                        <th class="px-3 py-2">
                            <i class="fa-solid fa-location-dot mr-1.5"></i>
                            {{ __('Site') }}
                        </th>
                        <th class="px-3 py-2">
                            <i class="fa-solid fa-satellite-dish mr-1.5"></i>
                            {{ __('Transponders') }}
                        </th>
                        <th class="px-3 py-2">
                            <i class="fa-solid fa-user mr-1.5"></i>
                            {{ __('User') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-600 bg-white dark:bg-gray-800">
                    @forelse($history as $item)
                        <tr>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ optional($item->recorded_at)->format('d/m/Y H:i:s') }}</td>
                            <td class="px-3 py-2 {{ $descriptionText }} font-semibold">{{ $item->up_link_site }}</td>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $item->description }}</td>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $item->user?->name ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-5 text-center text-gray-500 dark:text-gray-400">{{ __('No records yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
