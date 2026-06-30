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
                    <i class="fa-solid fa-server text-gray-700 dark:text-gray-300"></i>
                    {{ $rack->name }}
                    <span class="text-gray-400 dark:text-gray-500">/</span>
                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold bg-blue-100 dark:bg-blue-800 text-blue-700 dark:text-blue-300 rounded">
                        {{ __('History') }}
                    </span>
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('Every creation, update, and deletion is recorded at the field level.') }}
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
                <div class="flex items-center gap-2 mb-2">
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
                    <x-label for="position">
                        <i class="fa-solid fa-hashtag mr-1"></i>
                        {{ __('Position (Unit)') }}
                    </x-label>
                    <x-input id="position" class="block mt-1 w-full" type="number" name="position"
                        :value="$filters['position'] ?? ''" min="1" max="{{ $rack->total_units }}"
                        placeholder="{{ __('Any') }}" autocomplete="off" />
                </div>
                <div>
                    <x-label for="change_type">
                        <i class="fa-solid fa-exchange-alt mr-1"></i>
                        {{ __('Change type') }}
                    </x-label>
                    <select id="change_type" name="change_type"
                        class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white truncate leading-tight
                            {{ Auth::user()?->area === 'DTH'
                                ? 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500 dark:focus:border-secondary-500'
                                : 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500' }}">
                        <option value="">{{ __('All') }}</option>
                        <option value="created" {{ ($filters['change_type'] ?? '') === 'created' ? 'selected' : '' }}>{{ __('Created') }}</option>
                        <option value="updated" {{ ($filters['change_type'] ?? '') === 'updated' ? 'selected' : '' }}>{{ __('Updated') }}</option>
                        <option value="deleted" {{ ($filters['change_type'] ?? '') === 'deleted' ? 'selected' : '' }}>{{ __('Deleted') }}</option>
                    </select>
                </div>
                <div>
                    <x-label for="from">
                        <i class="fa-regular fa-calendar mr-1"></i>
                        {{ __('From') }}
                    </x-label>
                    <x-input id="from" class="block mt-1 w-full" type="date" name="from"
                        :value="$filters['from'] ?? ''" autocomplete="off" />
                </div>
                <div>
                    <x-label for="to">
                        <i class="fa-regular fa-calendar mr-1"></i>
                        {{ __('To') }}
                    </x-label>
                    <x-input id="to" class="block mt-1 w-full" type="date" name="to"
                        :value="$filters['to'] ?? ''" autocomplete="off" />
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white rounded-lg {{ Auth::user()?->area === 'DTH' ? 'bg-secondary-600 hover:bg-secondary-700' : 'bg-primary-600 hover:bg-primary-700' }} shadow-sm hover:shadow-md">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        {{ __('Filter') }}
                    </button>
                    <a href="{{ route('admin.rack-layout.history', $rack) }}"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm">
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
            <div class="space-y-2.5">
                @foreach($history as $entry)
                    @php
                        $type = $entry->change_type;
                        $typeStyles = [
                            'created' => [
                                'icon'      => 'fa-circle-plus',
                                'dot'       => 'bg-gradient-to-br from-green-400 to-green-600',
                                'badge'     => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 ring-1 ring-inset ring-green-200 dark:ring-green-800',
                                'accent'    => 'border-l-green-500 dark:border-l-green-500',
                                'bg'        => 'bg-green-50/40 dark:bg-green-900/10',
                                'hoverBg'   => 'hover:bg-green-50/60 dark:hover:bg-green-900/20',
                                'label'     => __('Created'),
                            ],
                            'updated' => [
                                'icon'      => 'fa-pen-to-square',
                                'dot'       => 'bg-gradient-to-br from-blue-400 to-blue-600',
                                'badge'     => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 ring-1 ring-inset ring-blue-200 dark:ring-blue-800',
                                'accent'    => 'border-l-blue-500 dark:border-l-blue-500',
                                'bg'        => 'bg-blue-50/40 dark:bg-blue-900/10',
                                'hoverBg'   => 'hover:bg-blue-50/60 dark:hover:bg-blue-900/20',
                                'label'     => __('Updated'),
                            ],
                            'deleted' => [
                                'icon'      => 'fa-circle-minus',
                                'dot'       => 'bg-gradient-to-br from-red-400 to-red-600',
                                'badge'     => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 ring-1 ring-inset ring-red-200 dark:ring-red-800',
                                'accent'    => 'border-l-red-500 dark:border-l-red-500',
                                'bg'        => 'bg-red-50/40 dark:bg-red-900/10',
                                'hoverBg'   => 'hover:bg-red-50/60 dark:hover:bg-red-900/20',
                                'label'     => __('Deleted'),
                            ],
                        ];
                        $style = $typeStyles[$type] ?? [
                            'icon'      => 'fa-circle',
                            'dot'       => 'bg-gradient-to-br from-gray-400 to-gray-600',
                            'badge'     => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 ring-1 ring-inset ring-gray-200 dark:ring-gray-600',
                            'accent'    => 'border-l-gray-400 dark:border-l-gray-500',
                            'bg'        => 'bg-gray-50/40 dark:bg-gray-700/20',
                            'hoverBg'   => 'hover:bg-gray-50 dark:hover:bg-gray-700/40',
                            'label'     => ucfirst($type),
                        ];
                        $userName = $entry->user->name ?? __('System');
                        $userInitial = strtoupper(mb_substr($userName, 0, 1));
                    @endphp

                    <a href="{{ route('admin.rack-layout.position.history', [$rack, $entry->position]) }}"
                        class="group grid grid-cols-[64px_1fr_auto] sm:grid-cols-[72px_1fr_auto] gap-5 items-stretch">

                        <div class="relative flex flex-col items-center justify-center rounded-xl border-2 bg-gray-100 dark:bg-gray-700 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-1 shadow-sm overflow-hidden">
                            <span class="text-[10px] font-bold uppercase tracking-wider opacity-70">{{ __('Unit') }}</span>
                            <span class="text-lg font-black tracking-tight">U{{ $entry->position }}</span>
                        </div>

                        <div class="flex flex-col justify-center rounded-xl border border-gray-200 dark:border-gray-700 {{ $style['bg'] }} border-l-4 {{ $style['accent'] }} px-5 py-5 shadow-sm group-hover:shadow-md">
                            <div class="flex items-start gap-3">
                                <span class="flex-shrink-0 flex items-center justify-center w-14 h-14 rounded-lg bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 shadow-sm">
                                    <i class="fa-solid fa-hard-drive text-gray-500 dark:text-gray-400 text-xl"></i>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap mb-1">
                                        <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 truncate">
                                            {{ $entry->equipment_name ?? '—' }}
                                        </h3>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-full {{ $style['badge'] }}">
                                            <i class="fa-solid {{ $style['icon'] }} text-[8px]"></i>
                                            {{ $style['label'] }}
                                        </span>
                                    </div>

                                    <div class="flex items-center flex-wrap gap-x-4 gap-y-1 text-[12px] text-gray-600 dark:text-gray-400 pt-2">
                                        @if($entry->equipment_model)
                                            <span class="inline-flex items-center gap-1.5" title="{{ __('Model') }}">
                                                <i class="fa-solid fa-microchip text-gray-400 dark:text-gray-500"></i>
                                                <span class="font-medium">{{ $entry->equipment_model }}</span>
                                            </span>
                                        @endif
                                        @if($entry->vendor)
                                            <span class="inline-flex items-center gap-1.5" title="{{ __('Vendor') }}">
                                                <i class="fa-solid fa-industry text-gray-400 dark:text-gray-500"></i>
                                                <span>{{ $entry->vendor }}</span>
                                            </span>
                                        @endif
                                        @if($entry->ip_address)
                                            <span class="inline-flex items-center gap-1.5" title="{{ __('IP Address') }}">
                                                <i class="fa-solid fa-network-wired text-gray-400 dark:text-gray-500"></i>
                                                <span class="font-mono">{{ $entry->ip_address }}</span>
                                            </span>
                                        @endif
                                        @if($entry->mac_address)
                                            <span class="inline-flex items-center gap-1.5" title="{{ __('MAC Address') }}">
                                                <i class="fa-solid fa-address-card text-gray-400 dark:text-gray-500"></i>
                                                <span class="font-mono">{{ $entry->mac_address }}</span>
                                            </span>
                                        @endif
                                        @if($entry->serial_number)
                                            <span class="inline-flex items-center gap-1.5" title="{{ __('Serial Number') }}">
                                                <i class="fa-solid fa-barcode text-gray-400 dark:text-gray-500"></i>
                                                <span class="font-mono">{{ $entry->serial_number }}</span>
                                            </span>
                                        @endif
                                        @if($entry->equipment_role)
                                            <span class="inline-flex items-center gap-1.5" title="{{ __('Role / Function') }}">
                                                <i class="fa-solid fa-user-gear text-gray-400 dark:text-gray-500"></i>
                                                <span class="font-semibold">{{ $entry->equipment_role }}</span>
                                            </span>
                                        @endif
                                        @if($entry->installation_date)
                                            <span class="inline-flex items-center gap-1.5" title="{{ __('Installation Date') }}">
                                                <i class="fa-solid fa-calendar-check text-gray-400 dark:text-gray-500"></i>
                                                <span>{{ $entry->installation_date->format('M d, Y') }}</span>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 pl-3 min-w-[320px]" title="{{ $entry->changed_at->format('Y-m-d H:i:s') }}">
                            <div class="flex flex-col items-start gap-2.5 flex-1 min-w-0">
                                <div class="flex items-center gap-2.5 w-full">
                                    <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gradient-to-br from-sky-500 to-sky-700 text-white text-base font-bold shadow-sm flex-shrink-0">
                                        {{ $userInitial }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 leading-none mb-2">
                                            {{ __('Changed by') }}
                                        </div>
                                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate mb-2">
                                            {{ $userName }}
                                        </div>
                                        <div class="flex items-center gap-1.5 mt-1">
                                            <i class="fa-regular fa-clock text-gray-400 dark:text-gray-500 text-[11px]"></i>
                                            <span class="text-[11px] font-semibold text-gray-700 dark:text-gray-200">
                                                {{ ucfirst($entry->changed_at->diffForHumans()) }}
                                            </span>
                                            <span class="text-[11px] text-gray-400 dark:text-gray-500">·</span>
                                            <span class="text-[11px] text-gray-500 dark:text-gray-400 font-mono">
                                                {{ $entry->changed_at->format('M d, Y · H:i') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="mt-6">{{ $history->links() }}</div>
        @endif
    </div>
</x-admin-layout>
