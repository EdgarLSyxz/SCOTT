@php
    $area = Auth::user()?->area ?? 'OTT';
    $isDth = $area === 'DTH';
    $iconColor = $isDth ? 'text-secondary-600 dark:text-secondary-400' : 'text-primary-600 dark:text-primary-400';
    $focusRing = $isDth ? 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500 dark:focus:border-secondary-500' : 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500';
    $descriptionText = $isDth ? 'text-secondary-800 dark:text-secondary-200' : 'text-primary-800 dark:text-primary-200';
@endphp

<div wire:poll.10s class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
    <div class="flex items-center justify-between px-4 py-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 tracking-wide truncate leading-tight">
            <i class="fa-solid fa-bolt mr-2 text-secondary-600 dark:text-secondary-400"></i>{{ __('Emergency power and fuel monitor') }}
        </h3>
        <span class="text-xs text-gray-400 dark:text-gray-500 md:block hidden">{{ __('Current status') }}</span>
    </div>

    <div class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-1">
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">
                    <i class="fa-solid fa-bolt mr-1.5 mb-2"></i>
                    {{ __('Power source') }}
                </label>
                <select wire:model="powerSource"
                    class="bg-gray-50 border border-gray-300 text-gray-900 rounded-md focus:ring-2 focus:ring-secondary-500 focus:border-secondary-500 block w-full py-2 px-2 text-sm dark:bg-gray-700 dark:text-white transition-all h-10 truncate leading-tight">
                    <option value="CFE">CFE</option>
                    <option value="Power Plant">{{ __('Power Plant') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">
                    <i class="fa-solid fa-note-sticky mr-1.5 mb-2"></i>
                    {{ __('Notes') }}
                </label>
                <x-input type="text" wire:model.defer="notes" placeholder="{{ __('Example: Correct switch to CFE/Power Plant') }}"
                    class="rounded-md" required />
                @error('notes')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex justify-end mt-4">
            <x-button type="button" wire:click="updatePowerSource">
                <i class="fa-solid fa-floppy-disk mr-2"></i>{{ __('Save') }}
            </x-button>
        </div>

        @if($latestEvent)
            @php
                $latestIsPowerPlant = in_array($latestEvent->power_source, ['Power Plant', 'Planta Electrica'], true);
                $showElapsedAlert = $latestIsPowerPlant && $this->showFuelAlert;
            @endphp
            <div class="rounded-lg mt-4 border border-gray-200 dark:border-gray-600 p-3 bg-gray-50 dark:bg-gray-700/40">
                <p class="text-xs text-gray-500 dark:text-gray-300">{{ __('Current status') }}</p>
                <div class="{{ $showElapsedAlert ? '' : 'mt-1' }}">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm font-semibold {{ $descriptionText }}">
                            {{ $latestIsPowerPlant ? __('Power Plant') : 'CFE' }}
                        </p>

                        @if($showElapsedAlert)
                            <div class="inline-flex items-center gap-2 rounded-full border border-red-200/80 dark:border-red-800/70 px-2 py-1 bg-red-50/95 dark:bg-red-950/35 text-red-700 dark:text-red-200 text-[11px] font-semibold whitespace-nowrap shadow-sm sm:self-center">
                                <span class="inline-flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-red-200/80 dark:bg-red-800/70 text-red-700 dark:text-red-100 ring-1 ring-inset ring-red-300/60 dark:ring-red-700/60">
                                    <i class="fa-solid fa-triangle-exclamation text-[8px]"></i>
                                </span>
                                <span class="font-medium tabular-nums text-[11px]">{{ __('Elapsed time') }}: {{ $this->elapsedHuman }}</span>
                                <span class="opacity-40 text-[11px]">•</span>
                                <span class="font-medium text-[11px]">{{ __('Review fuel level') }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between {{ $showElapsedAlert ? '' : 'mt-1' }}">
                        <p class="text-xs text-gray-500 dark:text-gray-300">
                            {{ __('Last change') }}: {{ optional($latestEvent->changed_at)->format('d/m/Y H:i:s') }}
                            @if($latestEvent->user)
                                · {{ $latestEvent->user->name }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="overflow-x-auto border border-gray-200 dark:border-gray-600 rounded-lg mt-4">
            <table class="min-w-[680px] w-full text-xs sm:text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-200 uppercase text-xs">
                    <tr>
                        <th class="px-3 py-2">
                            <i class="fa-solid fa-calendar mr-1.5"></i>
                            {{ __('Date') }}
                        </th>
                        <th class="px-3 py-2">
                            <i class="fa-solid fa-bolt mr-1.5"></i>
                            {{ __('Status') }}
                        </th>
                        <th class="px-3 py-2">
                            <i class="fa-solid fa-sticky-note mr-1.5"></i>
                            {{ __('Notes') }}
                        </th>
                        <th class="px-3 py-2">
                            <i class="fa-solid fa-user mr-1.5"></i>
                            {{ __('User') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-600 bg-white dark:bg-gray-800">
                    @forelse($history as $event)
                        @php
                            $eventIsPowerPlant = in_array($event->power_source, ['Power Plant', 'Planta Electrica'], true);
                        @endphp
                        <tr>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ optional($event->changed_at)->format('d/m/Y H:i:s') }}</td>
                            <td class="px-3 py-2 font-semibold {{ $descriptionText }}">{{ $eventIsPowerPlant ? __('Power Plant') : 'CFE' }}</td>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $event->notes ?: '—' }}</td>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $event->user?->name ?: '—' }}</td>
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
