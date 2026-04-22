@php
$area = Auth::user()?->area ?? 'OTT';
$isDth = $area === 'DTH';
$iconColor = $isDth ? 'text-secondary-600 dark:text-secondary-400' : 'text-primary-600 dark:text-primary-400';
$focusRing = $isDth ? 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500 dark:focus:border-secondary-500' : 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500';
@endphp

<div wire:poll.10s class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
    <div class="flex items-center justify-between px-4 py-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 tracking-wide">
            <i class="fa-solid fa-bolt mr-2 text-secondary-600 dark:text-secondary-400"></i>{{ __('Emergency power and fuel monitor') }}
        </h3>
        <span class="text-xs text-gray-400 dark:text-gray-500 md:block hidden">{{ __('Current status') }}</span>
    </div>

    <div class="p-4 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
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
                    {{ __('Notes (optional)') }}
                </label>
                <x-input type="text" wire:model.defer="notes" placeholder="{{ __('Example: Correct switch to CFE/Power Plant') }}"
                    class="rounded-md" />
            </div>
        </div>

        <div class="flex justify-end">
            <x-button type="button" wire:click="updatePowerSource">
                <i class="fa-solid fa-floppy-disk mr-2"></i>{{ __('Save') }}
            </x-button>
        </div>

        @if($latestEvent)
            @php
    $latestIsPowerPlant = in_array($latestEvent->power_source, ['Power Plant', 'Planta Electrica'], true);
            @endphp
            <div class="rounded-lg border border-gray-200 dark:border-gray-600 p-3 bg-gray-50 dark:bg-gray-700/40 space-y-2">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ __('Current status') }}:
                        <span class="{{ $latestIsPowerPlant ? 'text-amber-600' : 'text-emerald-600' }}">{{ $latestIsPowerPlant ? __('Power Plant') : 'CFE' }}</span>
                    </p>
                    <span class="text-xs text-gray-500 dark:text-gray-300">{{ __('Elapsed time') }}: {{ $this->elapsedHuman }}</span>
                </div>

                @if($this->showFuelAlert)
                    <div class="rounded-lg px-3 py-2 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 text-xs font-semibold">
                        <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                        @if($latestIsPowerPlant)
                            {{ __('More than 8 hours have passed on Power Plant. Fuel level check is required.') }}
                        @else
                            {{ __('More than 8 hours have passed since the last change to CFE. It is recommended to check emergency Power Plant fuel level.') }}
                        @endif
                    </div>
                @endif

                <p class="text-xs text-gray-500 dark:text-gray-300">
                    {{ __('Last change') }}: {{ optional($latestEvent->changed_at)->format('d/m/Y H:i:s') }}
                    @if($latestEvent->user)
                        · {{ $latestEvent->user->name }}
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
                            <td class="px-3 py-2 font-semibold {{ $eventIsPowerPlant ? 'text-amber-600' : 'text-emerald-600' }}">{{ $eventIsPowerPlant ? __('Power Plant') : 'CFE' }}</td>
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
