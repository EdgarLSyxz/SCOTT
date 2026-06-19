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
            'name' => __('Rack layout'),
            'icon' => 'fa-solid fa-server',
            'route' => route('admin.rack-layout.index'),
        ],
        [
            'name' => __('Rack'),
            'icon' => 'fa-solid fa-circle-info',
        ],
    ]">


    <x-slot name="action">
        <div class="flex gap-2">
            <a href="{{ route('admin.rack-layout.history', $rack) }}"
               class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fa-solid fa-clock-rotate-left mr-2"></i> {{ __('History') }}
            </a>
            <a href="{{ route('admin.rack-layout.export-pdf', $rack) }}"
               class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-[#9F24A5] rounded-lg hover:bg-[#7a1d82]">
                <i class="fa-solid fa-file-pdf mr-2"></i> {{ __('Export PDF') }}
            </a>
        </div>
    </x-slot>

    <style>
        .rack-grid {
            display: grid;
            grid-template-columns: 60px 1fr 110px;
            gap: 4px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
        }
        .rack-pos-num {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            color: #475569;
            font-weight: 700;
            text-align: center;
            padding: 8px 4px;
            border-radius: 3px;
        }
        .rack-pos-cell {
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            background: #ffffff;
            min-height: 38px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            transition: background 0.15s;
        }
        .rack-pos-cell:hover { background: #faf5ff; }
        .rack-pos-cell.empty { background: #f8fafc; color: #94a3b8; font-style: italic; }
        .rack-pos-cell .label { font-weight: 700; color: #1e293b; }
        .rack-pos-cell .subtitle { font-size: 11px; color: #64748b; margin-top: 2px; }
        .rack-pos-action { display: flex; align-items: center; justify-content: center; gap: 4px; }
        .rack-pos-cell.bg-green { background: #bbf7d0; }
        .rack-pos-cell.bg-green .label { color: #14532d; }
        .rack-pos-cell.bg-red { background: #fecaca; }
        .rack-pos-cell.bg-red .label { color: #7f1d1d; }
        .rack-pos-cell.bg-blue { background: #bfdbfe; }
        .rack-pos-cell.bg-blue .label { color: #1e3a8a; }
        .rack-pos-cell.bg-yellow { background: #fde68a; }
        .rack-pos-cell.bg-yellow .label { color: #713f12; }
        .rack-pos-cell.bg-orange { background: #fed7aa; }
        .rack-pos-cell.bg-orange .label { color: #7c2d12; }
    </style>

    <div class="p-4 sm:p-6 bg-white rounded-lg shadow-sm">
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800">{{ $rack->name }}</h2>
            <p class="text-sm text-gray-500 mt-1">
                @if($rack->location) <span class="mr-3"><i class="fa-solid fa-location-dot mr-1"></i>{{ $rack->location }}</span> @endif
                <span class="mr-3"><i class="fa-solid fa-server mr-1"></i>{{ $rack->total_units }}U</span>
                <span class="mr-3"><i class="fa-solid fa-circle-check mr-1 text-green-600"></i>{{ $rack->occupied_positions_count }} {{ __('occupied') }}</span>
                @if($rack->description) <span class="text-gray-400">{{ $rack->description }}</span> @endif
            </p>
        </div>

        <div class="rack-grid">
            @foreach($positions as $idx => $equipment)
                @php
    $position = $idx + 1;
    $isEmpty = !$equipment || empty($equipment->equipment_name);
    $colorClass = $equipment && $equipment->color ? 'bg-' . $equipment->color : '';
                @endphp
                <div class="rack-pos-num">{{ $position }}</div>
                <div class="rack-pos-cell {{ $isEmpty ? 'empty' : '' }} {{ $colorClass }}">
                    @if($isEmpty)
                        <span>{{ __('Empty') }}</span>
                    @else
                        <div class="label">{{ $equipment->equipment_name }}</div>
                        <div class="subtitle">
                            {{ trim(implode(' · ', array_filter([$equipment->equipment_model, $equipment->ip_address]))) }}
                            @if($equipment->equipment_role)
                                <span class="text-purple-700 font-medium ml-1">— {{ $equipment->equipment_role }}</span>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="rack-pos-action">
                    <a href="{{ route('admin.rack-layout.position.edit', [$rack, $position]) }}"
                       class="inline-flex items-center justify-center w-7 h-7 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50"
                       title="{{ $isEmpty ? __('Assign equipment') : __('Edit position') }}">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                    <a href="{{ route('admin.rack-layout.position.history', [$rack, $position]) }}"
                       class="inline-flex items-center justify-center w-7 h-7 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50"
                       title="{{ __('Position history') }}">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</x-admin-layout>
