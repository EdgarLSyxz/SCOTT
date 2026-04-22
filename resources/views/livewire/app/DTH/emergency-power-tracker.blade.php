<div wire:poll.10s class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden">
    <div class="px-4 py-3 bg-gray-100 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 uppercase tracking-wide">
            <i class="fa-solid fa-bolt mr-2"></i>{{ __('Emergency power and fuel monitor') }}
        </h3>
        <p class="text-xs text-gray-500 dark:text-gray-300 mt-1">{{ __('Real-time monitoring of CFE and backup power plant status.') }}</p>
    </div>

    <div class="p-4 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">{{ __('Power source') }}</label>
                <select wire:model="powerSource"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white">
                    <option value="CFE">CFE</option>
                    <option value="Power Plant">{{ __('Power Plant') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">{{ __('Notes (optional)') }}</label>
                <x-input type="text" wire:model.defer="notes" placeholder="{{ __('Example: Scheduled transfer') }}"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm" />
            </div>
        </div>

        <div class="flex justify-end">
            <button type="button" wire:click="updatePowerSource"
                class="w-full sm:w-auto text-white bg-amber-600 hover:bg-amber-700 focus:ring-4 focus:ring-amber-300 font-medium rounded-lg text-sm px-4 py-2">
                <i class="fa-solid fa-rotate mr-1"></i>{{ __('Save change') }}
            </button>
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
                            {{ __('More than 8 hours have passed since the last change to CFE. It is recommended to check emergency plant fuel level.') }}
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
            <table class="min-w-[640px] w-full text-xs sm:text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-200 uppercase text-xs">
                    <tr>
                        <th class="px-3 py-2">{{ __('Date') }}</th>
                        <th class="px-3 py-2">{{ __('Status') }}</th>
                        <th class="px-3 py-2">{{ __('Notes') }}</th>
                        <th class="px-3 py-2">{{ __('User') }}</th>
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
                            <td colspan="4" class="px-3 py-5 text-center text-gray-500 dark:text-gray-400">{{ __('No records yet') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
