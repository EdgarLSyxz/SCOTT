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
            'name' => __('History') . ' ' . 'U' . $position,
            'icon' => 'fa-solid fa-pen',
        ],
    ]">

    <x-slot name="action">
        <a href="{{ route('admin.rack-layout.show', $rack) }}"
           class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
            <i class="fa-solid fa-arrow-left mr-2"></i> {{ __('Go back') }}
        </a>
    </x-slot>

    <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm dark:shadow-none dark:border dark:border-gray-700">

        <div class="flex items-center justify-between gap-3 mb-5">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center">
                    <span class="inline-flex items-center justify-center rounded-lg text-gray-800 dark:text-white gap-2">
                        <i class="fa-solid fa-layer-group text-gray-800 dark:text-white mr-0.5"></i>
                        {{ $rack->name }}
                        <span class="text-gray-400 dark:text-gray-500 mx-1">/</span>
                        @php
                            $displayPosition = 'U' . $position;
                            if (!empty($current) && (int) ($current->size_u ?? 1) > 1) {
                                $displayPosition = 'U' . $position . ' - U' . ($position + (int) $current->size_u - 1);
                            }
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold bg-blue-200 dark:bg-blue-700 rounded">
                            {{ $displayPosition }}
                        </span>
                    </span>
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('Change history for this specific position.') }}
                </p>
            </div>

            @php
                $created = $history->where('change_type', 'created')->count();
                $updated = $history->where('change_type', 'updated')->count();
                $deleted = $history->where('change_type', 'deleted')->count();
                $total = $history->total();
            @endphp

            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                    <i class="fa-solid fa-list text-gray-400"></i>
                    {{ trans_choice(':count Record|:count Records', $total, ['count' => $total]) }}
                </span>
                @if($created > 0)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                        <i class="fa-solid fa-plus"></i> {{ $created }}
                    </span>
                @endif
                @if($updated > 0)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                        <i class="fa-solid fa-pen-to-square"></i> {{ $updated }}
                    </span>
                @endif
                @if($deleted > 0)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">
                        <i class="fa-solid fa-trash"></i> {{ $deleted }}
                    </span>
                @endif
            </div>
        </div>

        @if($current && $current->equipment_name)
            @php
                $colorIcon = match($current->color) {
                    'green'  => ['icon' => 'fa-circle-check',    'bg' => 'bg-green-100 dark:bg-green-900/40',  'text' => 'text-green-600 dark:text-green-400',  'border' => 'border-green-200 dark:border-green-800',  'grad' => 'from-green-50 to-emerald-50 dark:from-green-900/30 dark:to-emerald-900/20', 'blob' => 'bg-green-200/30 dark:bg-green-700/20', 'label' => 'text-green-700 dark:text-green-300'],
                    'red'    => ['icon' => 'fa-circle-exclamation', 'bg' => 'bg-red-100 dark:bg-red-900/40',    'text' => 'text-red-600 dark:text-red-400',    'border' => 'border-red-200 dark:border-red-800',    'grad' => 'from-red-50 to-rose-50 dark:from-red-900/30 dark:to-rose-900/20',           'blob' => 'bg-red-200/30 dark:bg-red-700/20',       'label' => 'text-red-700 dark:text-red-300'],
                    'blue'   => ['icon' => 'fa-circle-info',     'bg' => 'bg-blue-100 dark:bg-blue-900/40',   'text' => 'text-blue-600 dark:text-blue-400',   'border' => 'border-blue-200 dark:border-blue-800',  'grad' => 'from-sky-50 to-blue-50 dark:from-sky-900/30 dark:to-blue-900/20',          'blob' => 'bg-blue-200/30 dark:bg-blue-700/20',      'label' => 'text-blue-700 dark:text-blue-300'],
                    'yellow' => ['icon' => 'fa-triangle-exclamation', 'bg' => 'bg-yellow-100 dark:bg-yellow-900/40', 'text' => 'text-yellow-600 dark:text-yellow-400', 'border' => 'border-yellow-200 dark:border-yellow-800', 'grad' => 'from-yellow-50 to-amber-50 dark:from-yellow-900/30 dark:to-amber-900/20', 'blob' => 'bg-yellow-200/30 dark:bg-yellow-700/20', 'label' => 'text-yellow-700 dark:text-yellow-300'],
                    'orange' => ['icon' => 'fa-bell',            'bg' => 'bg-orange-100 dark:bg-orange-900/40', 'text' => 'text-orange-600 dark:text-orange-400', 'border' => 'border-orange-200 dark:border-orange-800', 'grad' => 'from-orange-50 to-amber-50 dark:from-orange-900/30 dark:to-amber-900/20', 'blob' => 'bg-orange-200/30 dark:bg-orange-700/20', 'label' => 'text-orange-700 dark:text-orange-300'],
                    default  => ['icon' => 'fa-server',          'bg' => 'bg-slate-100 dark:bg-slate-700/40', 'text' => 'text-slate-600 dark:text-slate-300', 'border' => 'border-slate-200 dark:border-slate-600', 'grad' => 'from-slate-50 to-gray-50 dark:from-slate-800/40 dark:to-gray-800/30',  'blob' => 'bg-slate-200/30 dark:bg-slate-700/20',   'label' => 'text-slate-700 dark:text-slate-300'],
                };
            @endphp

            <div class="mb-6 relative overflow-hidden p-4 bg-gradient-to-r {{ $colorIcon['grad'] }} border {{ $colorIcon['border'] }} rounded-lg">
                <div class="absolute top-0 right-0 w-40 h-40 -mt-12 -mr-12 {{ $colorIcon['blob'] }} rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative flex items-start gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-white dark:bg-gray-800 border {{ $colorIcon['border'] }} flex items-center justify-center shadow-sm">
                        <i class="fa-solid fa-server {{ $colorIcon['text'] }} text-lg"></i>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="text-[11px] font-bold uppercase tracking-wider {{ $colorIcon['label'] }}">
                                {{ __('Current equipment') }}
                                <i class="fa-solid fa-dot w-2 h-2 text-sm rounded-full bg-green-400 animate-pulse ml-1"></i>
                            </span>
                        </div>

                        <div class="font-semibold text-gray-800 dark:text-gray-100 text-base truncate">
                            {{ $current->equipment_name }}
                        </div>

                        <div class="flex items-center flex-wrap gap-x-4 gap-y-1.5 text-xs text-gray-600 dark:text-gray-300 mt-2">
                            @if($current->equipment_model)
                                <span class="inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-microchip text-gray-400 w-3.5 text-center"></i>
                                    <span class="font-medium">{{ $current->equipment_model }}</span>
                                </span>
                            @endif
                            @if($current->ip_address)
                                <span class="inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-network-wired text-gray-400 w-3.5 text-center"></i>
                                    <span class="font-mono">{{ $current->ip_address }}</span>
                                </span>
                            @endif
                            @if($current->equipment_role)
                                <span class="inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle-nodes text-gray-400 w-3.5 text-center"></i>
                                    <span>{{ $current->equipment_role }}</span>
                                </span>
                            @endif
                            @if($current->vendor)
                                <span class="inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-industry text-gray-400 w-3.5 text-center"></i>
                                    <span>{{ $current->vendor }}</span>
                                </span>
                            @endif
                            @if($current->serial_number)
                                <span class="inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-barcode text-gray-400 w-3.5 text-center"></i>
                                    <span class="font-mono">{{ $current->serial_number }}</span>
                                </span>
                            @endif
                            @if($current->mac_address)
                                <span class="inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-address-card text-gray-400 w-3.5 text-center"></i>
                                    <span class="font-mono">{{ $current->mac_address }}</span>
                                </span>
                            @endif
                            @if($current->installation_date)
                                <span class="inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-calendar-check text-gray-400 w-3.5 text-center"></i>
                                    <span>{{ $current->installation_date->format('M d, Y') }}</span>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/40 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-500 dark:text-gray-400 flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 flex items-center justify-center">
                    <i class="fa-solid fa-hard-drive text-gray-400"></i>
                </div>
                <div>
                    <div class="font-semibold text-gray-700 dark:text-gray-200">{{ __('This position is currently empty.') }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('No equipment is currently assigned to U:pos.', ['pos' => $position]) }}</div>
                </div>
            </div>
        @endif

        @if($history->count() === 0)
            <div class="text-center py-16 text-gray-500 dark:text-gray-400">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 mb-4">
                    <i class="fa-solid fa-clock-rotate-left text-3xl text-gray-300 dark:text-gray-500"></i>
                </div>
                <p class="font-medium">{{ __('No history records for this position yet.') }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ __('Changes will appear here as they happen.') }}</p>
            </div>
        @else
            <div class="relative">
                <div class="absolute left-[19px] top-2 bottom-2 w-px bg-gradient-to-b from-gray-200 via-gray-200 to-transparent dark:from-gray-700 dark:via-gray-700"></div>

                <ul class="space-y-4">
                    @foreach($history as $entry)
                        @php
                            $type = $entry->change_type;
                            $typeStyles = [
                                'created' => [
                                    'dot'       => 'bg-gradient-to-br from-green-400 to-green-600 ring-green-100 dark:ring-green-900/40',
                                    'icon'      => 'fa-circle-plus',
                                    'badge'     => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 ring-1 ring-inset ring-green-200 dark:ring-green-800',
                                    'accent'    => 'border-l-green-400 dark:border-l-green-500',
                                    'diffBg'    => 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800',
                                    'diffTitle' => 'text-green-800 dark:text-green-300',
                                    'metaBg'    => 'bg-green-50/60 dark:bg-green-900/20 border-green-100 dark:border-green-900/40',
                                ],
                                'updated' => [
                                    'dot'       => 'bg-gradient-to-br from-blue-400 to-blue-600 ring-blue-100 dark:ring-blue-900/40',
                                    'icon'      => 'fa-pen-to-square',
                                    'badge'     => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 ring-1 ring-inset ring-blue-200 dark:ring-blue-800',
                                    'accent'    => 'border-l-blue-400 dark:border-l-blue-500',
                                    'diffBg'    => 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800',
                                    'diffTitle' => 'text-blue-800 dark:text-blue-300',
                                    'metaBg'    => 'bg-blue-50/60 dark:bg-blue-900/20 border-blue-100 dark:border-blue-900/40',
                                ],
                                'deleted' => [
                                    'dot'       => 'bg-gradient-to-br from-red-400 to-red-600 ring-red-100 dark:ring-red-900/40',
                                    'icon'      => 'fa-circle-minus',
                                    'badge'     => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 ring-1 ring-inset ring-red-200 dark:ring-red-800',
                                    'accent'    => 'border-l-red-400 dark:border-l-red-500',
                                    'diffBg'    => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800',
                                    'diffTitle' => 'text-red-800 dark:text-red-300',
                                    'metaBg'    => 'bg-red-50/60 dark:bg-red-900/20 border-red-100 dark:border-red-900/40',
                                ],
                            ];
                            $style = $typeStyles[$type] ?? [
                                'dot'       => 'bg-gradient-to-br from-gray-400 to-gray-600 ring-gray-100 dark:ring-gray-700',
                                'icon'      => 'fa-circle',
                                'badge'     => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 ring-1 ring-inset ring-gray-200 dark:ring-gray-600',
                                'accent'    => 'border-l-gray-300 dark:border-l-gray-600',
                                'diffBg'    => 'bg-gray-50 dark:bg-gray-700/40 border-gray-200 dark:border-gray-600',
                                'diffTitle' => 'text-gray-800 dark:text-gray-200',
                                'metaBg'    => 'bg-gray-50 dark:bg-gray-700/40 border-gray-100 dark:border-gray-700',
                            ];
                            $fieldLabels = [
                                'equipment_name'    => __('Name'),
                                'equipment_model'   => __('Model'),
                                'equipment_role'    => __('Role'),
                                'ip_address'        => __('IP'),
                                'serial_number'     => __('Serial'),
                                'mac_address'       => __('MAC'),
                                'vendor'            => __('Vendor'),
                                'installation_date' => __('Installed'),
                                'notes'             => __('Notes'),
                                'color'             => __('Color'),
                                    'is_active'         => __('Status'),
                                    'size_u'            => __('Tamaño de la unidad'),
                            ];
                            $userName = $entry->user->name ?? __('System');
                            $userInitial = strtoupper(mb_substr($userName, 0, 1));
                        @endphp

                        <li class="relative pl-12">
                            <span class="absolute left-0 top-3 flex items-center justify-center w-10 h-10 rounded-full ring-4 shadow-sm {{ $style['dot'] }}">
                                <i class="fa-solid {{ $style['icon'] }} text-white text-sm"></i>
                            </span>

                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 border-l-4 {{ $style['accent'] }} rounded-lg shadow-sm hover:shadow-md transition-shadow">

                                <div class="flex items-start justify-between gap-3 px-4 pt-4">
                                    <div class="flex items-center gap-2 flex-wrap min-w-0">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full {{ $style['badge'] }}">
                                            <i class="fa-solid {{ $style['icon'] }} text-[10px]"></i>
                                            {{ $entry->change_type_label }}
                                        </span>
                                    </div>
                                </div>

                                <div class="px-4 pt-3 pb-3">
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-gray-50 dark:bg-gray-700/60 border border-gray-200 dark:border-gray-600 flex items-center justify-center">
                                            <i class="fa-solid fa-server text-gray-500 dark:text-gray-400 text-sm"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate">
                                                {{ $entry->equipment_name ?? '—' }}
                                            </div>
                                            <div class="flex items-center flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500 dark:text-gray-400 mt-1.5">
                                                @if($entry->equipment_model)
                                                    <span class="inline-flex items-center gap-1.5">
                                                        <i class="fa-solid fa-microchip text-gray-400 w-3.5 text-center"></i>
                                                        <span class="font-medium">{{ $entry->equipment_model }}</span>
                                                    </span>
                                                @endif
                                                @if($entry->ip_address)
                                                    <span class="inline-flex items-center gap-1.5">
                                                        <i class="fa-solid fa-network-wired text-gray-400 w-3.5 text-center"></i>
                                                        <span class="font-mono">{{ $entry->ip_address }}</span>
                                                    </span>
                                                @endif
                                                @if($entry->equipment_role)
                                                    <span class="inline-flex items-center gap-1.5">
                                                        <i class="fa-solid fa-circle-nodes text-gray-400 w-3.5 text-center"></i>
                                                        <span>{{ $entry->equipment_role }}</span>
                                                    </span>
                                                @endif
                                                @if($entry->serial_number)
                                                    <span class="inline-flex items-center gap-1.5">
                                                        <i class="fa-solid fa-barcode text-gray-400 w-3.5 text-center"></i>
                                                        <span class="font-mono">{{ $entry->serial_number }}</span>
                                                    </span>
                                                @endif
                                                @if($entry->mac_address)
                                                    <span class="inline-flex items-center gap-1.5">
                                                        <i class="fa-solid fa-address-card text-gray-400 w-3.5 text-center"></i>
                                                        <span class="font-mono">{{ $entry->mac_address }}</span>
                                                    </span>
                                                @endif
                                                @if($entry->vendor)
                                                    <span class="inline-flex items-center gap-1.5">
                                                        <i class="fa-solid fa-industry text-gray-400 w-3.5 text-center"></i>
                                                        <span>{{ $entry->vendor }}</span>
                                                    </span>
                                                @endif
                                                @if($entry->installation_date)
                                                    <span class="inline-flex items-center gap-1.5">
                                                        <i class="fa-solid fa-calendar-check text-gray-400 w-3.5 text-center"></i>
                                                        <span>{{ $entry->installation_date->format('M d, Y') }}</span>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mx-4 mb-3 flex flex-wrap items-stretch gap-2 p-2.5 rounded-lg border {{ $style['metaBg'] }}">
                                    <div class="flex items-center gap-2 flex-1 min-w-[180px]">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-gradient-to-br from-sky-500 to-sky-700 text-white flex items-center justify-center text-xs font-bold shadow-sm">
                                            {{ $userInitial }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 leading-none mb-0.5">
                                                {{ __('Changed by') }}
                                            </div>
                                            <div class="text-xs font-semibold text-gray-800 dark:text-gray-100 truncate">
                                                {{ $userName }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="hidden sm:block w-px bg-gray-200 dark:bg-gray-700"></div>

                                    <div class="flex items-center gap-2 flex-1 min-w-[180px]">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 flex items-center justify-center">
                                            <i class="fa-regular fa-clock text-gray-500 dark:text-gray-400 text-xs"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 leading-none mb-0.5">
                                                {{ __('When') }}
                                            </div>
                                            <div class="text-xs font-semibold text-gray-800 dark:text-gray-100 truncate" title="{{ $entry->changed_at->format('Y-m-d H:i:s') }}">
                                                {{ ucfirst($entry->changed_at->diffForHumans()) }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="hidden sm:block w-px bg-gray-200 dark:bg-gray-700"></div>

                                    <div class="flex items-center gap-2 flex-1 min-w-[180px]">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 flex items-center justify-center">
                                            <i class="fa-regular fa-calendar text-gray-500 dark:text-gray-400 text-xs"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 leading-none mb-0.5">
                                                {{ __('Date') }}
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <div class="text-xs font-semibold text-gray-800 dark:text-gray-100 truncate font-mono">
                                                    {{ $entry->changed_at->format('M d, Y') }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400 font-mono ml-0.5">
                                                    {{ $entry->changed_at->format('H:i:s') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if($type === 'updated' && !empty($entry->changes))
                                    <div class="px-4 pb-4">
                                        <details class="group">
                                            <summary class="cursor-pointer list-none flex items-center gap-2 text-xs font-semibold {{ $style['diffTitle'] }} hover:opacity-80">
                                                <i class="fa-solid fa-chevron-right text-[10px] transition-transform group-open:rotate-90"></i>
                                                <i class="fa-solid fa-code-compare"></i>
                                                {{ __('View changes') }}
                                                <span class="text-[10px] font-normal opacity-75">
                                                    ({{ count($entry->changes) }} {{ trans_choice('Field|Fields', count($entry->changes)) }})
                                                </span>
                                            </summary>
                                            <div class="mt-2 p-3 {{ $style['diffBg'] }} border rounded-md">
                                                <ul class="space-y-1.5 text-xs">
                                                    @foreach($entry->changes as $field => $diff)
                                                        <li class="grid grid-cols-[120px_1fr] gap-3 items-start">
                                                            <span class="font-semibold text-gray-700 dark:text-gray-300 truncate">
                                                                {{ $fieldLabels[$field] ?? $field }}
                                                            </span>
                                                            @php
                                                                $oldRaw = $diff['old'] ?? null;
                                                                $newRaw = $diff['new'] ?? null;
                                                                if ($field === 'size_u') {
                                                                    $oldDisplay = ($oldRaw !== null && $oldRaw !== '')
                                                                        ? ('U' . $entry->position . ((int) $oldRaw > 1 ? ' - U' . ($entry->position + (int) $oldRaw - 1) : ''))
                                                                        : '∅';
                                                                    $newDisplay = ($newRaw !== null && $newRaw !== '')
                                                                        ? ('U' . $entry->position . ((int) $newRaw > 1 ? ' - U' . ($entry->position + (int) $newRaw - 1) : ''))
                                                                        : '∅';
                                                                } else {
                                                                    $oldDisplay = ($oldRaw !== null && $oldRaw !== '') ? $oldRaw : '∅';
                                                                    $newDisplay = ($newRaw !== null && $newRaw !== '') ? $newRaw : '∅';
                                                                }
                                                            @endphp
                                                            <span class="flex items-center gap-2 flex-wrap min-w-0">
                                                                <span class="px-1.5 py-0.5 bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 rounded decoration-red-400/60 truncate max-w-[200px]">
                                                                    {{ $oldDisplay }}
                                                                </span>
                                                                <i class="fa-solid fa-arrow-right text-[10px] text-gray-400"></i>
                                                                <span class="px-1.5 py-0.5 bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 rounded truncate max-w-[200px]">
                                                                    {{ $newDisplay }}
                                                                </span>
                                                            </span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </details>
                                    </div>
                                @elseif($type === 'created' && $entry->equipment_name)
                                    <div class="px-4 pb-4 text-xs text-green-700 dark:text-green-400 flex items-center gap-1.5 mt-4">
                                        <i class="fa-solid fa-circle-check"></i>
                                        {{ __('Equipment installed at this position.') }}
                                    </div>
                                @elseif($type === 'deleted')
                                    <div class="px-4 pb-4 text-xs text-red-700 dark:text-red-400 flex items-center gap-1.5 mt-4">
                                        <i class="fa-solid fa-circle-xmark"></i>
                                        {{ __('Equipment removed from this position.') }}
                                    </div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="mt-6">{{ $history->links() }}</div>
        @endif
    </div>
</x-admin-layout>
