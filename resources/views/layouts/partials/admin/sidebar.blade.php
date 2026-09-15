@php
    $links = [
        [
            'name' => __('Home'),
            'icon' => 'fa-solid fa-house',
            'route' => route('dashboard'),
            'active' => request()->routeIs('dashboard'),
            'divider' => true,
        ],
        [
            'name' => __('Dashboard'),
            'icon' => 'fa-solid fa-wrench',
            'route' => route('admin.dashboard'),
            'active' => request()->routeIs('admin.dashboard'),
        ],
        [
            'name' => __('Users'),
            'icon' => 'fa-solid fa-user-group',
            'route' => route('admin.users.index'),
            'active' => request()->routeIs('admin.users.*'),
        ],
        [
            'name' => __('Channels'),
            'icon' => 'fa-solid fa-tv',
            'route' => route('admin.channels.index'),
            'active' => request()->routeIs('admin.channels.*'),
        ],
        [
            'name' => __('Stages'),
            'icon' => 'fa-solid fa-bars-staggered',
            'route' => route('admin.stages.index'),
            'active' => request()->routeIs('admin.stages.*'),
        ],
        [
            'name' => __('Devices'),
            'icon' => 'fa-solid fa-hard-drive',
            'route' => route('admin.devices.index'),
            'active' => request()->routeIs('admin.devices.*') || request()->routeIs('admin.rack-layout.*'),
        ],
        [
            'name' => __('Radios'),
            'icon' => 'fa-solid fa-radio',
            'route' => route('admin.radios.index'),
            'active' => request()->routeIs('admin.radios.*'),
        ],
        [
            'name' => __('modulators.modulators'),
            'icon' => 'fa-solid fa-right-left',
            'route' => route('admin.modulators.index'),
            'active' => request()->routeIs('admin.modulators.*') || request()->routeIs('modulators.*'),
        ],
        [
            'name' => __('Grafana'),
            'icon' => 'fa-solid fa-chart-pie',
            'route' => route('admin.grafana.index'),
            'active' => request()->routeIs('admin.grafana.*'),
        ],
        [
            'name' => __('SLA'),
            'icon' => 'fa-solid fa-traffic-light',
            'route' => route('admin.sla.index'),
            'active' => request()->routeIs('admin.sla.*'),
        ],
        [
            'name' => __('MySQL'),
            'icon' => 'fa-solid fa-database',
            'route' => 'http://172.16.126.166/phpmyadmin/index.php?route=/database/structure&db=scott_database',
            'external' => true,
            'active' => false,
        ],
    ];

    $currentUser = Auth::user();
    $allowedSlaUserIds = [1, 2, 3, 5, 7, 8];
    $canAccessSla = $currentUser && in_array((int) $currentUser->id, $allowedSlaUserIds, true);

    if (! $canAccessSla) {
        $links = array_values(array_filter($links, function ($l) {
            return !isset($l['route']) || $l['route'] !== route('admin.sla.index');
        }));
    }

    $userArea = strtolower(trim((string) ($currentUser?->default_area ?? '')));

    if (!($currentUser && $currentUser->id === 1)) {
        if ($userArea === 'ott') {
            $links = array_values(array_filter($links, function ($l) {
                return !isset($l['icon']) || $l['icon'] !== 'fa-solid fa-radio';
            }));
        }

        if ($userArea === 'dth') {
            $links = array_values(array_filter($links, function ($l) {
                return !isset($l['icon']) || $l['icon'] !== 'fa-brands fa-apple';
            }));
        }
    }

    $canAccessModulators = $currentUser?->canAccessModulators() ?? false;

    if (! $canAccessModulators) {
        $links = array_values(array_filter($links, function ($l) {
            return !isset($l['route']) || $l['route'] !== route('admin.modulators.index');
        }));
    }
@endphp

