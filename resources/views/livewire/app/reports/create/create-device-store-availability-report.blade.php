<div>
    <form wire:submit.prevent="saveReport" class="space-y-5">
        <div x-data="{ open: true }">
            <div class="p-4 md:p-6 border bg-white dark:bg-gray-800 rounded-2xl shadow-2xl">
                <div class="flex flex-wrap items-center justify-between cursor-pointer gap-3 md:gap-0" @click="open = !open">
                    <div class="flex items-center gap-2 md:gap-3 min-w-0">
                        <button type="button" class="text-primary-600 flex-shrink-0 text-lg md:text-base" @click.stop="open = !open">
                            <i :class="open ? 'fas fa-chevron-down' : 'fas fa-chevron-right'"></i>
                        </button>
                        <div class="dark:text-white font-semibold relative min-w-0">
                            <h3 class="cursor-pointer px-2 md:px-3 py-1 md:py-2 rounded-full shadow-md flex items-center gap-1 md:gap-2 transition bg-gray-50 border dark:bg-gray-700 dark:border-white truncate text-sm md:text-base">
                                <i class="fa-solid fa-store text-gray-800 dark:text-gray-200 text-sm md:text-base"></i>
                                <span class="truncate">{{ __('StarTV Stream availability') }}</span>
                            </h3>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 md:gap-3 transition-all duration-300">
                        <span class="bg-primary-100 text-primary-800 text-xs md:text-sm font-medium py-1 px-2 md:px-3 rounded-full">
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
                        <div class="p-3 md:p-6 bg-gray-50 border dark:bg-gray-700 rounded-xl shadow-2xl">
                            <div class="flex flex-wrap justify-between items-center mb-3 md:mb-4 gap-2 md:gap-4">
                                <h4 class="text-sm md:text-base font-semibold text-gray-800 dark:text-white">
                                    {{ __('Device') }} {{ $index + 1 }}
                                </h4>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4 items-start md:items-center">
                                <div>
                                    <input type="hidden" wire:model="reportData.devices.{{ $index }}.device_id">
                                    <label class="block text-xs md:text-sm font-medium text-gray-700 dark:text-white mb-2">
                                        <i class="fa-solid fa-hard-drive mr-1.5"></i> {{ __('Device') }}
                                    </label>

                                    <div class="relative">
                                        <input type="text" disabled
                                            value="{{ $currentDevice['name'] ?? __('Unknown device') }}"
                                            class="w-full px-3 md:px-4 py-1.5 md:py-2 pl-10 md:pl-14 rounded-lg bg-gray-100 border border-gray-300 dark:bg-gray-700 dark:text-white cursor-not-allowed text-sm md:text-base">
                                        <div class="absolute left-2 md:left-3 top-1/2 -translate-y-1/2 flex items-center">
                                            @if (!empty($currentDevice['image']))
                                                <img src="{{ $currentDevice['image'] }}" class="w-6 md:w-8 h-6 md:h-8 object-contain object-center">
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap gap-1 md:gap-2 text-xs mt-2 ml-0 md:ml-1">
                                        @if(strtoupper($currentDevice['protocol']) === 'HLS')
                                            <span
                                                class="inline-flex items-center px-1.5 md:px-2 py-0.5 md:py-1 text-xs font-medium text-blue-800 bg-blue-200 dark:bg-blue-800 dark:text-blue-200 rounded-full">
                                                <i class="fa-solid fa-tv mr-1"></i>
                                                {{ __('HLS') }}
                                            </span>
                                        @elseif(strtoupper($currentDevice['protocol']) === 'DASH')
                                            <span
                                                class="inline-flex items-center px-1.5 md:px-2 py-0.5 md:py-1 text-xs font-medium text-blue-800 bg-blue-200 dark:bg-blue-800 dark:text-blue-200 rounded-full">
                                                <i class="fa-solid fa-computer mr-1"></i>
                                                {{ __('DASH') }}
                                            </span>
                                        @endif
                                        @if(strtoupper($currentDevice['drm']) === 'VERIMATRIX')
                                            <span
                                                class="inline-flex items-center px-1.5 md:px-2 py-0.5 md:py-1 text-xs font-medium text-pink-800 bg-pink-200 dark:bg-pink-800 dark:text-pink-200 rounded-full">
                                                <i class="fa-solid fa-certificate mr-1"></i>
                                                {{ __('Verimatrix') }}
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-1.5 md:px-2 py-0.5 md:py-1 text-xs font-medium text-pink-800 bg-pink-200 dark:bg-pink-800 dark:text-pink-200 rounded-full">
                                                <i class="fa-brands fa-google mr-1"></i>
                                                {{ __('Widevine') }}
                                            </span>
                                        @endif
                                        @if (!empty($currentDevice['store_url']))
                                            <a href="{{ $currentDevice['store_url'] }}" target="_blank"
                                                class="inline-flex items-center px-1.5 md:px-2 py-0.5 md:py-1 text-xs font-medium text-emerald-800 bg-emerald-200 dark:bg-emerald-800 dark:text-emerald-200 rounded-full hover:underline">
                                                <i class="fa-solid fa-arrow-up-right-from-square mr-1"></i>
                                                {{ ($currentDevice['name'] ?? null) === 'Web Client' ? __('Website') : __('Store link') }}
                                            </a>
                                        @endif
                                    </div>

                                    @error('reportData.devices.' . $index . '.device_id')
                                        <span class="text-red-600 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div x-data="{ isChecked: false }">
                                    <input type="checkbox" wire:model.live="reportData.devices.{{ $index }}.is_available_in_store"
                                        @change="isChecked = $el.checked"
                                        class="hidden peer" id="availability_{{ $index }}">
                                    <label for="availability_{{ $index }}"
                                        class="group flex items-center gap-2 md:gap-4 rounded-xl border p-3 md:p-4 transition-all duration-200 shadow-sm cursor-pointer border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-700 hover:border-slate-400 dark:hover:border-slate-500 peer-checked:border-emerald-400 dark:peer-checked:border-emerald-600 peer-checked:bg-emerald-100 dark:peer-checked:bg-emerald-900/30 peer-checked:hover:border-emerald-500 dark:peer-checked:hover:border-emerald-500">
                                        <div :class="{
                                            'h-5 w-5 md:h-6 md:w-6 rounded border-2 flex items-center justify-center transition-all flex-shrink-0': true,
                                            'border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-700': !isChecked,
                                            'border-emerald-500 dark:border-emerald-500 bg-emerald-600': isChecked,
                                        }">
                                            <svg x-show="isChecked" class="w-3 h-3 md:w-4 md:h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="transition-colors duration-200 min-w-0">
                                            <p class="text-xs md:text-sm font-semibold mb-1 md:mb-2 text-slate-700 dark:text-slate-200 peer-checked:text-emerald-700 dark:peer-checked:text-emerald-300">
                                                {{ ($currentDevice['name'] ?? null) === 'Web Client' ? __('Available in browser') : __('Available in app store') }}
                                            </p>
                                            <p class="text-xs text-slate-500 dark:text-slate-300 peer-checked:text-emerald-600 dark:peer-checked:text-emerald-400 leading-tight">
                                                {{ ($currentDevice['name'] ?? null) === 'Web Client' ? __('Confirms StarTV Stream is available through the web browser.') : __('Confirms the StarTV Stream app is currently listed in the platform store.') }}
                                            </p>
                                        </div>
                                    </label>
                                    @error('reportData.devices.' . $index . '.is_available_in_store')
                                        <span class="text-red-600 text-xs md:text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-3 md:mt-4 grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-xs md:text-sm font-medium text-gray-700 dark:text-white mb-2">
                                        <i class="fa-solid fa-comment-dots mr-1.5"></i> {{ __('Notes') }}
                                    </label>
                                    <textarea wire:model="reportData.devices.{{ $index }}.notes" rows="4"
                                        class="w-full rounded-lg bg-gray-50 border border-gray-300 dark:bg-gray-700 dark:text-white focus:ring-primary-600 focus:border-primary-600 cursor-pointer text-sm"
                                        placeholder="{{ __('Optional notes about the app status in the store...') }}"></textarea>
                                    @error('reportData.devices.' . $index . '.notes')
                                        <span class="text-red-600 text-xs md:text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-col-reverse md:flex-row justify-end gap-2 md:gap-4 mt-4 md:mt-6">
                <button data-modal-hide="create-device-store-report-modal" type="button"
                    class="py-2 px-3 md:px-4 text-sm md:text-base font-bold text-gray-700 bg-white rounded-lg border border-gray-400 hover:border-primary-600 hover:text-primary-600 dark:text-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:hover:text-primary-400 dark:hover:bg-gray-700 transition-all">
                    <i class="fa-solid fa-xmark"></i>
                    {{ __('Discard') }}
                </button>
                <button type="submit"
                    class="w-full md:w-auto py-2 px-3 md:px-4 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-bold text-sm md:text-base transition-all">
                    <i class="fas fa-file-lines mr-1.5"></i> {{ __('Generate report') }}
                </button>
            </div>
        </div>
    </form>
</div>
