<div wire:poll.10s class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden">
    <div class="px-4 py-3 bg-gray-100 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 uppercase tracking-wide">
            <i class="fa-solid fa-bolt mr-2"></i>Energia y combustible - Planta de emergencia
        </h3>
        <p class="text-xs text-gray-500 dark:text-gray-300 mt-1">Seguimiento en tiempo real de CFE vs Planta Electrica.</p>
    </div>

    <div class="p-4 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Fuente de energia</label>
                <select wire:model="powerSource"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white">
                    <option value="CFE">CFE</option>
                    <option value="Planta Electrica">Planta Electrica</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Observaciones (opcional)</label>
                <x-input type="text" wire:model.defer="notes" placeholder="Ej. Transferencia programada"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm" />
            </div>
        </div>

        <div class="flex justify-end">
            <button type="button" wire:click="updatePowerSource"
                class="w-full sm:w-auto text-white bg-amber-600 hover:bg-amber-700 focus:ring-4 focus:ring-amber-300 font-medium rounded-lg text-sm px-4 py-2">
                <i class="fa-solid fa-rotate mr-1"></i>Registrar cambio
            </button>
        </div>

        @if($latestEvent)
            <div class="rounded-lg border border-gray-200 dark:border-gray-600 p-3 bg-gray-50 dark:bg-gray-700/40 space-y-2">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                        Estado actual: <span class="{{ $latestEvent->power_source === 'Planta Electrica' ? 'text-amber-600' : 'text-emerald-600' }}">{{ $latestEvent->power_source }}</span>
                    </p>
                    <span class="text-xs text-gray-500 dark:text-gray-300">Contador: {{ $this->elapsedHuman }}</span>
                </div>

                @if($this->showFuelAlert)
                    <div class="rounded-lg px-3 py-2 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 text-xs font-semibold">
                        <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                        @if($latestEvent->power_source === 'Planta Electrica')
                            Han pasado mas de 8 horas en Planta Electrica. Se requiere revisar el nivel de combustible.
                        @else
                            Han pasado mas de 8 horas desde el ultimo cambio a CFE. Se recomienda revisar el nivel de combustible de la planta de emergencia.
                        @endif
                    </div>
                @endif

                <p class="text-xs text-gray-500 dark:text-gray-300">
                    Ultimo cambio: {{ optional($latestEvent->changed_at)->format('d/m/Y H:i:s') }}
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
                        <th class="px-3 py-2">Fecha</th>
                        <th class="px-3 py-2">Estado</th>
                        <th class="px-3 py-2">Observaciones</th>
                        <th class="px-3 py-2">Usuario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-600 bg-white dark:bg-gray-800">
                    @forelse($history as $event)
                        <tr>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ optional($event->changed_at)->format('d/m/Y H:i:s') }}</td>
                            <td class="px-3 py-2 font-semibold {{ $event->power_source === 'Planta Electrica' ? 'text-amber-600' : 'text-emerald-600' }}">{{ $event->power_source }}</td>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $event->notes ?: '—' }}</td>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $event->user?->name ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-5 text-center text-gray-500 dark:text-gray-400">Sin registros aun.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
