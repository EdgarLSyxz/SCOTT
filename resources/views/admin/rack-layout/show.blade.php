@php
$isDth = Auth::user()?->area === 'DTH';

$userAccent = $isDth
    ? [
        'bg' => 'bg-secondary-700',
        'bgHover' => 'hover:bg-secondary-800',
        'border' => 'border-secondary-500',
        'ring' => 'focus:ring-secondary-300',
        'textLight' => 'text-secondary-700',
        'textDark' => 'dark:text-secondary-400',
        'borderHi' => 'dark:border-secondary-500',
    ]
    : [
        'bg' => 'bg-primary-700',
        'bgHover' => 'hover:bg-primary-800',
        'border' => 'border-primary-500',
        'ring' => 'focus:ring-primary-300',
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
               class="hidden sm:inline-flex items-center text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 transition">
                <i class="fa-solid fa-pen mr-1.5"></i> {{ __('Edit rack') }}
            </a>
            <button type="button" onclick="confirmDeleteRack()"
                    class="inline-flex items-center text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 transition">
                <i class="fa-solid fa-trash-can mr-1.5"></i> {{ __('Delete rack') }}
            </button>
        </div>
    </x-slot>

    <div class="w-full bg-white dark:bg-gray-800 rounded-lg shadow-2xl dark:shadow-none dark:border dark:border-gray-700 p-5">

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
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
                        <span class="text-gray-400 dark:text-gray-500 italic">{{ $rack->description }}</span>
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

        <div class="grid grid-cols-[60px_1fr_100px] gap-1 font-mono text-sm">

            @foreach($positions as $idx => $equipment)
                @php
                    $position = $idx + 1;
                    $isEmpty = !$equipment || empty($equipment->equipment_name);
                    $color = $equipment && $equipment->color ? $equipment->color : null;

                    $indicator = match ($color) {
                        'green' => ['dot' => 'bg-green-500', 'border' => 'border-green-400 dark:border-green-600', 'tone' => 'text-green-700 dark:text-green-300'],
                        'red' => ['dot' => 'bg-red-500', 'border' => 'border-red-400 dark:border-red-600', 'tone' => 'text-red-700 dark:text-red-300'],
                        'blue' => ['dot' => 'bg-blue-500', 'border' => 'border-blue-400 dark:border-blue-600', 'tone' => 'text-blue-700 dark:text-blue-300'],
                        'yellow' => ['dot' => 'bg-amber-500', 'border' => 'border-amber-400 dark:border-amber-600', 'tone' => 'text-amber-700 dark:text-amber-300'],
                        'orange' => ['dot' => 'bg-orange-500', 'border' => 'border-orange-400 dark:border-orange-600', 'tone' => 'text-orange-700 dark:text-orange-300'],
                        default => null,
                    };

                    $baseClasses = $isEmpty
                        ? 'bg-gray-50 dark:bg-gray-700 text-gray-400 dark:text-gray-500'
                        : 'bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200';

                    $borderClass = $indicator
                        ? $indicator['border']
                        : 'border-gray-200 dark:border-gray-700';
                @endphp

                <div class="flex items-center justify-center bg-gray-50 border border-gray-200 dark:bg-gray-700 dark:border-gray-700 text-gray-600 dark:text-gray-400 font-bold rounded py-2 px-1">
                    <b>{{ $position }}</b>
                </div>

                <div class="flex flex-col justify-center border rounded px-3 py-2 min-h-[44px]
                        {{ $baseClasses }} {{ $borderClass }}
                        @if($indicator) {{ $userAccent['border'] }} dark:{{ str_replace('border-', 'border-', $userAccent['borderHi']) }} @endif">
                    @if($isEmpty)
                        <span class="text-xs italic">{{ __('Empty') }}</span>
                    @else
                        <div class="flex items-center gap-2">
                            @if($indicator)
                                <span class="w-2 h-2 rounded-full {{ $indicator['dot'] }} flex-shrink-0"></span>
                            @else
                                <span class="w-2 h-2 rounded-full bg-gray-300 dark:bg-gray-600 flex-shrink-0"></span>
                            @endif
                            <div class="font-bold text-sm leading-tight {{ $indicator['tone'] ?? '' }}">{{ $equipment->equipment_name }}</div>
                        </div>
                        <div class="text-xs opacity-80 mt-0.5 leading-tight pl-4">
                            {{ trim(implode(' · ', array_filter([$equipment->equipment_model, $equipment->ip_address]))) }}
                            @if($equipment->equipment_role)
                                <span class="font-semibold ml-1">— {{ $equipment->equipment_role }}</span>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-center gap-1">
                    <a href="{{ route('admin.rack-layout.position.edit', [$rack, $position]) }}"
                       class="inline-flex items-center justify-center w-12 h-12 text-sm rounded focus:ring-4 focus:outline-none font-medium shadow-sm
                              bg-gray-50 text-gray-600 border border-gray-200 hover:bg-gray-100 hover:text-gray-900 hover:border-gray-400
                              dark:bg-gray-700 dark:text-gray-400 dark:border-gray-700 dark:hover:bg-gray-700 dark:hover:text-white dark:hover:border-gray-500
                              {{ $userAccent['ring'] }}"
                       title="{{ $isEmpty ? __('Assign equipment') : __('Edit position') }}" aria-label="{{ $isEmpty ? __('Assign equipment') : __('Edit position') }}">
                        <i class="fa-solid fa-pen"></i>
                    </a>

                    <a href="{{ route('admin.rack-layout.position.history', [$rack, $position]) }}"
                       class="inline-flex items-center justify-center w-12 h-12 text-sm rounded focus:ring-4 focus:outline-none font-medium shadow-sm
                              bg-gray-50 text-gray-600 border border-gray-200 hover:bg-gray-100 hover:text-gray-900 hover:border-gray-400
                              dark:bg-gray-700 dark:text-gray-400 dark:border-gray-700 dark:hover:bg-gray-700 dark:hover:text-white dark:hover:border-gray-500
                              {{ $userAccent['ring'] }}"
                       title="{{ __('Position history') }}" aria-label="{{ __('Position history') }}">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </a>
                </div>
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
