@php

    $isDth = Auth::user()?->area === 'DTH';

    $userAccent = $isDth
        ? [
            'bg' => 'bg-secondary-700',
            'bgHover' => 'hover:bg-secondary-800',
            'border' => 'border-secondary-500',
            'ring' => 'focus:ring-secondary-400',
            'textLight' => 'text-secondary-700',
            'textDark' => 'dark:text-secondary-400',
            'borderHi' => 'dark:border-secondary-500',
        ]
        : [
            'bg' => 'bg-primary-700',
            'bgHover' => 'hover:bg-primary-800',
            'border' => 'border-primary-500',
            'ring' => 'focus:ring-primary-400',
            'textLight' => 'text-primary-700',
            'textDark' => 'dark:text-primary-400',
            'borderHi' => 'dark:border-primary-500',
        ];
@endphp

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
        ],
    ]">

    <x-slot name="action">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.rack-layout.index') }}"
               class="hidden sm:inline-flex items-center text-white bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 font-medium rounded-lg text-sm px-4 py-2">
                <i class="fa-solid fa-arrow-left mr-1.5"></i>
                {{ __('Go back') }}
            </a>
            <a href="{{ route('admin.rack-layout.history', $rack) }}"
               class="hidden sm:inline-flex items-center text-white bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 font-medium rounded-lg text-sm px-4 py-2">
                <i class="fa-solid fa-clock-rotate-left mr-1.5"></i> {{ __('History') }}
            </a>
            <a href="{{ route('admin.rack-layout.export-pdf', $rack) }}"
               class="hidden sm:inline-flex items-center text-white bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 font-medium rounded-lg text-sm px-4 py-2">
                <i class="fa-solid fa-file-pdf mr-1.5"></i> {{ __('Export PDF') }}
            </a>
            <a href="{{ route('admin.rack-layout.edit-rack', $rack) }}"
               class="hidden sm:inline-flex items-center text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">
                <i class="fa-solid fa-pen mr-1.5"></i> {{ __('Edit rack') }}
            </a>
            <button type="button" onclick="confirmDeleteRack()"
                    class="inline-flex items-center text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2">
                <i class="fa-solid fa-trash-can mr-1.5"></i> {{ __('Delete rack') }}
            </button>
        </div>
    </x-slot>

    <div class="w-full bg-white dark:bg-gray-800 rounded-lg shadow-2xl dark:shadow-none dark:border dark:border-gray-700 p-5">

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2 mb-1">
                    <i class="fa-solid fa-server"></i>
                    {{ $rack->name }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                    @if($rack->location)
                        <span><i class="fa-solid fa-location-dot mr-1.5 text-gray-400 dark:text-gray-500"></i>{{ $rack->location }}</span>
                    @endif
                    <span><i class="fa-solid fa-layer-group mr-1.5 text-gray-400 dark:text-gray-500"></i>{{ $rack->total_units }} {{ __('Units') }}</span>
                    <span class="text-green-600 dark:text-green-400 font-medium">
                        <i class="fa-solid fa-circle-check mr-1.5"></i>{{ $rack->occupied_positions_count }} / {{ $rack->total_units }} {{ __('Occupied') }}
                    </span>
                    <span class="text-gray-500 dark:text-gray-400">
                        <i class="fa-solid fa-circle-xmark mr-1.5"></i>{{ $rack->total_units - $rack->occupied_positions_count }} / {{ $rack->total_units }} {{ __('Empty') }}
                    </span>
                    @if($rack->description)
                        <span class="text-gray-400 dark:text-gray-500 italic">
                            <i class="fa-solid fa-align-left mr-1.5"></i>
                            {{ $rack->description }}
                        </span>
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('Legend') }}:</span>
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>{{ __('Highlighted') }}
                </span>
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>{{ __('Caution') }}
                </span>
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                    <span class="w-2 h-2 rounded-full bg-orange-500"></span>{{ __('Warning') }}
                </span>
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span>{{ __('Critical') }}
                </span>
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>{{ __('Info') }}
                </span>
            </div>
        </div>

        <div class="space-y-2.5">
            @php
                $skipSlots = [];
            @endphp
            @foreach($positions as $idx => $equipment)
                @php
                    if (in_array($idx, $skipSlots, true)) {
                        continue;
                    }

                    $position = $idx + 1;
                    $isEmpty = !$equipment || empty($equipment->equipment_name);
                    $color = $equipment && $equipment->color ? $equipment->color : null;
                    $sizeU = $equipment ? max(1, (int) $equipment->size_u) : 1;
                    $isSpanned = $sizeU > 1;
                    if ($isSpanned) {
                        for ($s = 1; $s < $sizeU; $s++) {
                            $skipSlots[] = $idx + $s;
                        }
                    }

                    $indicator = match ($color) {
                        'green' => [
                            'dot' => 'bg-green-500',
                            'bg' => 'bg-green-50/50 dark:bg-green-900/10',
                            'border' => 'border-green-200 dark:border-green-800',
                            'accent' => 'border-l-green-500',
                            'tone' => 'text-green-800 dark:text-green-300',
                            'pill' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                        ],
                        'red' => [
                            'dot' => 'bg-red-500',
                            'bg' => 'bg-red-50/50 dark:bg-red-900/10',
                            'border' => 'border-red-200 dark:border-red-800',
                            'accent' => 'border-l-red-500',
                            'tone' => 'text-red-800 dark:text-red-300',
                            'pill' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                        ],
                        'blue' => [
                            'dot' => 'bg-blue-500',
                            'bg' => 'bg-blue-50/50 dark:bg-blue-900/10',
                            'border' => 'border-blue-200 dark:border-blue-800',
                            'accent' => 'border-l-blue-500',
                            'tone' => 'text-blue-800 dark:text-blue-300',
                            'pill' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                        ],
                        'yellow' => [
                            'dot' => 'bg-amber-500',
                            'bg' => 'bg-amber-50/50 dark:bg-amber-900/10',
                            'border' => 'border-amber-200 dark:border-amber-800',
                            'accent' => 'border-l-amber-500',
                            'tone' => 'text-amber-800 dark:text-amber-300',
                            'pill' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                        ],
                        'orange' => [
                            'dot' => 'bg-orange-500',
                            'bg' => 'bg-orange-50/50 dark:bg-orange-900/10',
                            'border' => 'border-orange-200 dark:border-orange-800',
                            'accent' => 'border-l-orange-500',
                            'tone' => 'text-orange-800 dark:text-orange-300',
                            'pill' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
                        ],
                        default => null,
                    };

                    $slotClasses = $isEmpty
                        ? 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700'
                        : ($indicator
                            ? $indicator['bg'] . ' ' . $indicator['border']
                            : 'bg-white dark:bg-gray-800 ' . $userAccent['border']);

                    $pillClass = $indicator['pill'] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
                @endphp

                @if($isSpanned)
                    @php
                        $slotBg = $color
                            ? match ($color) {
                                'green' => 'bg-green-500/15 dark:bg-green-500/20 border-green-300 dark:border-green-700',
                                'red' => 'bg-red-500/15 dark:bg-red-500/20 border-red-300 dark:border-red-700',
                                'blue' => 'bg-blue-500/15 dark:bg-blue-500/20 border-blue-300 dark:border-blue-700',
                                'yellow' => 'bg-amber-500/15 dark:bg-amber-500/20 border-amber-300 dark:border-amber-700',
                                'orange' => 'bg-orange-500/15 dark:bg-orange-500/20 border-orange-300 dark:border-orange-700',
                                default => 'bg-gray-100 dark:bg-gray-700/60 border-gray-200 dark:border-gray-600',
                            }
                            : 'bg-gray-100 dark:bg-gray-700/60 border-gray-200 dark:border-gray-600';

                        $slotDot = $color
                            ? match ($color) {
                                'green' => 'bg-green-500',
                                'red' => 'bg-red-500',
                                'blue' => 'bg-blue-500',
                                'yellow' => 'bg-amber-500',
                                'orange' => 'bg-orange-500',
                                default => 'bg-gray-400',
                            }
                            : 'bg-gray-400';
                    @endphp

                    <div class="grid grid-cols-[64px_1fr_auto] sm:grid-cols-[72px_1fr_auto] gap-3 items-stretch">
                        <div class="relative flex flex-col items-center justify-center rounded-xl border-2 {{ $slotBg }} text-gray-700 dark:text-gray-200 py-2 px-1 shadow-sm overflow-hidden">
                            <span class="text-[10px] font-bold uppercase tracking-wider opacity-70">{{ __('Unit') }}</span>
                            <span class="text-sm font-black tracking-tight leading-none mt-0.5">U{{ $position }} - U{{ $position + $sizeU - 1 }}</span>
                        </div>

                        <div class="flex flex-col justify-center rounded-xl border {{ $slotClasses }} {{ $indicator ? 'border-l-4 ' . $indicator['accent'] : '' }} px-4 py-3 shadow-sm-all duration-200">
                            <div class="flex items-center gap-3 h-full">
                                <span class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-lg bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 shadow-sm">
                                    @if($indicator)
                                        <i class="fa-solid fa-server {{ $indicator['tone'] }} text-base"></i>
                                    @else
                                        <i class="fa-solid fa-server text-gray-500 dark:text-gray-400 text-base"></i>
                                    @endif
                                </span>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 truncate {{ $indicator['tone'] ?? '' }}">
                                        {{ $equipment->equipment_name }}
                                    </h3>

                                    @if($equipment->equipment_model || $equipment->vendor || $equipment->ip_address || $equipment->mac_address || $equipment->serial_number || $equipment->equipment_role || $equipment->installation_date)
                                        <div class="flex items-center flex-wrap gap-x-4 gap-y-1 text-[11px] text-gray-600 dark:text-gray-400 mt-1.5">
                                            @if($equipment->equipment_model)
                                                <span class="inline-flex items-center gap-1.5" title="{{ __('Model') }}">
                                                    <i class="fa-solid fa-microchip text-gray-400 dark:text-gray-500"></i>
                                                    <span class="font-medium">{{ $equipment->equipment_model }}</span>
                                                </span>
                                            @endif
                                            @if($equipment->vendor)
                                                <span class="inline-flex items-center gap-1.5" title="{{ __('Vendor') }}">
                                                    <i class="fa-solid fa-industry text-gray-400 dark:text-gray-500"></i>
                                                    <span>{{ $equipment->vendor }}</span>
                                                </span>
                                            @endif
                                            @if($equipment->ip_address)
                                                <span class="inline-flex items-center gap-1.5" title="{{ __('IP Address') }}">
                                                    <i class="fa-solid fa-network-wired text-gray-400 dark:text-gray-500"></i>
                                                    <span class="font-mono">{{ $equipment->ip_address }}</span>
                                                </span>
                                            @endif
                                            @if($equipment->mac_address)
                                                <span class="inline-flex items-center gap-1.5" title="{{ __('MAC Address') }}">
                                                    <i class="fa-solid fa-address-card text-gray-400 dark:text-gray-500"></i>
                                                    <span class="font-mono">{{ $equipment->mac_address }}</span>
                                                </span>
                                            @endif
                                            @if($equipment->serial_number)
                                                <span class="inline-flex items-center gap-1.5" title="{{ __('Serial Number') }}">
                                                    <i class="fa-solid fa-barcode text-gray-400 dark:text-gray-500"></i>
                                                    <span class="font-mono">{{ $equipment->serial_number }}</span>
                                                </span>
                                            @endif
                                            @if($equipment->equipment_role)
                                                <span class="inline-flex items-center gap-1.5" title="{{ __('Function') }}">
                                                    <i class="fa-solid fa-user-gear text-gray-400 dark:text-gray-500"></i>
                                                    <span class="font-semibold">{{ $equipment->equipment_role }}</span>
                                                </span>
                                            @endif
                                            @if($equipment->installation_date)
                                                <span class="inline-flex items-center gap-1.5" title="{{ __('Installation date') }}">
                                                    <i class="fa-solid fa-calendar-check text-gray-400 dark:text-gray-500"></i>
                                                    <span>{{ $equipment->installation_date->format('M d, Y') }}</span>
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col justify-center gap-2">
                            <a href="{{ route('admin.rack-layout.position.edit', [$rack, $position]) }}"
                               class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-sm font-medium shadow-sm
                                bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600
                                hover:bg-gray-50 hover:text-gray-900 hover:border-gray-400 hover:shadow
                                dark:hover:bg-gray-600 dark:hover:text-white dark:hover:border-gray-500-all duration-200"
                               title="{{ __('Edit position') }}"
                               aria-label="{{ __('Edit position') }}">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            <a href="{{ route('admin.rack-layout.position.history', [$rack, $position]) }}"
                               class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-sm font-medium shadow-sm
                                bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600
                                hover:bg-gray-50 hover:text-gray-900 hover:border-gray-400 hover:shadow
                                dark:hover:bg-gray-600 dark:hover:text-white dark:hover:border-gray-500-all duration-200"
                               title="{{ __('Position history') }}"
                               aria-label="{{ __('Position history') }}">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </a>
                        </div>
                    </div>
                @else
                    @php
                        $slotBg = $color
                            ? match ($color) {
                                'green' => 'bg-green-500/15 dark:bg-green-500/20 border-green-300 dark:border-green-700',
                                'red' => 'bg-red-500/15 dark:bg-red-500/20 border-red-300 dark:border-red-700',
                                'blue' => 'bg-blue-500/15 dark:bg-blue-500/20 border-blue-300 dark:border-blue-700',
                                'yellow' => 'bg-amber-500/15 dark:bg-amber-500/20 border-amber-300 dark:border-amber-700',
                                'orange' => 'bg-orange-500/15 dark:bg-orange-500/20 border-orange-300 dark:border-orange-700',
                                default => 'bg-gray-100 dark:bg-gray-700/60 border-gray-200 dark:border-gray-600',
                            }
                            : 'bg-gray-100 dark:bg-gray-700/60 border-gray-200 dark:border-gray-600';

                        $slotDot = $color
                            ? match ($color) {
                                'green' => 'bg-green-500',
                                'red' => 'bg-red-500',
                                'blue' => 'bg-blue-500',
                                'yellow' => 'bg-amber-500',
                                'orange' => 'bg-orange-500',
                                default => 'bg-gray-400',
                            }
                            : 'bg-gray-400';
                    @endphp
                    <div class="group grid grid-cols-[64px_1fr_auto] sm:grid-cols-[72px_1fr_auto] gap-3 items-stretch">

                        <div class="relative flex flex-col items-center justify-center rounded-xl border-2 {{ $slotBg }} text-gray-700 dark:text-gray-200 py-2 px-1 shadow-sm overflow-hidden">
                            <span class="text-[10px] font-bold uppercase tracking-wider opacity-70">{{ __('Unit') }}</span>
                            <span class="text-sm font-black tracking-tight leading-none mt-0.5">U{{ $position }}</span>
                        </div>

                        <div class="flex flex-col justify-center rounded-xl border {{ $slotClasses }} {{ $indicator ? 'border-l-4 ' . $indicator['accent'] : '' }} px-4 py-3 shadow-sm-all duration-200 group-hover:shadow-md">
                            @if($isEmpty)
                                <div class="flex items-center gap-3 text-gray-400 dark:text-gray-500">
                                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-gray-100 dark:bg-gray-700/60">
                                        <i class="fa-regular fa-square text-sm"></i>
                                    </span>
                                    <div>
                                        <div class="text-sm font-medium">{{ __('Empty slot') }}</div>
                                        <div class="text-xs opacity-75">{{ __('No equipment assigned') }}</div>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-start gap-3">
                                    <span class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-lg bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 shadow-sm">
                                        @if($indicator)
                                            <i class="fa-solid fa-server {{ $indicator['tone'] }} text-base"></i>
                                        @else
                                            <i class="fa-solid fa-server text-gray-500 dark:text-gray-400 text-base"></i>
                                        @endif
                                    </span>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 truncate {{ $indicator['tone'] ?? '' }}">
                                            {{ $equipment->equipment_name }}
                                        </h3>

                                        @if($equipment->equipment_model || $equipment->vendor || $equipment->ip_address || $equipment->mac_address || $equipment->serial_number || $equipment->equipment_role || $equipment->installation_date)
                                            <div class="flex items-center flex-wrap gap-x-4 gap-y-1 text-[11px] text-gray-600 dark:text-gray-400 mt-1.5">
                                                @if($equipment->equipment_model)
                                                    <span class="inline-flex items-center gap-1.5" title="{{ __('Model') }}">
                                                        <i class="fa-solid fa-microchip text-gray-400 dark:text-gray-500"></i>
                                                        <span class="font-medium">{{ $equipment->equipment_model }}</span>
                                                    </span>
                                                @endif
                                                @if($equipment->vendor)
                                                    <span class="inline-flex items-center gap-1.5" title="{{ __('Vendor') }}">
                                                        <i class="fa-solid fa-industry text-gray-400 dark:text-gray-500"></i>
                                                        <span>{{ $equipment->vendor }}</span>
                                                    </span>
                                                @endif
                                                @if($equipment->ip_address)
                                                    <span class="inline-flex items-center gap-1.5" title="{{ __('IP Address') }}">
                                                        <i class="fa-solid fa-network-wired text-gray-400 dark:text-gray-500"></i>
                                                        <span class="font-mono">{{ $equipment->ip_address }}</span>
                                                    </span>
                                                @endif
                                                @if($equipment->mac_address)
                                                    <span class="inline-flex items-center gap-1.5" title="{{ __('MAC Address') }}">
                                                        <i class="fa-solid fa-address-card text-gray-400 dark:text-gray-500"></i>
                                                        <span class="font-mono">{{ $equipment->mac_address }}</span>
                                                    </span>
                                                @endif
                                                @if($equipment->serial_number)
                                                    <span class="inline-flex items-center gap-1.5" title="{{ __('Serial Number') }}">
                                                        <i class="fa-solid fa-barcode text-gray-400 dark:text-gray-500"></i>
                                                        <span class="font-mono">{{ $equipment->serial_number }}</span>
                                                    </span>
                                                @endif
                                                @if($equipment->equipment_role)
                                                    <span class="inline-flex items-center gap-1.5" title="{{ __('Function') }}">
                                                        <i class="fa-solid fa-user-gear text-gray-400 dark:text-gray-500"></i>
                                                        <span class="font-semibold">{{ $equipment->equipment_role }}</span>
                                                    </span>
                                                @endif
                                                @if($equipment->installation_date)
                                                    <span class="inline-flex items-center gap-1.5" title="{{ __('Installation date') }}">
                                                        <i class="fa-solid fa-calendar-check text-gray-400 dark:text-gray-500"></i>
                                                        <span>{{ $equipment->installation_date->format('M d, Y') }}</span>
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col justify-center gap-2">
                            <a href="{{ route('admin.rack-layout.position.edit', [$rack, $position]) }}"
                               class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-sm font-medium shadow-sm
                                bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600
                                hover:bg-gray-50 hover:text-gray-900 hover:border-gray-400 hover:shadow
                                dark:hover:bg-gray-600 dark:hover:text-white dark:hover:border-gray-500-all duration-200"
                               title="{{ $isEmpty ? __('Assign equipment') : __('Edit position') }}"
                               aria-label="{{ $isEmpty ? __('Assign equipment') : __('Edit position') }}">
                                <i class="fa-solid {{ $isEmpty ? 'fa-plus' : 'fa-pen' }}"></i>
                            </a>

                            <a href="{{ route('admin.rack-layout.position.history', [$rack, $position]) }}"
                               class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-sm font-medium shadow-sm
                                bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600
                                hover:bg-gray-50 hover:text-gray-900 hover:border-gray-400 hover:shadow
                                dark:hover:bg-gray-600 dark:hover:text-white dark:hover:border-gray-500-all duration-200"
                               title="{{ __('Position history') }}"
                               aria-label="{{ __('Position history') }}">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </a>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <form action="{{ route('admin.rack-layout.destroy-rack', $rack) }}" method="POST" id="delete-rack-form">
        @csrf
        @method('DELETE')
    </form>

    @push('js')
        <script>
            function confirmDeleteRack() {
                const equipmentCount = {{ $rack->equipment->count() }};
                const historyCount = {{ $rack->history->count() }};
                const positionInfo = equipmentCount > 0
                    ? `{{ __('This rack has :n equipment item(s) and :h history record(s) that will be permanently deleted.', ['n' => '__EQ__', 'h' => '__HQ__']) }}`
                        .replace('__EQ__', equipmentCount)
                        .replace('__HQ__', historyCount)
                    : `{{ __('This action cannot be undone.') }}`;

                Swal.fire({
                    title: "{{ __('Are you sure?') }}",
                    text: positionInfo,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "{{ __('Yes, delete it!') }}",
                    cancelButtonText: "{{ __('Cancel') }}",
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-rack-form').submit();
                    }
                });
            }
        </script>
    @endpush
</x-admin-layout>
