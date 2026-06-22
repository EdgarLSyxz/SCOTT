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
        ],
    ]">

    @if ($racks->count())
        <x-slot name="action">
            <a href="{{ route('admin.rack-layout.create') }}"
                class="hidden sm:block text-white {{ Auth::user()?->area === 'DTH'
                ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-4 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800'
                : 'bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }} font-medium rounded-lg text-sm px-5 py-2 focus:outline-none shadow-xl">
                <i class="fa-solid fa-plus mr-1"></i>
                {{ __('Register new rack layout') }}
            </a>
        </x-slot>
        <a href="{{ route('admin.rack-layout.create') }}"
            class="mb-4 sm:hidden block text-center text-white {{ Auth::user()?->area === 'DTH'
            ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-4 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800'
            : 'bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }} font-medium rounded-lg text-sm px-5 py-2 focus:outline-none shadow-xl">
            <i class="fa-solid fa-plus mr-1"></i>
            {{ __('Register new rack layout') }}
        </a>

        <div class="bg-white dark:bg-gray-800 relative shadow-2xl rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full table-fixed text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs dark:text-white uppercase dark:bg-gray-600 shadow-2xl">
                        <tr>
                            <th scope="col" class="px-4 py-3 w-[250px]">
                                <i class="fa-solid fa-server mr-1"></i>
                                {{ __('Name') }}
                            </th>
                            <th scope="col" class="px-4 py-3 w-[250px]">
                                <i class="fa-solid fa-location-dot mr-1"></i>
                                {{ __('Location') }}
                            </th>
                            <th scope="col" class="px-4 py-3 w-[250px]">
                                <i class="fa-solid fa-hashtag mr-1"></i>
                                {{ __('Slots') }}
                            </th>
                            <th scope="col" class="px-4 py-3 w-[250px]">
                                <i class="fa-solid fa-check mr-1"></i>
                                {{ __('Occupied') }}
                            </th>
                            <th scope="col" class="px-4 py-3 w-[250px]">
                                <i class="fa-solid fa-toggle-on mr-1"></i>
                                {{ __('Status') }}
                            </th>
                            <th scope="col" class="px-4 py-3 text-center whitespace-nowrap w-[60px]"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($racks as $rack)
                            <tr onclick="window.location.href='{{ route('admin.rack-layout.show', $rack) }}'"
                                class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-600 text-black dark:text-white cursor-pointer group">
                                <td class="px-4 py-3 font-bold whitespace-nowrap">
                                    <a href="{{ route('admin.rack-layout.show', $rack) }}">
                                        {{ $rack->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $rack->location }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $rack->total_units }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $rack->occupied_positions_count }} / <b>{{ $rack->total_units }}</b></td>
                                <td class="px-4 py-3">
                                    @if($rack->is_active)
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-800 bg-green-200 rounded-full dark:bg-green-800 dark:text-green-200">
                                            <i class="fa-solid fa-check-circle mr-1.5"></i>
                                            {{ __('Active') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-800 bg-red-200 rounded-full dark:bg-red-800 dark:text-red-200">
                                            <i class="fa-solid fa-times-circle mr-1.5"></i>
                                            {{ __('Inactive') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span class="flex items-center h-full justify-center"
                                        style="height: 100%; min-height: 24px;">
                                        <i class="fa-solid fa-chevron-right transition-colors text-gray-300 group-hover:text-gray-700 dark:text-gray-500 dark:group-hover:text-gray-400"
                                        style="vertical-align: middle; font-size: 1.1em; line-height: 1;"></i>
                                    </span>
                                </td>
                                {{-- <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.rack-layout.show', $rack) }}"
                                        class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded shadow-sm text-gray-700 bg-white border border-gray-300 hover:bg-gray-50"
                                        onclick="event.stopPropagation();" aria-label="{{ __('View') }}">
                                        <i class="fa-solid fa-eye mr-1"></i> {{ __('View') }}
                                    </a>
                                    <a href="{{ route('admin.rack-layout.history', $rack) }}"
                                        class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded shadow-sm text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 ml-1"
                                        onclick="event.stopPropagation();" aria-label="{{ __('History') }}">
                                        <i class="fa-solid fa-clock-rotate-left mr-1"></i> {{ __('History') }}
                                    </a>
                                    <a href="{{ route('admin.rack-layout.export-pdf', $rack) }}"
                                        class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded shadow-sm text-white bg-[#9F24A5] border border-[#9F24A5] hover:bg-[#7a1d82] ml-1"
                                        onclick="event.stopPropagation();" aria-label="{{ __('PDF') }}">
                                        <i class="fa-solid fa-file-pdf mr-1"></i> {{ __('PDF') }}
                                    </a>
                                    @if(auth()->id() === 1)
                                        <form action="{{ route('admin.rack-layout.destroy-rack', $rack) }}" method="POST" class="inline ml-1"
                                                onsubmit="return confirm('{{ __('Delete this rack and all its history?') }}');" onsubmit="event.stopPropagation();">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded shadow-sm text-white bg-red-600 border border-red-600 hover:bg-red-700"
                                                    onclick="event.stopPropagation();" aria-label="{{ __('Delete') }}">
                                                <i class="fa-solid fa-trash mr-1"></i> {{ __('Delete') }}
                                            </button>
                                        </form>
                                    @endif
                                </td> --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400 shadow-xl"
            role="alert">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <div>
                    {{ __('There are no rack layouts registered in the database.') }}
                </div>
            </div>

            <div class="flex justify-center sm:justify-end">
                <a href="{{ route('admin.rack-layout.create') }}" class="text-white
                    {{ Auth::user()?->area === 'DTH'
                    ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-4 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800'
                    : 'bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }}
                    font-medium rounded-lg text-sm px-5 py-2 focus:outline-none shadow-xl">
                    <i class="fa-solid fa-plus mr-1"></i>
                    {{ __('Register new rack layout') }}
                </a>
            </div>
        </div>
    @endif
</x-admin-layout>
