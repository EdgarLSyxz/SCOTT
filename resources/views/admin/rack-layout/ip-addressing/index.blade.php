<x-admin-layout :breadcrumbs="[
        ['name' => __('Dashboard'), 'icon' => 'fa-solid fa-wrench', 'route' => route('admin.dashboard')],
        ['name' => __('Devices'), 'icon' => 'fa-solid fa-hard-drive', 'route' => route('admin.devices.index')],
        ['name' => __('Rack layouts'), 'icon' => 'fa-solid fa-server', 'route' => route('admin.rack-layout.index')],
        ['name' => $rack->name, 'icon' => 'fa-solid fa-circle-info', 'route' => route('admin.rack-layout.show', $rack)],
        ['name' => __('IP Addressing'), 'icon' => 'fa-solid fa-globe'],
    ]">

    <x-slot name="action">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.rack-layout.show', $rack) }}"
               class="hidden sm:inline-flex items-center text-white bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 font-medium rounded-lg text-sm px-4 py-2">
                <i class="fa-solid fa-arrow-left mr-1.5"></i> {{ __('Go back') }}
            </a>
            @can('create', App\Models\RackIpRange::class)
                <a href="{{ route('admin.rack-layout.ip-addressing.create', $rack) }}"
                   class="inline-flex items-center text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-4 py-2 shadow-sm">
                    <i class="fa-solid fa-plus mr-1.5"></i> {{ __('Add IP range') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="w-full bg-white dark:bg-gray-800 rounded-lg shadow-2xl dark:shadow-none dark:border dark:border-gray-700 p-5">

        <div class="mb-5 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-300">
                        <i class="fa-solid fa-globe text-lg"></i>
                    </span>
                    {{ __('IP Addressing — :rack', ['rack' => $rack->name]) }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-13">
                    {{ __('CIDR ranges, masks and VLANs assigned to this rack.') }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 p-4 flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-300">
                    <i class="fa-solid fa-globe"></i>
                </span>
                <div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Total ranges') }}</div>
                </div>
            </div>
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 p-4 flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-300">
                    <i class="fa-solid fa-circle-check"></i>
                </span>
                <div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['active'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Active') }}</div>
                </div>
            </div>
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 p-4 flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-300">
                    <i class="fa-solid fa-tag"></i>
                </span>
                <div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['vlans'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Distinct VLANs') }}</div>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.rack-layout.ip-addressing.index', $rack) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
            <div class="md:col-span-2">
                <x-label for="search" value="{{ __('Search') }}" />
                <x-input id="search" name="search" type="text"
                    :value="request('search')" placeholder="{{ __('CIDR, mask, VLAN, description…') }}" />
            </div>
            <div>
                <x-label for="vlan" value="{{ __('VLAN') }}" />
                <select id="vlan" name="vlan"
                    class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">{{ __('All VLANs') }}</option>
                    @foreach($vlans as $v)
                        <option value="{{ $v }}" {{ (string) request('vlan') === (string) $v ? 'selected' : '' }}>VLAN {{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-label for="status" value="{{ __('Status') }}" />
                <select id="status" name="status"
                    class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">{{ __('All') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                </select>
            </div>
            <div class="md:col-span-4 flex justify-end gap-2">
                <a href="{{ route('admin.rack-layout.ip-addressing.index', $rack) }}"
                   class="inline-flex items-center text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg text-sm px-4 py-2 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                    <i class="fa-solid fa-times mr-1.5"></i>{{ __('Clear') }}
                </a>
                <button type="submit"
                        class="inline-flex items-center text-white bg-primary-700 hover:bg-primary-800 rounded-lg text-sm px-4 py-2 shadow-sm">
                    <i class="fa-solid fa-magnifying-glass mr-1.5"></i>{{ __('Filter') }}
                </button>
            </div>
        </form>

        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-3 w-12">#</th>
                        <th class="px-4 py-3">{{ __('CIDR range') }}</th>
                        <th class="px-4 py-3">{{ __('Mask') }}</th>
                        <th class="px-4 py-3">{{ __('VLAN') }}</th>
                        <th class="px-4 py-3">{{ __('Description') }}</th>
                        <th class="px-4 py-3">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-right w-32">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ipRanges as $range)
                        <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">#{{ $range->id }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-globe text-primary-500 text-xs"></i>
                                    <span class="font-mono font-semibold text-gray-900 dark:text-white">{{ $range->cidr_range }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-mono text-gray-700 dark:text-gray-300 text-xs">{{ $range->mask }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($range->vlan)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-mono font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300">
                                        VLAN {{ $range->vlan }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300 max-w-md">
                                @if($range->description)
                                    <span class="line-clamp-2" title="{{ $range->description }}">{{ $range->description }}</span>
                                @else
                                    <span class="text-gray-400 italic text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($range->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span>{{ __('Active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-500 mr-1.5"></span>{{ __('Inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-1">
                                    @can('update', $range)
                                        <a href="{{ route('admin.rack-layout.ip-addressing.edit', [$rack, $range]) }}"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-md text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition"
                                           title="{{ __('Edit') }}">
                                            <i class="fa-solid fa-pen text-xs"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $range)
                                        <form action="{{ route('admin.rack-layout.ip-addressing.destroy', [$rack, $range]) }}" method="POST" class="inline"
                                              onsubmit="return confirm('{{ __('Delete this IP range?') }}')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-md text-red-600 dark:text-red-300 bg-white dark:bg-gray-700 border border-red-200 dark:border-red-800 hover:bg-red-50 dark:hover:bg-red-900/30 transition"
                                                    title="{{ __('Delete') }}">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                                <i class="fa-solid fa-globe text-4xl mb-3 block text-gray-300 dark:text-gray-600"></i>
                                <p class="text-sm">{{ __('No IP ranges registered yet for this rack.') }}</p>
                                @can('create', App\Models\RackIpRange::class)
                                    <a href="{{ route('admin.rack-layout.ip-addressing.create', $rack) }}"
                                       class="inline-flex items-center mt-3 text-white bg-primary-700 hover:bg-primary-800 rounded-lg text-xs px-3 py-1.5">
                                        <i class="fa-solid fa-plus mr-1.5"></i>{{ __('Add the first one') }}
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $ipRanges->links() }}
        </div>
    </div>
</x-admin-layout>
