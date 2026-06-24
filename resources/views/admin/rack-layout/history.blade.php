<x-admin-layout :breadcrumbs="[
        [
            'name' => __('Dashboard'),
            'icon' => 'fa-solid fa-wrench',
            'route' => route('admin.dashboard'),
        ],
        [
            'name' => __('Devices'),
            'icon' => 'fa-solid fa-hard-drive',
            'route' => route('admin.devices.index'),
        ],
        [
            'name' => __('Rack layouts'),
            'icon' => 'fa-solid fa-server',
            'route' => route('admin.rack-layout.index'),
        ],
        [
            'name' => $rack->name,
            'icon' => 'fa-solid fa-circle-info',
            'route' => route('admin.rack-layout.show', $rack),
        ],
        [
            'name' => __('History'),
            'icon' => 'fa-solid fa-clock-rotate-left',
        ],
    ]">

    <x-slot name="action">
        <a href="{{ route('admin.rack-layout.show', $rack) }}"
           class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors">
            <i class="fa-solid fa-arrow-left mr-2"></i> {{ __('Go back') }}
        </a>
    </x-slot>

    <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm dark:shadow-none dark:border dark:border-gray-700">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-layer-group text-gray-700 dark:text-gray-300"></i>
                    {{ $rack->name }}
                    <span class="text-gray-400 dark:text-gray-500">/</span>
                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 rounded-full">
                        <i class="fa-solid fa-clock-rotate-left mr-1"></i>
                        {{ __('History') }}
                    </span>
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('Every create, update, and delete is recorded with a field-level diff.') }}
                </p>
            </div>

            @php
                $created = $history->where('change_type', 'created')->count();
                $updated = $history->where('change_type', 'updated')->count();
                $deleted = $history->where('change_type', 'deleted')->count();
                $total = $history->total();
            @endphp

            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                    <i class="fa-solid fa-list text-gray-400"></i>
                    {{ trans_choice(':count Record|:count Records', $total, ['count' => $total]) }}
                </span>
                @if($created > 0)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                        <i class="fa-solid fa-circle-plus"></i> {{ $created }}
                    </span>
                @endif
                @if($updated > 0)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                        <i class="fa-solid fa-pen-to-square"></i> {{ $updated }}
                    </span>
                @endif
                @if($deleted > 0)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">
                        <i class="fa-solid fa-circle-minus"></i> {{ $deleted }}
                    </span>
                @endif
            </div>
        </div>

        <form method="GET" action="{{ route('admin.rack-layout.history', $rack) }}" class="mb-6 p-4 bg-gradient-to-br from-gray-50 to-gray-100/50 dark:from-gray-700/40 dark:to-gray-800/40 border border-gray-200 dark:border-gray-600 rounded-lg">
            <div class="flex items-center justify-between gap-2 mb-3">
                <div class="flex items-center gap-2">
                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 flex items-center justify-center shadow-sm">
                        <i class="fa-solid fa-filter text-gray-500 dark:text-gray-400 text-sm"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ __('Filters') }}</h3>
                </div>
                @if(array_filter($filters ?? []))
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300">
                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500 animate-pulse"></span>
                        {{ __('Active') }}
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div>
                    <label class="flex items-center gap-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 mb-1.5">
                        <i class="fa-solid fa-hashtag text-gray-400"></i>
                        {{ __('Position (Unit)') }}
                    </label>
                    <input type="number" name="position" value="{{ $filters['position'] ?? '' }}" min="1" max="{{ $rack->total_units }}"
                           placeholder="{{ __('Any') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-md focus:ring-2 focus:ring-[#9F24A5]/30 focus:border-[#9F24A5] transition-all">
                </div>
                <div>
                    <label class="flex items-center gap-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 mb-1.5">
                        <i class="fa-solid fa-tag text-gray-400"></i>
                        {{ __('Change type') }}
                    </label>
                    <select name="change_type" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-md focus:ring-2 focus:ring-[#9F24A5]/30 focus:border-[#9F24A5] transition-all">
                        <option value="">{{ __('All') }}</option>
                        <option value="created" {{ ($filters['change_type'] ?? '') === 'created' ? 'selected' : '' }}>{{ __('Created') }}</option>
                        <option value="updated" {{ ($filters['change_type'] ?? '') === 'updated' ? 'selected' : '' }}>{{ __('Updated') }}</option>
                        <option value="deleted" {{ ($filters['change_type'] ?? '') === 'deleted' ? 'selected' : '' }}>{{ __('Deleted') }}</option>
                    </select>
                </div>
                <div>
                    <label class="flex items-center gap-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 mb-1.5">
                        <i class="fa-regular fa-calendar text-gray-400"></i>
                        {{ __('From') }}
                    </label>
                    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-md focus:ring-2 focus:ring-[#9F24A5]/30 focus:border-[#9F24A5] transition-all">
                </div>
                <div>
                    <label class="flex items-center gap-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 mb-1.5">
                        <i class="fa-regular fa-calendar text-gray-400"></i>
                        {{ __('To') }}
                    </label>
                    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-md focus:ring-2 focus:ring-[#9F24A5]/30 focus:border-[#9F24A5] transition-all">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-[#9F24A5] rounded-md hover:bg-[#7a1d82] shadow-sm hover:shadow-md transition-all">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        {{ __('Filter') }}
                    </button>
                    <a href="{{ route('admin.rack-layout.history', $rack) }}"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm transition-all">
                        <i class="fa-solid fa-rotate-left text-xs"></i>
                        {{ __('Clear') }}
                    </a>
                </div>
            </div>
        </form>

        @if($history->count() === 0)
            <div class="text-center py-16 px-4">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 mb-4 shadow-inner">
                    <i class="fa-solid fa-clock-rotate-left text-4xl text-gray-400 dark:text-gray-500"></i>
                </div>
                <p class="text-base font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('No history records yet.') }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Changes will appear here as they happen.') }}</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($history as $entry)
                    @php
                        $type = $entry->change_type;
                        $typeStyles = [
                            'created' => [
                                'icon'      => 'fa-circle-plus',
                                'dot'       => 'bg-gradient-to-br from-green-400 to-green-600',
                                'badge'     => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 ring-1 ring-inset ring-green-200 dark:ring-green-800',
                                'accent'    => 'border-l-green-400 dark:border-l-green-500',
                                'hoverBg'   => 'hover:bg-green-50/50 dark:hover:bg-green-900/10',
                                'label'     => __('Created'),
                            ],
                            'updated' => [
                                'icon'      => 'fa-pen-to-square',
                                'dot'       => 'bg-gradient-to-br from-blue-400 to-blue-600',
                                'badge'     => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 ring-1 ring-inset ring-blue-200 dark:ring-blue-800',
                                'accent'    => 'border-l-blue-400 dark:border-l-blue-500',
                                'hoverBg'   => 'hover:bg-blue-50/50 dark:hover:bg-blue-900/10',
                                'label'     => __('Updated'),
                            ],
                            'deleted' => [
                                'icon'      => 'fa-circle-minus',
                                'dot'       => 'bg-gradient-to-br from-red-400 to-red-600',
                                'badge'     => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 ring-1 ring-inset ring-red-200 dark:ring-red-800',
                                'accent'    => 'border-l-red-400 dark:border-l-red-500',
                                'hoverBg'   => 'hover:bg-red-50/50 dark:hover:bg-red-900/10',
                                'label'     => __('Deleted'),
                            ],
                        ];
                        $style = $typeStyles[$type] ?? [
                            'icon'      => 'fa-circle',
                            'dot'       => 'bg-gradient-to-br from-gray-400 to-gray-600',
                            'badge'     => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 ring-1 ring-inset ring-gray-200 dark:ring-gray-600',
                            'accent'    => 'border-l-gray-300 dark:border-l-gray-600',
                            'hoverBg'   => 'hover:bg-gray-50 dark:hover:bg-gray-700/40',
                            'label'     => ucfirst($type),
                        ];
                        $userName = $entry->user->name ?? __('System');
                        $userInitial = strtoupper(mb_substr($userName, 0, 1));
                    @endphp

                    <a href="{{ route('admin.rack-layout.position.history', [$rack, $entry->position]) }}"
                       class="group flex items-center gap-4 p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 border-l-4 {{ $style['accent'] }} rounded-lg shadow-sm hover:shadow-md {{ $style['hoverBg'] }} transition-all duration-200">

                        <span class="flex-shrink-0 flex items-center justify-center w-11 h-11 rounded-full ring-2 ring-white dark:ring-gray-800 shadow-sm {{ $style['dot'] }}">
                            <i class="fa-solid {{ $style['icon'] }} text-white text-sm"></i>
                        </span>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold rounded-full {{ $style['badge'] }}">
                                    {{ $style['label'] }}
                                </span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-bold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 rounded">
                                    <i class="fa-solid fa-location-dot text-[9px] text-gray-400"></i>
                                    U{{ $entry->position }}
                                </span>
                            </div>
                            <div class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate mb-1">
                                {{ $entry->equipment_name ?? '—' }}
                            </div>
                            <div class="flex items-center flex-wrap gap-x-4 gap-y-1 text-[11px] text-gray-500 dark:text-gray-400">
                                @if($entry->equipment_model)
                                    <span class="inline-flex items-center gap-1.5">
                                        <i class="fa-solid fa-microchip text-gray-400"></i>
                                        <span class="font-medium">{{ $entry->equipment_model }}</span>
                                    </span>
                                @endif
                                @if($entry->ip_address)
                                    <span class="inline-flex items-center gap-1.5">
                                        <i class="fa-solid fa-network-wired text-gray-400"></i>
                                        <span class="font-mono">{{ $entry->ip_address }}</span>
                                    </span>
                                @endif
                                @if($entry->equipment_role)
                                    <span class="inline-flex items-center gap-1.5">
                                        <i class="fa-solid fa-circle-nodes text-gray-400"></i>
                                        <span>{{ $entry->equipment_role }}</span>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="hidden lg:flex items-center gap-4 flex-shrink-0 pl-4 border-l border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-3" title="{{ $entry->changed_at->format('Y-m-d H:i:s') }}">
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-sky-500 to-sky-700 text-white text-sm font-bold shadow-sm flex-shrink-0">
                                    {{ $userInitial }}
                                </span>
                                <div class="min-w-0">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 leading-none mb-1">
                                        {{ __('Changed by') }}
                                    </div>
                                    <div class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate max-w-[180px]">
                                        {{ $userName }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="hidden sm:flex items-center gap-4 flex-shrink-0 pl-4 border-l border-gray-200 dark:border-gray-700">
                            <div class="flex flex-col items-start min-w-[150px]">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 leading-none mb-1">
                                    {{ __('When') }}
                                </div>
                                <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    {{ ucfirst($entry->changed_at->diffForHumans()) }}
                                </div>
                                <div class="text-[11px] text-gray-500 dark:text-gray-400 font-mono mt-0.5">
                                    {{ $entry->changed_at->format('M d, Y · H:i') }}
                                </div>
                            </div>
                        </div>

                        <div class="flex-shrink-0 text-gray-300 dark:text-gray-600 group-hover:text-[#9F24A5] dark:group-hover:text-purple-400 group-hover:translate-x-1 transition-all pl-2">
                            <i class="fa-solid fa-chevron-right text-sm"></i>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-6">{{ $history->links() }}</div>
        @endif
    </div>
</x-admin-layout>
