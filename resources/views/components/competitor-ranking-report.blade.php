@props(['ranking' => [], 'title' => 'Ranking de Apps Competidoras', 'date' => null])

<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
    <div class="px-4 py-3 bg-gradient-to-r from-primary-50 to-primary-50/50 dark:from-primary-900/30 dark:to-primary-900/10 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-podium text-primary-600 dark:text-primary-400"></i>
                {{ $title }}
            </h3>
            @if($date)
                <p class="text-xs font-medium text-gray-600 dark:text-gray-400">
                    {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                </p>
            @endif
        </div>
    </div>

    @if($ranking->isEmpty())
        <div class="p-6 text-center">
            <div class="text-gray-400 dark:text-gray-500 mb-2">
                <i class="fa-solid fa-inbox text-2xl"></i>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">No hay datos de ranking disponibles para esta fecha</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/40">
                    <tr>
                        <th class="px-3 py-2.5 text-left w-10">Pos</th>
                        <th class="px-3 py-2.5 text-left flex-1">Aplicación</th>
                        <th class="px-3 py-2.5 text-center w-16">Rating</th>
                        <th class="px-3 py-2.5 text-center w-20">Descargas</th>
                        <th class="px-3 py-2.5 text-center w-20">Opiniones</th>
                        <th class="px-3 py-2.5 text-center w-20">Tendencia</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($ranking as $item)
                        @php
                            $movement = $item['movement'] ?? 'new';
                            $delta = (int) ($item['movement_delta'] ?? 0);
                            $isPrimary = (bool) ($item['is_primary'] ?? false);
                        @endphp
                        <tr class="{{ $isPrimary ? 'bg-primary-50/40 dark:bg-primary-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-700/30' }} transition-colors">
                            <td class="px-3 py-2.5 font-bold text-gray-900 dark:text-white">
                                @if($item['rank'] === '—')
                                    —
                                @else
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full {{ $item['rank'] === 1 ? 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }} text-[10px]">
                                        {{ $item['rank'] }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 font-semibold text-gray-900 dark:text-white">
                                {{ $item['app'] }}
                                @if($isPrimary)
                                    <span class="ml-1 inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-primary-100 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300">
                                        <i class="fa-solid fa-crown"></i> PRINCIPAL
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-center font-bold text-gray-900 dark:text-white">{{ $item['rating'] }}</td>
                            <td class="px-3 py-2.5 text-center text-gray-700 dark:text-gray-300">{{ $item['downloads'] }}</td>
                            <td class="px-3 py-2.5 text-center text-gray-700 dark:text-gray-300">{{ $item['reviews'] }}</td>
                            <td class="px-3 py-2.5 text-center">
                                @if($movement === 'up')
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400">
                                        <i class="fa-solid fa-arrow-up text-[9px]"></i>+{{ abs($delta) }}
                                    </span>
                                @elseif($movement === 'down')
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-400">
                                        <i class="fa-solid fa-arrow-down text-[9px]"></i>{{ $delta }}
                                    </span>
                                @elseif($movement === 'same')
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
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
