<x-admin-layout :breadcrumbs="[
        ['name' => __('Dashboard'), 'icon' => 'fa-solid fa-wrench', 'route' => route('admin.dashboard')],
        ['name' => __('Devices'), 'icon' => 'fa-solid fa-hard-drive', 'route' => route('admin.devices.index')],
        ['name' => __('Rack layouts'), 'icon' => 'fa-solid fa-server', 'route' => route('admin.rack-layout.index')],
        ['name' => $rack->name, 'icon' => 'fa-solid fa-circle-info', 'route' => route('admin.rack-layout.show', $rack)],
        ['name' => __('Cables'), 'icon' => 'fa-solid fa-cable-car'],
    ]">

    <x-slot name="action">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.rack-layout.show', $rack) }}"
               class="hidden sm:inline-flex items-center text-white bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 font-medium rounded-lg text-sm px-4 py-2">
                <i class="fa-solid fa-arrow-left mr-1.5"></i> {{ __('Go back') }}
            </a>
            <a href="{{ route('admin.rack-layout.cables.create', $rack) }}"
               class="inline-flex items-center text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 font-medium rounded-lg text-sm px-4 py-2">
                <i class="fa-solid fa-plus mr-1.5"></i> {{ __('Add cable') }}
            </a>
        </div>
    </x-slot>

    <div class="w-full bg-white dark:bg-gray-800 rounded-lg shadow-2xl dark:shadow-none dark:border dark:border-gray-700 p-5">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-cable-car text-primary-500"></i>
                    {{ __('Routing table — :rack', ['rack' => $rack->name]) }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('Physical cable connections leaving this rack. Total: :n', ['n' => $cables->total()]) }}
                </p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.rack-layout.cables.index', $rack) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
            <div class="md:col-span-2">
                <x-label for="search" value="{{ __('Search') }}" />
                <x-input id="search" name="search" type="text"
                    :value="request('search')" placeholder="{{ __('Destination, port, notes…') }}" />
            </div>
            <div>
                <x-label for="vlan" value="{{ __('VLAN') }}" />
                <x-input id="vlan" name="vlan" type="number" min="1" max="4094" :value="request('vlan')" />
            </div>
            <div>
                <x-label for="cable_type" value="{{ __('Cable type') }}" />
                <select id="cable_type" name="cable_type"
                    class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">{{ __('All') }}</option>
                    @foreach($cableTypes as $value => $label)
                        <option value="{{ $value }}" {{ request('cable_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-4 flex justify-end gap-2">
                <a href="{{ route('admin.rack-layout.cables.index', $rack) }}"
                   class="inline-flex items-center text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg text-sm px-4 py-2 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                    <i class="fa-solid fa-times mr-1.5"></i>{{ __('Clear') }}
                </a>
                <button type="submit"
                        class="inline-flex items-center text-white bg-primary-700 hover:bg-primary-800 rounded-lg text-sm px-4 py-2">
                    <i class="fa-solid fa-magnifying-glass mr-1.5"></i>{{ __('Filter') }}
                </button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">{{ __('Origin') }}</th>
                        <th class="px-4 py-3">{{ __('Destination') }}</th>
                        <th class="px-4 py-3">{{ __('VLAN') }}</th>
                        <th class="px-4 py-3">{{ __('Type') }}</th>
                        <th class="px-4 py-3">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cables as $cable)
                        @php
                            $rowColor = $cable->color;
                            $rowAccent = match($rowColor) {
                                'green' => 'border-l-green-500',
                                'red' => 'border-l-red-500',
                                'blue' => 'border-l-blue-500',
                                'yellow' => 'border-l-amber-500',
                                'orange' => 'border-l-orange-500',
                                default => 'border-l-gray-300 dark:border-l-gray-600',
                            };
                        @endphp
                        <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 border-l-4 {{ $rowAccent }}">
                            <td class="px-4 py-3 font-mono text-gray-500 dark:text-gray-400">{{ $cable->id }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-gray-900 dark:text-white">U{{ $cable->source_position }}</span>
                                    @if($cable->source_port)
                                        <span class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $cable->source_port }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $cable->destination_label }}</span>
                                    @if($cable->destination_ip)
                                        <span class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $cable->destination_ip }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($cable->vlan)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300">VLAN {{ $cable->vlan }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">{{ $cableTypes[$cable->cable_type] ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if($cable->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                        <i class="fa-solid fa-circle-check mr-1"></i>{{ __('Active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                        <i class="fa-solid fa-circle-pause mr-1"></i>{{ __('Inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('admin.rack-layout.cables.edit', [$rack, $cable]) }}"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-md text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600"
                                       title="{{ __('Edit') }}">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </a>
                                    <form action="{{ route('admin.rack-layout.cables.destroy', [$rack, $cable]) }}" method="POST" class="inline"
                                          onsubmit="return confirm('{{ __('Delete this cable?') }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-md text-red-600 dark:text-red-300 bg-white dark:bg-gray-700 border border-red-200 dark:border-red-800 hover:bg-red-50 dark:hover:bg-red-900/30"
                                                title="{{ __('Delete') }}">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                <i class="fa-solid fa-cable-car text-2xl mb-2 block"></i>
                                {{ __('No cables registered yet for this rack.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $cables->links() }}
        </div>
    </div>
</x-admin-layout>
