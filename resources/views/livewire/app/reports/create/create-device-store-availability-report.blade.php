<div>
    <form wire:submit.prevent="saveReport" class="space-y-5">
        <div x-data="{ open: true }">
            <div class="p-4 md:p-6 border bg-white dark:bg-gray-800 rounded-2xl shadow-2xl">
                <div class="flex flex-wrap items-center justify-between cursor-pointer" @click="open = !open">
                    <div class="flex items-center gap-3 min-w-0">
                        <button type="button" class="text-primary-600 flex-shrink-0" @click.stop="open = !open">
                            <i :class="open ? 'fas fa-chevron-down' : 'fas fa-chevron-right'"></i>
                        </button>
                        <div class="dark:text-white text-lg font-semibold relative min-w-0">
                            <h3 class="cursor-pointer px-3 py-2 rounded-full shadow-md flex items-center gap-2 transition bg-gray-50 border dark:bg-gray-700 dark:border-white truncate max-w-[280px] md:max-w-md">
                                <i class="fa-solid fa-store text-gray-800 dark:text-gray-200"></i>
                                <span class="truncate">{{ __('StarTV Stream availability') }}</span>
                            </h3>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 transition-all duration-300">
                        <span class="bg-primary-100 text-primary-800 text-sm font-medium py-1 px-3 rounded-full">
                            {{ __('Contains') }} {{ count($reportData['devices']) }}
                            {{ count($reportData['devices']) === 1 ? __('Device') : __('Devices') }}
                        </span>
                    </div>
                </div>

                <div x-show="open" class="mt-6 space-y-6">
                    @foreach ($reportData['devices'] as $index => $deviceRow)
                        @php
                            $currentDevice = collect($devices)->firstWhere('id', $deviceRow['device_id']);
                        @endphp
                        <div class="p-4 md:p-6 bg-gray-50 border dark:bg-gray-700 rounded-xl shadow-2xl">
                            <div class="flex flex-wrap justify-between items-center mb-4 gap-4">
                                <h4 class="text-md font-semibold text-gray-800 dark:text-white">
                                    {{ __('Device') }} {{ $index + 1 }}
                                </h4>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                                <div>
                                    <input type="hidden" wire:model="reportData.devices.{{ $index }}.device_id">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">
                                        <i class="fa-solid fa-hard-drive mr-1.5"></i> {{ __('Device') }}
                                    </label>

                                    <div class="relative">
                                        <input type="text" disabled
                                            value="{{ $currentDevice['name'] ?? __('Unknown device') }}"
                                            class="w-full px-4 py-2 pl-14 rounded-lg bg-gray-100 border border-gray-300 dark:bg-gray-700 dark:text-white cursor-not-allowed">
                                        <div class="absolute left-3 top-1/2 -translate-y-1/2 flex items-center">
                                            @if (!empty($currentDevice['image']))
                                                <img src="{{ $currentDevice['image'] }}" class="w-8 h-8 object-contain object-center">
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap gap-2 text-xs mt-2 ml-1">
                                        @if(strtoupper($currentDevice['protocol']) === 'HLS')
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-800 bg-blue-200 dark:bg-blue-800 dark:text-blue-200 rounded-full">
                                                <i class="fa-solid fa-tv mr-1.5"></i>
                                                {{ __('HLS') }}
                                            </span>
                                        @elseif(strtoupper($currentDevice['protocol']) === 'DASH')
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-800 bg-blue-200 dark:bg-blue-800 dark:text-blue-200 rounded-full">
                                                <i class="fa-solid fa-computer mr-1.5"></i>
                                                {{ __('DASH') }}
                                            </span>
                                        @endif
                                        @if(strtoupper($currentDevice['drm']) === 'VERIMATRIX')
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
                                        @if (!empty($currentDevice['store_url']))
                                            <a href="{{ $currentDevice['store_url'] }}" target="_blank"
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-emerald-800 bg-emerald-200 dark:bg-emerald-800 dark:text-emerald-200 rounded-full hover:underline">
                                                <i class="fa-solid fa-arrow-up-right-from-square mr-1.5"></i>
                                                {{ __('Store link') }}
                                            </a>
                                        @endif
                                    </div>

                                    @error('reportData.devices.' . $index . '.device_id')
                                        <span class="text-red-600 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="flex items-center gap-4 rounded-lg border border-gray-200 dark:border-gray-600 p-4 bg-white dark:bg-gray-800 cursor-pointer">
                                        <input type="checkbox" wire:model="reportData.devices.{{ $index }}.is_available_in_store"
                                            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800 dark:text-white mb-2">{{ __('Available in app store') }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-300">{{ __('Confirms the Startv Stream app is currently listed in the platform store.') }}</p>
                                        </div>
                                    </label>
                                    @error('reportData.devices.' . $index . '.is_available_in_store')
                                        <span class="text-red-600 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">
                                        <i class="fa-solid fa-comment-dots mr-1.5"></i> {{ __('Notes') }}
                                    </label>
                                    <textarea wire:model="reportData.devices.{{ $index }}.notes" rows="5"
                                        class="w-full rounded-lg bg-gray-50 border border-gray-300 dark:bg-gray-700 dark:text-white focus:ring-primary-600 focus:border-primary-600 cursor-pointer"
                                        placeholder="{{ __('Optional notes about the app status in the store...') }}"></textarea>
                                    @error('reportData.devices.' . $index . '.notes')
                                        <span class="text-red-600 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-col md:flex-row justify-end gap-4 mt-6">
                <button type="submit"
                    class="w-full md:w-auto py-2 px-4 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-bold text-base">
                    <i class="fas fa-file-lines mr-1.5"></i> {{ __('Generate report') }}
                </button>
                <button data-modal-hide="create-device-store-report-modal" type="button"
                    class="py-2 px-4 text-base font-bold text-gray-700 bg-white rounded-lg border border-gray-400 hover:border-primary-600 hover:text-primary-600 dark:text-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:hover:text-primary-400 dark:hover:bg-gray-700">
                    <i class="fa-solid fa-xmark"></i>
                    {{ __('Discard') }}
                </button>
            </div>
        </div>
    </form>
</div>
