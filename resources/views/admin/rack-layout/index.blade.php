<x-admin-layout>
    <x-slot name="breadcrumbs">
        <li>
            <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-gray-700">{{ __('Dashboard') }}</a>
        </li>
        <li><span class="text-gray-400 mx-1">/</span></li>
        <li class="text-gray-700 font-medium">{{ __('Rack Layout') }}</li>
    </x-slot>

    <x-slot name="action">
        @if(auth()->id() === 1)
            <a href="{{ route('admin.rack-layout.create') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-[#9F24A5] rounded-lg hover:bg-[#7a1d82] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#9F24A5]">
                <i class="fa-solid fa-plus mr-2"></i> {{ __('New rack') }}
            </a>
        @endif
    </x-slot>

    <div class="p-4 sm:p-6 bg-white rounded-lg shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">{{ __('Rack Layout') }}</h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ __('Manage rack inventory, assign equipment to each U position, and review change history.') }}
                </p>
            </div>
        </div>

        @if($racks->count() === 0)
            <div class="text-center py-12 text-gray-500">
                <i class="fa-solid fa-server text-4xl mb-3 text-gray-300"></i>
                <p>{{ __('No racks configured yet.') }}</p>
                @if(auth()->id() === 1)
                    <a href="{{ route('admin.rack-layout.create') }}"
                       class="inline-flex items-center mt-3 px-3 py-2 text-sm font-medium text-white bg-[#9F24A5] rounded-md hover:bg-[#7a1d82]">
                        {{ __('Create the first rack') }}
                    </a>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3">{{ __('Name') }}</th>
                            <th class="px-4 py-3">{{ __('Location') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('Total U') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('Occupied') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($racks as $rack)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-800">
                                    <a href="{{ route('admin.rack-layout.show', $rack) }}" class="hover:text-[#9F24A5]">
                                        {{ $rack->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $rack->location ?? '—' }}</td>
                                <td class="px-4 py-3 text-center text-gray-600">{{ $rack->total_units }}U</td>
                                <td class="px-4 py-3 text-center text-gray-600">{{ $rack->occupied_positions_count }}U</td>
                                <td class="px-4 py-3 text-center">
                                    @if($rack->is_active)
                                        <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800">{{ __('Active') }}</span>
                                    @else
                                        <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-700">{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.rack-layout.show', $rack) }}"
                                       class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">
                                        <i class="fa-solid fa-eye mr-1"></i> {{ __('View') }}
                                    </a>
                                    <a href="{{ route('admin.rack-layout.history', $rack) }}"
                                       class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 ml-1">
                                        <i class="fa-solid fa-clock-rotate-left mr-1"></i> {{ __('History') }}
                                    </a>
                                    <a href="{{ route('admin.rack-layout.export-pdf', $rack) }}"
                                       class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-white bg-[#9F24A5] border border-[#9F24A5] rounded hover:bg-[#7a1d82] ml-1">
                                        <i class="fa-solid fa-file-pdf mr-1"></i> {{ __('PDF') }}
                                    </a>
                                    @if(auth()->id() === 1)
                                        <form action="{{ route('admin.rack-layout.destroy-rack', $rack) }}" method="POST" class="inline ml-1"
                                              onsubmit="return confirm('{{ __('Delete this rack and all its history?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-white bg-red-600 border border-red-600 rounded hover:bg-red-700">
                                                <i class="fa-solid fa-trash mr-1"></i> {{ __('Delete') }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $racks->links() }}</div>
        @endif
    </div>
</x-admin-layout>
