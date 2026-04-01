<div class="bg-white dark:bg-gray-800 relative shadow-2xl rounded-lg overflow-hidden">
    <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-2 sm:p-4">
        <div class="w-full md:w-1/2">
            <form class="flex items-center" onsubmit="event.preventDefault();">
                <label for="simple-search" class="sr-only">Search</label>
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor"
                            viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input type="text" wire:model.live="search" id="simple-search" autocomplete="off"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-xs sm:text-sm rounded-lg block w-full pl-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white
                            {{ Auth::user()?->area === 'DTH'
                                ? 'focus:ring-secondary-500 focus:border-secondary-500 dark:focus:ring-secondary-500 dark:focus:border-secondary-500'
                                : 'focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500' }}"
                        placeholder="{{ __('Search') }}" required autofocus>
                </div>
            </form>
        </div>
        <div
            class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">
            @can('create', App\Models\Channel::class)
                <a href="{{ route('admin.channels.create') }}"
                    class="block w-full sm:w-auto text-white
                        {{ Auth::user()?->area === 'DTH'
                            ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-4 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800'
                            : 'bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }}
                        font-medium rounded-lg text-xs sm:text-sm px-5 py-2 focus:outline-none shadow-xl text-center">
                    <i class="fa-solid fa-plus mr-1"></i>
                    {{ __('Register new channel') }}
                </a>
            @endcan
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3 w-full sm:w-auto">
                <button id="filterDropdownButton" data-dropdown-toggle="filterDropdown"
                    class="w-full sm:w-full md:w-auto flex items-center justify-center py-2 px-4 text-xs sm:text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700"
                    type="button">
                    <i class="fa-solid fa-filter mr-1.5"></i>
                    {{ __('Filter') }}
                    <i class="fa-solid fa-chevron-down ml-1.5"></i>
                </button>
                <div id="filterDropdown" class="z-10 hidden w-64 p-3 bg-white rounded-lg shadow dark:bg-gray-700">
                    <div class="mb-2">
                        <x-checkbox id="active-filter" wire:model.live="showActive"></x-checkbox>
                        <label for="active-filter" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300 cursor-pointer">
                            {{ __('Show only active channels') }}
                        </label>
                    </div>
                    <x-checkbox id="inactive-filter" wire:model.live="showInactive"></x-checkbox>
                    <label for="inactive-filter" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300 cursor-pointer">
                        {{ __('Show only inactive channels') }}
                    </label>
                </div>
                <button wire:click="resetFilters"
                    class="w-full sm:w-full md:w-auto flex justify-center items-center gap-2 py-2 px-4 text-xs sm:text-sm font-medium text-gray-700 border rounded-lg border-gray-200 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                    <i class="fa-solid fa-rotate-left"></i>
                    {{ __('Reset table') }}
                </button>
            </div>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full table-fixed text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs dark:text-white uppercase dark:bg-gray-600 shadow-2xl">
                <tr>
                    <th scope="col" class="px-4 py-3 w-[120px]">
                        <i class="fa-solid fa-image mr-1"></i>
                        {{ __('Image') }}
                    </th>
                    <th scope="col" class="px-4 py-3 w-[275px]">
                        <i class="fa-solid fa-tv mr-1"></i>
                        {{ __('Channel') }}
                    </th>
                    <th class="py-3 px-4 text-left cursor-pointer w-[150px]"
                        wire:click="toggleOriginFilter">
                        <i class="fa-solid fa-arrow-right-arrow-left mr-1"></i>
                        <span class="text-gray-500 dark:text-white">
                            @if ($originFilter)
                                {{ $originFilter }}
                            @else
                                {{ __('All Origins') }}
                            @endif
                            <i class="ml-1 fa-solid fa-sort"></i>
                        </span>
                    </th>
                    <th class="py-3 px-4 text-left cursor-pointer w-[225px]"
                        wire:click="toggleCategoryFilter">
                        <i class="fa-solid fa-list mr-1"></i>
                        <span class="text-gray-500 dark:text-white">
                            @if ($categoryFilter)
                                {{ __($categoryFilter) }}
                            @else
                                {{ __('All Categories') }}
                            @endif
                            <i class="ml-1 fa-solid fa-sort"></i>
                        </span>
                    </th>
                    <th scope="col" class="px-4 py-3 w-[225px] text-left cursor-pointer"
                        wire:click="toggleAreaFilter">
                        <i class="fa-solid fa-building mr-1"></i>
                        <span class="text-gray-500 dark:text-white">
                            @if ($areaFilter && $areaFilter !== 'all')
                                {{ $areaFilter }}
                            @else
                                {{ __('All Areas') }}
                            @endif
                            <i class="ml-1 fa-solid fa-sort"></i>
                        </span>
                    </th>
                    <th scope="col" class="px-4 py-3 w-[150px]">
                        <i class="fa-solid fa-toggle-on mr-1"></i>
                        {{ __('Status') }}
                    </th>
                    <th scope="col" class="px-4 py-3 w-[80px]">
                        <span class="sr-only">
                            <i class="fa-solid fa-sliders-h mr-1"></i>
                            {{ __('Options') }}
                        </span>
                    </th>
                </tr>
            </thead>
            <tbody x-data="{ openDropdown: null }">
                @forelse ($channels as $channel)
                    <tr onclick="window.location.href='{{ route('admin.channels.show', $channel) }}'"
                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-600 text-black dark:text-white cursor-pointer">
                        <td class="px-4 py-3 w-[120px]">
                            <img src="{{ $channel->image_url ? asset('storage/' . $channel->image_url) : asset('img/no-image.png') }}"
                                alt="{{ $channel->name }}" class="w-10 h-10 object-center object-contain rounded-sm">
                        </td>
                        <th scope="row"
                            class="px-4 py-2.5 font-bold text-gray-900 dark:text-white w-[275px] truncate overflow-hidden">
                            {{ $channel->number }} {{ $channel->name }}
                        </th>
                        <td class="px-4 py-2.5 w-[150px] truncate whitespace-nowrap overflow-hidden">
                            {{ $channel->origin }}
                        </td>
                        <td class="px-4 py-2.5 w-[225px]">
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium truncate whitespace-nowrap overflow-hidden
                                    {{ Auth::user()?->area === 'DTH'
                                        ? 'text-secondary-800 bg-secondary-200 dark:bg-secondary-800 dark:text-secondary-200'
                                        : 'text-primary-800 bg-primary-200 dark:bg-primary-800 dark:text-primary-200' }}
                                    rounded-full">
                                @switch($channel->category)
                                    @case('Standard TV Channel')
                                        <i class="fa-solid fa-tv mr-1.5"></i>
                                    @break

                                    @case('Stingray Music')
                                        <i class="fa-solid fa-music mr-1.5"></i>
                                    @break

                                    @case('RESTART/CUTV')
                                        <i class="fa-solid fa-repeat mr-1.5"></i>
                                    @break

                                    @case('FAST')
                                        <i class="fa-solid fa-bolt mr-1.5"></i>
                                    @break

                                    @case('Radio TV Channel')
                                        <i class="fa-solid fa-radio mr-1.5 pb-[1px]"></i>
                                    @break

                                    @case('Learning TV Channel')
                                        <i class="fa-solid fa-book-open mr-1.5"></i>
                                    @break

                                    @default
                                        <i class="fa-solid fa-layer-group mr-1.5"></i>
                                @endswitch
                                {{ __($channel->category) }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 w-[150px] truncate whitespace-nowrap overflow-hidden">
                            @if($channel->area === 'DTH/OTT')
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-secondary-800 bg-secondary-200 dark:bg-secondary-800 dark:text-secondary-200 rounded-full mr-2">
                                    <i class="fa-solid fa-satellite-dish mr-1.5"></i>
                                    {{ __('DTH') }}
                                </span>
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-primary-800 bg-primary-200 dark:bg-primary-800 dark:text-primary-200 rounded-full">
                                    <i class="fa-solid fa-cube mr-1.5"></i>
                                    {{ __('OTT') }}
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full
                                        {{ $channel->area === 'DTH'
                                            ? 'text-secondary-800 bg-secondary-200 dark:bg-secondary-800 dark:text-secondary-200'
                                            : ($channel->area === 'OTT'
                                                ? 'text-primary-800 bg-primary-200 dark:bg-primary-800 dark:text-primary-200'
                                                : 'text-gray-800 bg-gray-200 dark:bg-gray-800 dark:text-gray-200') }}">
                                    @if($channel->area === 'DTH')
                                        <i class="fa-solid fa-satellite-dish mr-1.5"></i>
                                    @elseif($channel->area === 'OTT')
                                        <i class="fa-solid fa-cube mr-1.5"></i>
                                    @else
                                        <i class="fa-solid fa-layer-group mr-1.5"></i>
                                    @endif
                                    {{ $channel->area ?? __('N/A') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 w-[150px] whitespace-nowrap">
                            @if ($channel->status === 1)
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-800 bg-green-200 rounded-full dark:bg-green-800 dark:text-green-200">
                                    <i class="fa-solid fa-check-circle mr-1"></i>
                                    {{ __('Active') }}
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-800 bg-red-200 rounded-full dark:bg-red-800 dark:text-red-200">
                                    <i class="fa-solid fa-times-circle mr-1"></i>
                                    {{ __('Inactive') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 flex items-center justify-end w-full">
                            <button
                                @click="openDropdown = (openDropdown === {{ $channel->id }} ? null : {{ $channel->id }})"
                                @click.stop
                                class="inline-flex items-center p-3 text-sm font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none dark:text-gray-400 dark:hover:text-gray-100"
                                type="button">
                                <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a 2 2 0 100-4 2 2 0 000 4z" />
                                </svg>
                            </button>
                            <div x-show="openDropdown === {{ $channel->id }}" @click.away="openDropdown = null"
                                x-transition
                                class="absolute z-50 w-44 bg-white rounded divide-y divide-gray-300 shadow-2xl dark:bg-gray-700 dark:divide-gray-600">
                                <ul class="flex flex-col items-start py-1 text-sm text-gray-700 dark:text-gray-200">
                                    <li class="w-full">
                                        <a href="#" title="{{ __('Open Multicast') }}"
                                            @click.prevent.stop="downloadM3U('{{ $channel->url }}', '{{ $channel->number }}', '{{ $channel->name }}'); openDropdown = null"
                                            class="flex items-center w-full py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                                            <i class="fa-solid fa-video mr-2"></i>
                                            {{ __('Multicast') }}
                                        </a>
                                    </li>
                                    <li class="w-full">
                                        <a href="{{ route('admin.channels.show', $channel) }}"
                                            title="{{ __('Show channel information') }}"
                                            class="flex items-center w-full py-2 px-3.5 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                                            <i class="fa-solid fa-eye mr-2"></i>
                                            {{ __('Show') }}
                                        </a>
                                    </li>
                                    @can('edit', $channel)
                                        <li class="w-full">
                                            <a href="{{ route('admin.channels.edit', $channel) }}"
                                                title="{{ __('Edit channel') }}"
                                                class="flex items-center w-full py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                                                <i class="fa-solid fa-pen-to-square mr-2"></i>
                                                {{ __('Edit') }}
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                                @can('delete', $channel)
                                    <div class="w-full py-1">
                                        <form action="{{ route('admin.channels.destroy', $channel) }}" method="POST"
                                            id="delete-form-{{ $channel->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                @click.stop.prevent="confirmDelete({{ $channel->id }}); openDropdown = null"
                                                title="{{ __('Delete channel') }}"
                                                class="flex items-center w-full text-left py-2 px-4 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">
                                                <i class="fa-solid fa-trash-can mr-2"></i>
                                                {{ __('Delete') }}
                                            </button>
                                        </form>
                                    </div>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="bg-white dark:bg-gray-800 pt-8 text-center">
                                <i class="fa-solid fa-circle-info mr-1"></i>
                                {{ __('There are no channels that match your search.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-2 sm:p-4">
            {{ $channels->links() }}
        </div>
    </div>
    @push('js')
        <script>
            function confirmDelete(channelID) {
                Swal.fire({
                    title: "{{ __('Are you sure?') }}",
                    text: "{{ __('You wont be able to revert this!') }}",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "{{ __('Yes, delete it!') }}",
                    cancelButtonText: "{{ __('Cancel') }}"
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-form-' + channelID).submit();
                    }
                });
            }
        </script>
    @endpush

<script>
    function downloadM3U(url, number, name) {
        const content = url + "\n";
        let cleanName = (number ? number + '_' : '') + (name ? name : 'canal');
        cleanName = cleanName.replace(/[^a-zA-Z0-9-_]/g, '_');
        const filename = cleanName + '.m3u';
        const blob = new Blob([content], { type: "audio/x-mpegurl" });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        setTimeout(() => {
            URL.revokeObjectURL(a.href);
            document.body.removeChild(a);
        }, 100);
    }
</script>
