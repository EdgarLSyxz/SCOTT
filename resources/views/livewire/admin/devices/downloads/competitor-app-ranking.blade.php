<div class="bg-white dark:bg-gray-800 relative shadow-2xl rounded-lg overflow-hidden mb-6">
    <div class="flex flex-col gap-4 p-4 md:p-6 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/80">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-chart-column text-primary-600 dark:text-primary-400 text-lg"></i>
                    Ranking de Apps Competidoras
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Monitorea y compara el desempeño de apps competidoras frente a StarTV Stream
                </p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1 uppercase tracking-wide">
                        <i class="fa-regular fa-calendar mr-1"></i>Fecha de Consulta
                    </label>
                    <input type="date" wire:model.live="snapshotDate"
                        class="rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-medium" />
                </div>
            </div>
        </div>
    </div>

    <div class="p-4 md:p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/20">
        <div class="mb-4">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-emerald-500"></i>
                Registrar Nueva App Competidora
            </h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-4">
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5 uppercase tracking-wide">
                    <i class="fa-solid fa-mobile-screen mr-1"></i>Nombre de la App
                </label>
                <input type="text" wire:model.defer="newAppName" placeholder="Ej. TUBI"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-primary-500 focus:border-primary-500" />
                @error('newAppName')
                    <p class="mt-1 text-xs text-red-500 flex items-center gap-1"><i class="fa-solid fa-exclamation-circle"></i> {{ $message }}</p>
                @enderror
            </div>
            <div class="md:col-span-6">
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5 uppercase tracking-wide">
                    <i class="fa-solid fa-link mr-1"></i>Link en Tienda (Google Play / App Store)
                </label>
                <input type="url" wire:model.defer="newAppStoreUrl" placeholder="https://play.google.com/store/apps/..."
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-primary-500 focus:border-primary-500" />
                @error('newAppStoreUrl')
                    <p class="mt-1 text-xs text-red-500 flex items-center gap-1"><i class="fa-solid fa-exclamation-circle"></i> {{ $message }}</p>
                @enderror
            </div>
            <div class="md:col-span-2">
                <button type="button" wire:click="addApp"
                    class="w-full text-white {{ Auth::user()?->area === 'DTH' ? 'bg-secondary-600 hover:bg-secondary-700 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700' : 'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-300 dark:bg-emerald-600 dark:hover:bg-emerald-700' }} font-semibold rounded-lg text-sm px-4 py-2.5 shadow-md transition-all">
                    <i class="fa-solid fa-plus mr-1"></i>
                    Agregar App
                </button>
            </div>
        </div>
    </div>

    <div class="p-4 md:p-6">
        @if(count($rows) === 0)
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-4">
                    <i class="fa-solid fa-inbox text-3xl text-gray-400 dark:text-gray-500"></i>
                </div>
                <p class="text-gray-600 dark:text-gray-300 font-medium">Sin registros de apps aún</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Comienza registrando tu primera aplicación competidora</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 border-b-2 border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3">Aplicación</th>
                            <th class="px-4 py-3 text-center">Rating ⭐</th>
                            <th class="px-4 py-3 text-center">Descargas</th>
                            <th class="px-4 py-3 text-center">Opiniones</th>
                            <th class="px-4 py-3 text-center">Lanzamiento</th>
                            <th class="px-4 py-3">Link en Tienda</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($rows as $index => $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                                <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">
                                    <div class="flex items-center gap-2">
                                        {{ $row['name'] }}
                                        @if($row['is_primary'])
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary-100 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300">
                                                <i class="fa-solid fa-crown"></i> PRINCIPAL
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" step="0.1" min="0" max="5" wire:model.defer="rows.{{ $index }}.rating"
                                        class="w-20 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-center font-semibold text-sm focus:ring-primary-500 focus:border-primary-500" />
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" wire:model.defer="rows.{{ $index }}.downloads_label" placeholder="50 K +"
                                        class="w-28 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-center text-sm focus:ring-primary-500 focus:border-primary-500" />
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" wire:model.defer="rows.{{ $index }}.reviews_label" placeholder="248"
                                        class="w-24 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-center text-sm focus:ring-primary-500 focus:border-primary-500" />
                                </td>
                                <td class="px-4 py-3">
                                    <input type="date" wire:model.defer="rows.{{ $index }}.release_date"
                                        class="rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500" />
                                </td>
                                <td class="px-4 py-3">
                                    <input type="url" wire:model.defer="rows.{{ $index }}.store_url" placeholder="https://..."
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs focus:ring-primary-500 focus:border-primary-500" />
                                    <input type="hidden" wire:model.defer="rows.{{ $index }}.competitor_app_id" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-5 flex justify-end">
                <button type="button" wire:click="saveSnapshot"
                    class="inline-flex items-center gap-2 text-white {{ Auth::user()?->area === 'DTH' ? 'bg-secondary-600 hover:bg-secondary-700 focus:ring-secondary-300' : 'bg-primary-600 hover:bg-primary-700 focus:ring-primary-300' }} font-semibold rounded-lg text-sm px-6 py-2.5 shadow-lg transition-all">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Guardar Actualización y Recalcular Ranking
                </button>
            </div>
        @endif
    </div>

    @if($hasSnapshot && count($rows) > 0)
        <div class="border-t border-gray-100 dark:border-gray-700 p-4 md:p-6 bg-gradient-to-b from-gray-50/50 to-transparent dark:from-gray-700/20 dark:to-transparent space-y-6">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-4 py-3 bg-gradient-to-r from-primary-50 to-primary-50/50 dark:from-primary-900/30 dark:to-primary-900/10 border-b border-gray-200 dark:border-gray-700">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <i class="fa-solid fa-magnifying-glass text-primary-600 dark:text-primary-400"></i>
                            Consulta - StarTV Stream
                        </p>
                    </div>
                    @if($primaryAppRow)
                        <div class="p-4 space-y-3">
                            <div>
                                <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $primaryAppRow->app->name }}</div>
                                <p class="text-[11px] uppercase tracking-wider text-gray-500 dark:text-gray-400 mt-0.5">Aplicación Principal</p>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center px-2 py-1.5 rounded-lg bg-gray-50 dark:bg-gray-700/40">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Rating</span>
                                    <span class="font-bold text-primary-600 dark:text-primary-400">{{ number_format((float) $primaryAppRow->rating, 1) }} ⭐</span>
                                </div>
                                <div class="flex justify-between items-center px-2 py-1.5 rounded-lg bg-gray-50 dark:bg-gray-700/40">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Descargas</span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $primaryAppRow->downloads_label ?: '—' }}</span>
                                </div>
                                <div class="flex justify-between items-center px-2 py-1.5 rounded-lg bg-gray-50 dark:bg-gray-700/40">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Opiniones</span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $primaryAppRow->reviews_label ?: '—' }}</span>
                                </div>
                                <div class="flex justify-between items-center px-2 py-1.5 rounded-lg bg-gray-50 dark:bg-gray-700/40">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">Lanzamiento</span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ optional($primaryAppRow->release_date)->format('d M Y') ?: '—' }}</span>
                                </div>
                            </div>
                            @if($primaryAppRow->app->store_url)
                                <a href="{{ $primaryAppRow->app->store_url }}" target="_blank" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-primary-900/50 font-semibold text-xs transition-colors">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    Ver en Tienda
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            <i class="fa-solid fa-circle-info text-lg mb-2 block opacity-50"></i>
                            Sin registro de la app principal para esta fecha
                        </div>
                    @endif
                </div>

                <div class="lg:col-span-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-4 py-3 bg-gradient-to-r from-emerald-50 to-emerald-50/50 dark:from-emerald-900/30 dark:to-emerald-900/10 border-b border-gray-200 dark:border-gray-700">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <i class="fa-solid fa-podium text-emerald-600 dark:text-emerald-400"></i>
                            Top 10 Aplicaciones
                        </p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead class="text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                                <tr>
                                    <th class="px-3 py-2 text-left">Pos</th>
                                    <th class="px-3 py-2 text-left">App</th>
                                    <th class="px-3 py-2 text-center">Rating</th>
                                    <th class="px-3 py-2 text-center">Descargas</th>
                                    <th class="px-3 py-2 text-center">Opiniones</th>
                                    <th class="px-3 py-2 text-center">Tendencia</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($topTen as $item)
                                    @php
                                        $isPrimary = (bool) $item->app?->is_primary;
                                        $movement = $item->movement;
                                        $delta = (int) ($item->rank_delta ?? 0);
                                    @endphp
                                    <tr class="{{ $isPrimary ? 'bg-primary-50/60 dark:bg-primary-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-700/30' }} transition-colors">
                                        <td class="px-3 py-2 font-bold text-gray-900 dark:text-white">
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full {{ $item->rank_position === 1 ? 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                                {{ $item->rank_position ?: '—' }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 font-semibold text-gray-900 dark:text-white">{{ $item->app->name }}</td>
                                        <td class="px-3 py-2 text-center font-bold text-gray-900 dark:text-white">{{ number_format((float) $item->rating, 1) }}</td>
                                        <td class="px-3 py-2 text-center text-gray-700 dark:text-gray-300">{{ $item->downloads_label ?: '—' }}</td>
                                        <td class="px-3 py-2 text-center text-gray-700 dark:text-gray-300">{{ $item->reviews_label ?: '—' }}</td>
                                        <td class="px-3 py-2 text-center">
                                            @if($movement === 'up')
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400">
                                                    <i class="fa-solid fa-arrow-up"></i>+{{ abs($delta) }}
                                                </span>
                                            @elseif($movement === 'down')
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-bold bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-400">
                                                    <i class="fa-solid fa-arrow-down"></i>{{ $delta }}
                                                </span>
                                            @elseif($movement === 'same')
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-bold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400">
                                                    <i class="fa-solid fa-minus"></i>0
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-bold bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400">
                                                    <i class="fa-solid fa-star"></i>Nuevo
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 bg-gradient-to-r from-blue-50 to-blue-50/50 dark:from-blue-900/30 dark:to-blue-900/10 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 flex items-center gap-2">
                        <i class="fa-solid fa-timeline text-blue-600 dark:text-blue-400"></i>
                        Trazabilidad de Apps
                    </p>
                    <select wire:model.live="traceAppId" class="rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs font-medium py-1.5 px-2">
                        @foreach($traceApps as $traceApp)
                            <option value="{{ $traceApp->id }}">{{ $traceApp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/40">
                            <tr>
                                <th class="px-4 py-2.5 text-left">Fecha</th>
                                <th class="px-4 py-2.5 text-center">Posición</th>
                                <th class="px-4 py-2.5 text-center">Rating</th>
                                <th class="px-4 py-2.5 text-center">Descargas</th>
                                <th class="px-4 py-2.5 text-center">Cambio</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($traceHistory as $trace)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">{{ optional($trace->snapshot_date)->format('d/m/Y') }}</td>
                                    <td class="px-4 py-2.5 text-center font-bold">
                                        @if($trace->rank_position)
                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-[10px]">{{ $trace->rank_position }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-center font-semibold text-gray-900 dark:text-white">{{ number_format((float) $trace->rating, 1) }}</td>
                                    <td class="px-4 py-2.5 text-center text-gray-700 dark:text-gray-300">{{ $trace->downloads_label ?: '—' }}</td>
                                    <td class="px-4 py-2.5 text-center">
                                        @if($trace->movement === 'up')
                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400">
                                                <i class="fa-solid fa-arrow-up text-[9px]"></i>+{{ abs((int) $trace->rank_delta) }}
                                            </span>
                                        @elseif($trace->movement === 'down')
                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-400">
                                                <i class="fa-solid fa-arrow-down text-[9px]"></i>{{ (int) $trace->rank_delta }}
                                            </span>
                                        @elseif($trace->movement === 'same')
                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400">
                                                <i class="fa-solid fa-minus text-[9px]"></i>0
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400">
                                                <i class="fa-solid fa-star text-[9px]"></i>Nuevo
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                        <i class="fa-solid fa-inbox text-lg block mb-2 opacity-40"></i>
                                        Sin histórico para esta app
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