<aside id="logo-sidebar" class="fixed top-0 left-0 z-40 w-52 h-[100dvh] pt-[60px] bg-white dark:bg-gray-900 border-r border-gray-100 dark:border-gray-800 transition-transform duration-300
        -translate-x-full sm:translate-x-0"
    :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }" aria-label="Sidebar"
    x-data="{ openDropdown: null }">
    @php
        $area = Auth::user()?->area;
        $activeBg = $area === 'OTT' ? 'bg-primary-50 dark:bg-primary-900' : ($area === 'DTH' ? 'bg-secondary-50 dark:bg-secondary-900' : 'bg-primary-50 dark:bg-primary-900');
        $activeText = $area === 'OTT' ? 'text-primary-700 dark:text-primary-300' : ($area === 'DTH' ? 'text-secondary-700 dark:text-secondary-300' : 'text-primary-700 dark:text-primary-300');
        $activeFont = 'font-medium';
        $childActiveBg = $area === 'OTT' ? 'bg-primary-100 dark:bg-primary-800' : ($area === 'DTH' ? 'bg-secondary-100 dark:bg-secondary-800' : 'bg-primary-100 dark:bg-primary-800');
        $childActiveText = $area === 'OTT' ? 'text-primary-700 dark:text-primary-300' : ($area === 'DTH' ? 'text-secondary-700 dark:text-secondary-300' : 'text-primary-700 dark:text-primary-300');
        $hoverText = $area === 'OTT' ? 'hover:text-primary-600 dark:hover:text-primary-400' : ($area === 'DTH' ? 'hover:text-secondary-600 dark:hover:text-secondary-400' : 'hover:text-primary-600 dark:hover:text-primary-400');
    @endphp
    <div class="flex flex-col h-full px-4 pb-6 mt-2">
        <ul class="flex-1 space-y-1">
            @foreach ($links as $index => $link)
                @if (isset($link['children']))
                    <li>
                        <button @click="openDropdown = openDropdown === {{ $index }} ? null : {{ $index }}"
                            class="flex items-center justify-between gap-3 px-3 py-2 w-full rounded-md transition-colors duration-150 {{ $link['active'] ? "$activeBg $activeText $activeFont" : "text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 $hoverText" }}">
                            <div class="flex items-center gap-3">
                                <i class="{{ $link['icon'] }} w-4 h-4"></i>
                                <span class="truncate">{{ $link['name'] }}</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-xs transition-transform"
                                :class="{ 'rotate-180': openDropdown === {{ $index }} }"></i>
                        </button>
                        <ul x-show="openDropdown === {{ $index }}" x-transition class="pl-8 mt-1 space-y-1">
                            @foreach ($link['children'] as $child)
                                <li>
                                    <a href="{{ $child['route'] }}"
                                        class="flex items-center gap-2 justify-between px-3 py-1.5 rounded-md text-sm transition-colors duration-150 {{ $child['active']
                                        ? "$childActiveBg $childActiveText"
                                        : "text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 $hoverText" }} ">
                                        <span class="flex items-center gap-2">
                                            @if(isset($child['icon']))
                                                <i class="{{ $child['icon'] }} text-xs"></i>
                                            @endif
                                            <span>{{ $child['name'] }}</span>
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @else
                    <li>
                        @if(isset($link['external']) && $link['external'] && Auth::user() && Auth::user()->hasRole('master'))
                            <a href="{{ $link['route'] }}" target="_blank" rel="noopener"
                                class="group flex items-center justify-between gap-3 px-3 py-2 rounded-md transition-colors duration-150 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 {{ $hoverText }}">
                                <span class="flex items-center gap-3">
                                    <i class="{{ $link['icon'] }} text-base"></i>
                                    <span class="truncate">{{ $link['name'] }}</span>
                                </span>
                                @php
                                    $externalIconGroupHover = $area === 'OTT' ? 'group-hover:text-primary-500 group-hover:dark:text-primary-400' : ($area === 'DTH' ? 'group-hover:text-secondary-500 group-hover:dark:text-secondary-400' : 'group-hover:text-primary-500 group-hover:dark:text-primary-400');
                                @endphp
                                <i
                                    class="fa-solid fa-arrow-up-right-from-square text-xs text-gray-400 {{ $externalIconGroupHover }}"></i>
                            </a>
                        @elseif(!isset($link['external']))
                            <a href="{{ $link['route'] }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-md transition-colors duration-150 {{ $link['active']
                                ? "$activeBg $activeText $activeFont"
                                : "text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 $hoverText" }}"
                                aria-current="{{ $link['active'] ? 'page' : false }}">
                                <i class="{{ $link['icon'] }} text-base"></i>
                                <span class="truncate">{{ $link['name'] }}</span>
                            </a>
                        @endif
                    </li>
                @endif

                @if(isset($link['divider']) && $link['divider'])
                    <li>
                        <hr class="my-2 border-gray-200 dark:border-gray-700">
                    </li>
                @endif
            @endforeach
        </ul>

        <x-language-switch />

        <div class="mt-6 border-t border-gray-100 dark:border-gray-800 pt-4">
            <form method="POST" action="{{ route('logout') }}" x-data>
                @csrf
                <a href="{{ route('logout') }}" @click.prevent="$root.submit();"
                    class="flex justify-center items-center gap-2 text-xs text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    {{ __('Log Out') }}
                </a>
            </form>
        </div>
    </div>
</aside>
