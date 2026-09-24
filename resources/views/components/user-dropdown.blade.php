<x-dropdown align="right" width="56">
    @php
        $authUser = auth()->user();
        $area = $authUser?->area ?? null;
    @endphp
    <x-slot name="trigger">
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <button
                class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 {{ $area === 'DTH' ? 'focus:ring-secondary-300 dark:focus:ring-secondary-600' : 'focus:ring-primary-300 dark:focus:ring-primary-600' }} transition shadow-sm hover:shadow-md">
                <img class="h-9 w-9 rounded-full object-cover border border-gray-300 dark:border-gray-700"
                    src="{{ $authUser?->profile_photo_url }}" alt="{{ $authUser?->name }}" />
            </button>
        @else
            <button type="button"
                class="inline-flex items-center px-3 py-2 rounded-lg text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 {{ $area === 'DTH' ? 'focus:ring-secondary-300 dark:focus:ring-secondary-600' : 'focus:ring-primary-300 dark:focus:ring-primary-600' }} shadow-sm transition">
                <span class="font-medium">{{ $authUser?->name }}</span>
                <i class="fa-solid fa-chevron-down ml-2 text-xs opacity-70"></i>
            </button>
        @endif
    </x-slot>

    <x-slot name="content">
        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-600 dark:bg-gray-700 rounded-t-lg">
            <div class="text-sm font-semibold text-gray-900 dark:text-white text-center truncate">
                {{ $authUser?->name }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 text-center truncate">
                {{ $authUser?->email }}
            </div>
        </div>

        <div class="py-2 space-y-1">
            <x-dropdown-link href="{{ route('profile.show') }}" class="group">
                <i @class([
                    'fa-solid fa-user mr-2 text-gray-400 transition',
                    'group-hover:text-primary-500' => $area === 'OTT',
                    'group-hover:text-secondary-500' => $area === 'DTH',
                ])></i>
                {{ __('Profile') }}
            </x-dropdown-link>

            <x-dropdown-link href="{{ route('dashboard') }}" class="group">
                <i @class([
                    'fa-solid fa-house mr-2 text-gray-400 transition',
                    'group-hover:text-primary-500' => $area === 'OTT',
                    'group-hover:text-secondary-500' => $area === 'DTH',
                ])></i>
                {{ __('Home') }}
            </x-dropdown-link>

            <x-dropdown-link href="{{ route('admin.dashboard') }}" class="group">
                <i @class([
                    'fa-solid fa-wrench mr-2 text-gray-400 transition',
                    'group-hover:text-primary-500' => $area === 'OTT',
                    'group-hover:text-secondary-500' => $area === 'DTH',
                ])></i>
                {{ __('Dashboard') }}
            </x-dropdown-link>

            @php
                $canSwitch = $authUser && ($authUser->can_switch_area ?? false) && ($authUser->status ?? false);
                $nextArea = $authUser?->area === 'OTT' ? 'DTH' : 'OTT';
            @endphp

            @if ($canSwitch)
                <form method="POST" action="{{ route('admin.user.switch-area', ['area' => $nextArea]) }}" x-data>
                    @csrf
                    <x-dropdown-link href="#" @click.prevent="$root.submit();" class="group">
                        <i @class([
                            'fa-solid fa-arrows-rotate mr-2 text-gray-400 transition',
                            'group-hover:text-primary-500' => $authUser?->area === 'OTT',
                            'group-hover:text-secondary-500' => $authUser?->area === 'DTH',
                        ])></i>
                        {{ $authUser?->area === 'OTT' ? __('Switch to DTH') : __('Switch to OTT') }}
                    </x-dropdown-link>
                </form>
            @endif

            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                <x-dropdown-link href="{{ route('api-tokens.index') }}" class="group">
                    <i @class([
                        'fa-solid fa-key mr-2 text-gray-400 transition',
                        'group-hover:text-primary-500' => $area === 'OTT',
                        'group-hover:text-secondary-500' => $area === 'DTH',
                    ])></i>
                    {{ __('API Tokens') }}
                </x-dropdown-link>
            @endif
        </div>

        <div class="border-t border-gray-100 dark:border-gray-600"></div>

        <form method="POST" action="{{ route('logout') }}" x-data>
            @csrf
            <x-dropdown-link href="{{ route('logout') }}" @click.prevent="$root.submit();"
                class="text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition group">
                <i
                    class="fa-solid fa-arrow-right-from-bracket mr-2 text-red-400 group-hover:text-red-500 transition"></i>
                {{ __('Log Out') }}
            </x-dropdown-link>
        </form>
    </x-slot>
</x-dropdown>
