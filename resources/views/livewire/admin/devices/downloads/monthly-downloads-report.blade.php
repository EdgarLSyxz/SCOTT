<div class="space-y-4">
    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
        <div class="flex-1 flex flex-col sm:flex-row items-stretch sm:items-start gap-3">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="flex items-center gap-2 w-full sm:w-32">
                    <label class="sr-only">{{ __('Day') }}</label>
                    <select aria-label="Day" wire:model.live="day" class="cursor-pointer bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white {{ Auth::user()?->area === 'DTH' ? 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500 dark:focus:border-secondary-500'
                        : 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500' }}">
                        @foreach($days as $d)
                            <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-44">
                    <label class="sr-only">{{ __('Month') }}</label>
                    <select aria-label="Month" wire:model.live="month" class="cursor-pointer bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white {{ Auth::user()?->area === 'DTH' ? 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500 dark:focus:border-secondary-500'
                        : 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500' }}">
                        @foreach($months as $m)
                            <option value="{{ $m }}">{{ __(DateTime::createFromFormat('!m', $m)->format('F')) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-44">
                    <label class="sr-only">{{ __('Year') }}</label>
                    <select aria-label="Year" wire:model.live="year" class="cursor-pointer bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white {{ Auth::user()?->area === 'DTH' ? 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500 dark:focus:border-secondary-500'
                        : 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500' }}">
                        @foreach($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 w-full sm:w-auto">
                <div title="{{ __('Devices') }}" class="h-[44px] px-2 grid grid-cols-[28px_1fr_auto] items-center rounded-md bg-blue-50/40 dark:bg-sky-800/10 border border-blue-100 dark:border-sky-600 text-sm text-sky-800 dark:text-sky-200 hover:shadow-sm transition-shadow">
                    <div class="flex items-center justify-center text-sky-600 dark:text-sky-300">
                        <i class="fa-solid fa-microchip text-base"></i>
                    </div>
                    <div class="text-left text-xs uppercase tracking-wide font-medium text-sky-700 dark:text-sky-200 px-2">{{ __('Devices') }}</div>
                    <div class="text-right font-semibold text-base text-sky-800 dark:text-sky-200 ml-[6px]">{{ $devices->count() }}</div>
                </div>

                <div title="{{ __('Total downloads for month') }}" class="h-[44px] px-2 grid grid-cols-[28px_1fr_auto] items-center rounded-md bg-green-50/40 dark:bg-emerald-900/10 border border-green-200 dark:border-green-700 text-sm text-green-800 hover:shadow-sm transition-shadow">
                    <div class="flex items-center justify-center text-green-600 dark:text-green-300">
                        <i class="fa-solid fa-chart-column text-base"></i>
                    </div>
                    <div class="text-left text-xs uppercase tracking-wide font-medium text-green-700 dark:text-gray-200 px-2">{{ __('Total') }}</div>
                    <div class="text-right font-semibold text-base text-green-800 dark:text-green-300 ml-[6px]">{{ $this->total }}</div>
                </div>

                <div title="{{ __('Average downloads') }}" class="h-[44px] px-2 grid grid-cols-[28px_1fr_auto] items-center rounded-md bg-amber-50/40 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-700 text-sm text-amber-800 hover:shadow-sm transition-shadow">
                    <div class="flex items-center justify-center text-amber-600 dark:text-amber-300">
                        <i class="fa-solid fa-divide text-base"></i>
                    </div>
                    <div class="text-left text-xs uppercase tracking-wide font-medium text-amber-700  dark:text-amber-200 px-2">{{ __('Average') }}</div>
                    <div class="text-right font-semibold text-base text-amber-800 dark:text-amber-300 ml-[6px]">{{ $this->average }}</div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between lg:justify-end gap-3">
            <x-button wire:click="save" wire:target="save" class="w-full sm:w-auto">
                <i class="fa-solid fa-floppy-disk mr-1"></i>
                <span>{{ __('Save report') }}</span>
            </x-button>
        </div>
    </div>

    <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg mt-4 shadow-sm border border-gray-100 dark:border-gray-600 sm:mx-0">
        <table class="min-w-full text-sm text-left text-gray-600 dark:text-gray-300">
            <thead class="text-xs uppercase text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
                <tr>
                    <th class="px-3 sm:px-5 py-3 text-left w-2/3 truncate leading-tight">
                        <i class="fa-solid fa-hard-drive mr-1"></i>
                        {{ __('Device') }}
                    </th>
                    <th class="px-3 sm:px-5 py-3 text-left w-1/3 truncate leading-tight">
                        <i class="fa-solid fa-download mr-1"></i>
                        {{ __('Downloads') }}
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($devices as $device)
                    <tr>
                        <td class="px-3 sm:px-5 py-3">
                            <div class="flex items-center gap-2 sm:gap-3">
                                @php $img = $device->image ?? $device->image_url ?? $device->thumbnail ?? null; @endphp
                                <div class="w-6 h-6 sm:w-8 sm:h-8 overflow-hidden flex items-center justify-center flex-shrink-0">
                                    @if($img)
                                        <img src="{{ $img }}" alt="{{ $device->name }}" class="w-6 h-6 sm:w-8 sm:h-8 object-contain object-center" loading="lazy" />
                                    @else
                                        <i class="fa-solid fa-hard-drive text-gray-400 text-xs sm:text-base"></i>
                                    @endif
                                </div>

                                <div class="text-xs sm:text-sm font-medium text-gray-800 dark:text-gray-100 truncate">{{ $device->name }}</div>
                            </div>
                        </td>
                        <td class="px-3 sm:px-5 py-3 text-right">
                            <x-input
                                type="number"
                                min="0"
                                wire:model.live="counts.{{ $device->id }}"
                                inputmode="numeric"
                                pattern="\d*"
                                onfocus="this.select()"
                                onclick="this.select()"
                                onkeydown="if(['e','E','-','+','.'].includes(event.key)) event.preventDefault()"
                                oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                onpaste="setTimeout(()=>this.value=this.value.replace(/[^0-9]/g,''),0)"
                            />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if(!empty($competitorRanking))
        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <x-competitor-ranking-report
                :ranking="collect($competitorRanking)"
                :date="$reportDate"
                title="Ranking Competidor - Datos Ejecutivos"
            />
        </div>
    @endif
</div>
