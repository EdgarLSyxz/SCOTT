<div>
    @php
        $allowedIds = [1, 2, 3, 5, 7, 8];
        $userId = Auth::id();
        $isAllowedId = $userId && in_array((int) $userId, $allowedIds, true);
    @endphp

    @if($isAllowedId || Auth::user()?->can('create', App\Models\Device::class))
        <x-slot name="action">
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.devices.create') }}"
                    class="hidden lg:block text-white {{ Auth::user()?->area === 'DTH'
                    ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-4 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800'
                    : 'bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }} font-medium rounded-lg text-sm px-5 py-2 focus:outline-none shadow-xl">
                    <i class="fa-solid fa-plus mr-1"></i>
                    {{ __('Register new device') }}
                </a>
                @if($isAllowedId)
                    <a href="{{ route('admin.devices.packages') }}"
                        class="hidden lg:block text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800 font-medium rounded-lg text-sm px-5 py-2 focus:outline-none shadow-xl">
                        <i class="fa-solid fa-boxes-packing mr-1"></i>
                        {{ __('Packages') }}
                    </a>
                @endif
                @if($isAllowedId)
                    <a href="{{ route('admin.devices.log-analytics') }}"
                        class="hidden lg:block text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800 font-medium rounded-lg text-sm px-5 py-2 focus:outline-none shadow-xl">
                        <i class="fa-solid fa-chart-line mr-1"></i>
                        {{ __('CDN Traffic') }}
                    </a>
                @endif
                @if($isAllowedId)
                    <a href="{{ route('admin.rack-layout.index') }}"
                        class="hidden lg:block text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800 font-medium rounded-lg text-sm px-5 py-2 focus:outline-none shadow-xl">
                        <i class="fa-solid fa-server mr-1"></i>
                        {{ __('Rack layouts') }}
                    </a>
                @endif
                @if($isAllowedId)
                    <a href="{{ route('admin.devices.monthly-downloads') }}"
                        class="hidden lg:block text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800 font-medium rounded-lg text-sm px-5 py-2 focus:outline-none shadow-xl">
                        <i class="fa-solid fa-download mr-1"></i>
                        {{ __('Monthly downloads') }}
                    </a>
                @endif
            </div>
        </x-slot>
        <a href="{{ route('admin.devices.create') }}"
            class="mb-4 lg:hidden block text-center text-white {{ Auth::user()?->area === 'DTH'
            ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-4 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800'
            : 'bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }} font-medium rounded-lg text-sm px-5 py-2 focus:outline-none shadow-xl">
            <i class="fa-solid fa-plus mr-1"></i>
            {{ __('Register new device') }}
        </a>
        @if($isAllowedId)
            <a href="{{ route('admin.devices.packages') }}"
                class="mb-4 lg:hidden block text-center text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800 font-medium rounded-lg text-sm px-5 py-2 focus:outline-none shadow-xl">
                <i class="fa-solid fa-boxes-packing mr-1"></i>
                {{ __('Packages') }}
            </a>
        @endif
        @if($isAllowedId)
            <a href="{{ route('admin.devices.log-analytics') }}"
                class="mb-4 lg:hidden block text-center text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800 font-medium rounded-lg text-sm px-5 py-2 focus:outline-none shadow-xl">
                <i class="fa-solid fa-chart-line mr-1"></i>
                {{ __('CDN Traffic') }}
            </a>
        @endif
        @if($isAllowedId)
            <a href="{{ route('admin.rack-layout.index') }}"
                class="mb-4 lg:hidden block text-center text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800 font-medium rounded-lg text-sm px-5 py-2 focus:outline-none shadow-xl">
                <i class="fa-solid fa-server mr-1"></i>
                {{ __('Rack layouts') }}
            </a>
        @endif
        @if($isAllowedId)
            <a href="{{ route('admin.devices.monthly-downloads') }}"
                class="mb-4 lg:hidden block text-center text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800 font-medium rounded-lg text-sm px-5 py-2 focus:outline-none shadow-xl">
                <i class="fa-solid fa-download mr-1"></i>
                {{ __('Monthly downloads') }}
            </a>
        @endif
    @endif

    <div class="overflow-x-auto rounded-lg bg-white dark:bg-gray-800">
        <table class="w-full table-fixed text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs dark:text-white uppercase dark:bg-gray-600 shadow-2xl">
                <tr>
                    <th scope="col" class="px-4 py-3 w-[100px] text-left">
                        <i class="fa-solid fa-image mr-1"></i>
                        {{ __('Image') }}
                    </th>
                    <th scope="col" class="px-4 py-3 w-[250px] text-left">
                        <i class="fa-solid fa-hard-drive mr-1"></i>
                        {{ __('Device') }}
                    </th>
                    <th scope="col" class="px-4 py-3 w-[150px] cursor-pointer text-left"
                    wire:click="toggleProtocolFilter">
                        <i class="fa-solid fa-server mr-1"></i>
                        <span class="text-gray-500 dark:text-white">
                            @if ($protocolFilter)
                                {{ $protocolFilter }}
                            @else
                                {{ __('All Protocols') }}
                            @endif
                            <i class="ml-1 fa-solid fa-sort"></i>
                        </span>
                    </th>
                    <th scope="col" class="px-4 py-3 w-[150px] text-left">
                        <i class="fa-solid fa-shield-halved mr-1"></i>
                        {{ __('DRM') }}
                    </th>
                    <th scope="col" class="px-4 py-3 w-[150px] text-left">
                        <i class="fa-solid fa-building mr-1"></i>
                        {{ __('Area') }}
                    </th>
                    <th scope="col" class="px-4 py-3 w-[150px] text-left">
                        <i class="fa-solid fa-toggle-on mr-1"></i>
                        {{ __('Status') }}
                    </th>
                    <th scope="col" class="px-4 py-3 w-[80px] text-left">
                        <span class="sr-only">
                            <i class="fa-solid fa-sliders-h mr-1"></i>
                            {{ __('Options') }}
                        </span>
                    </th>
                </tr>
            </thead>
            <tbody x-data="{ openDropdown: null }">
                @forelse ($devices as $device)
                    <tr onclick="window.location.href='{{ route('admin.devices.show', $device) }}'"
                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-600 text-black dark:text-white cursor-pointer group">
                        <td class="px-4 py-3">
                            <img src="{{ $device->image_url ? asset('storage/' . $device->image_url) : asset('img/no-image.png') }}"
                                alt="{{ $device->name }}" class="w-8 h-8 object-center object-contain rounded-sm">
                        </td>
                        <th scope="row"
                            class="px-4 py-2.5 font-bold text-gray-900 dark:text-white truncate overflow-hidden">
                            {{ $device->name }}
                        </th>
                        <td class="px-4 py-2.5 truncate whitespace-nowrap overflow-hidden">
                            @if(strtoupper($device->protocol) === 'HLS')
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-800 bg-blue-200 dark:bg-blue-800 dark:text-blue-200 rounded-full">
                                    <i class="fa-solid fa-tv mr-1.5"></i>
                                    {{ __('HLS') }}
                                </span>
                            @elseif(strtoupper($device->protocol) === 'DASH')
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-800 bg-blue-200 dark:bg-blue-800 dark:text-blue-200 rounded-full">
                                    <i class="fa-solid fa-computer mr-1.5"></i>
                                    {{ __('DASH') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 truncate whitespace-nowrap overflow-hidden">
                            @if($device->drm)
                                @if(strtoupper($device->drm) === 'VERIMATRIX')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-pink-800 bg-pink-200 dark:bg-pink-800 dark:text-pink-200 rounded-full">
                                        <i class="fa-solid fa-certificate mr-1.5"></i>
                                        {{ __('Verimatrix') }}
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-pink-800 bg-pink-200 dark:bg-pink-800 dark:text-pink-200 rounded-full">
                                        <i class="fa-brands fa-google mr-1.5"></i>
                                        {{ __('Widevine') }}
                                    </span>
                                @endif
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 truncate whitespace-nowrap overflow-hidden">
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-primary-800 bg-primary-200 dark:bg-primary-800 dark:text-primary-200 rounded-full">
                                <i class="fa-solid fa-cube mr-1.5"></i>
                                {{ __('OTT') }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 whitespace-nowrap">
                            @if ($device->status == 1)
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
                        <td class="px-4 py-4 flex items-center justify-end align-middle w-full">
                            <span class="flex items-center h-full justify-center" style="height: 100%; min-height: 24px;">
                                <i class="fa-solid fa-chevron-right transition-colors text-gray-300 group-hover:text-gray-700 dark:text-gray-500 dark:group-hover:text-gray-400"
                                    style="vertical-align: middle; font-size: 1.1em; line-height: 1;"></i>
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="bg-white dark:bg-gray-800 py-5 text-center">
                            <i class="fa-solid fa-circle-info mr-1"></i>
                            {{ __('There are no channels that match your search.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
