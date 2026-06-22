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
               class="hidden sm:inline-flex items-center text-white bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-4 py-2 transition">
                <i class="fa-solid fa-arrow-left mr-1.5"></i>
                {{ __('Go back') }}
            </a>
            <a href="{{ route('admin.rack-layout.history', $rack) }}"
               class="inline-flex items-center text-white
                    {{ Auth::user()?->area === 'DTH'
                        ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-secondary-300'
                        : 'bg-primary-700 hover:bg-primary-800 focus:ring-primary-300' }}
                    focus:ring-4 focus:outline-none font-medium rounded-lg text-sm px-4 py-2 transition">
                <i class="fa-solid fa-clock-rotate-left mr-1.5"></i> {{ __('History') }}
            </a>
            <a href="{{ route('admin.rack-layout.export-pdf', $rack) }}"
               class="inline-flex items-center text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 transition">
                <i class="fa-solid fa-file-pdf mr-1.5"></i> {{ __('Export PDF') }}
            </a>
        </div>
    </x-slot>

    <div class="w-full bg-white rounded-lg shadow-2xl dark:border p-5 dark:bg-gray-800 dark:border-gray-700">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-300 flex items-center gap-2">
                    <i class="fa-solid fa-server"></i>
                    {{ $rack->name }}
                </h2>
                <p class="text-sm text-gray-500 mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                    @if($rack->location)
                        <span><i class="fa-solid fa-location-dot mr-1.5 text-gray-400"></i>{{ $rack->location }}</span>
                    @endif
                    <span><i class="fa-solid fa-layer-group mr-1.5 text-gray-400"></i>{{ $rack->total_units }} {{ __('Units') }}</span>
                    <span class="text-gray-400">
                        <i class="fa-solid fa-circle-check text-gray-400 mr-1.5"></i>{{ $rack->occupied_positions_count }} / <b>{{ $rack->total_units }}</b> {{ __('Occupied') }}
                    </span>
                    <span class="text-gray-400">
                        <i class="fa-solid fa-circle-xmark text-gray-400 mr-1.5"></i>{{ $rack->total_units - $rack->occupied_positions_count }} / <b>{{ $rack->total_units }}</b> {{ __('Empty') }}
                    </span>
                    @if($rack->description)
                        <span class="text-gray-400 italic">{{ $rack->description }}</span>
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('Legend') }}:</span>
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-green-100 text-green-800">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>{{ __('Highlighted') }}
                </span>
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-amber-100 text-amber-800">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>{{ __('Caution') }}
                </span>
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-orange-100 text-orange-800">
                    <span class="w-2 h-2 rounded-full bg-orange-500"></span>{{ __('Warning') }}
                </span>
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-red-100 text-red-800">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span>{{ __('Critical') }}
                </span>
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-blue-100 text-blue-800">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>{{ __('Info') }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-[60px_1fr_100px] gap-1 font-mono text-sm">
            @foreach($positions as $idx => $equipment)
                @php
                    $position = $idx + 1;
                    $isEmpty = ! $equipment || empty($equipment->equipment_name);
                    $color = $equipment && $equipment->color ? $equipment->color : null;

                    $cellClasses = match($color) {
                        'green'  => 'bg-green-200 hover:bg-green-300 border-green-300 text-green-900 dark:bg-green-700 dark:border-green-600 dark:text-green-100',
                        'red'    => 'bg-red-200 hover:bg-red-300 border-red-300 text-red-900 dark:bg-red-700 dark:border-red-600 dark:text-red-100',
                        'blue'   => 'bg-blue-200 hover:bg-blue-300 border-blue-300 text-blue-900 dark:bg-blue-700 dark:border-blue-600 dark:text-blue-100',
                        'yellow' => 'bg-yellow-200 hover:bg-yellow-300 border-yellow-300 text-yellow-900 dark:bg-yellow-600 dark:border-yellow-500 dark:text-yellow-900',
                        'orange' => 'bg-orange-200 hover:bg-orange-300 border-orange-300 text-orange-900 dark:bg-orange-700 dark:border-orange-600 dark:text-orange-100',
                        default  => $isEmpty
                            ? 'bg-gray-50 hover:bg-gray-100 border-gray-200 text-gray-400 italic dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300'
                            : 'bg-white hover:bg-purple-50 border-gray-200 text-gray-800 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200',
                    };
                @endphp

                <div class="flex items-center justify-center bg-gray-100 border border-gray-200 text-gray-600 font-bold rounded py-2 px-1">
                    <b>{{ $position }}</b>
                </div>

                <div class="flex flex-col justify-center border rounded px-3 py-2 min-h-[44px] {{ $cellClasses }}">
                    @if($isEmpty)
                        <span class="text-xs">{{ __('Empty') }}</span>
                    @else
                        <div class="font-bold text-sm leading-tight">{{ $equipment->equipment_name }}</div>
                        <div class="text-xs opacity-80 mt-0.5 leading-tight">
                            {{ trim(implode(' · ', array_filter([$equipment->equipment_model, $equipment->ip_address]))) }}
                            @if($equipment->equipment_role)
                                <span class="font-semibold ml-1">— {{ $equipment->equipment_role }}</span>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-center gap-1">
                    <a href="{{ route('admin.rack-layout.position.edit', [$rack, $position]) }}"
                       class="inline-flex items-center justify-center w-12 h-12 text-sm rounded focus:ring-4 focus:outline-none font-medium shadow-sm bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
                       onclick="event.stopPropagation();" title="{{ $isEmpty ? __('Assign equipment') : __('Edit position') }}" aria-label="{{ $isEmpty ? __('Assign equipment') : __('Edit position') }}">
                        <i class="fa-solid fa-pen"></i>
                    </a>

                    <a href="{{ route('admin.rack-layout.position.history', [$rack, $position]) }}"
                       class="inline-flex items-center justify-center w-12 h-12 text-sm rounded focus:ring-4 focus:outline-none font-medium shadow-sm {{ Auth::user()?->area === 'DTH' ? 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600' : 'bg-primary-700 text-white border border-primary-700 hover:bg-primary-800' }}"
                       onclick="event.stopPropagation();" title="{{ __('Position history') }}" aria-label="{{ __('Position history') }}">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</x-admin-layout>
