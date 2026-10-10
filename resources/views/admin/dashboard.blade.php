@php
    use Illuminate\Support\Str;

    $userArea = $userArea ?? (Auth::user()?->area ?? null);
    $areaColor = match ($userArea) {
        'OTT' => 'primary',
        'DTH' => 'secondary',
        default => 'gray',
    };
    $chipBg = "bg-{$areaColor}-50 dark:bg-{$areaColor}-500/15";
    $chipText = "text-{$areaColor}-700 dark:text-{$areaColor}-300";
    $chipRing = "ring-{$areaColor}-200/60 dark:ring-{$areaColor}-500/30";
    $accentBg = "bg-{$areaColor}-600 hover:bg-{$areaColor}-700";
    $accentText = "text-{$areaColor}-600 dark:text-{$areaColor}-400";
    $accentBorder = "border-{$areaColor}-600 dark:border-{$areaColor}-400";
@endphp

<x-admin-layout :breadcrumbs="[
        [
            'name' => __('Admin panel'),
            'icon' => 'fa-solid fa-hammer',
        ]
    ]">

    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-{{ $areaColor }}-600 via-{{ $areaColor }}-700 to-{{ $areaColor }}-900 px-6 py-7 sm:px-8 sm:py-9 shadow-xl">
        <div class="absolute inset-0 opacity-30 pointer-events-none" aria-hidden="true">
            <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-{{ $areaColor }}-400 blur-3xl"></div>
            <div class="absolute -bottom-24 -left-12 w-64 h-64 rounded-full bg-{{ $areaColor }}-300 blur-3xl"></div>
        </div>

        <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4 min-w-0">
                <span class="inline-flex items-center justify-center w-20 h-20 rounded-xl bg-white/15 backdrop-blur ring-1 ring-white/20 shadow-lg">
                    <i class="fa-solid fa-cube text-white text-5xl"></i>
                </span>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="text-[10px] uppercase tracking-[0.18em] font-semibold text-{{ $areaColor }}-100/80">
                            {{ __('Admin panel') }}
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight truncate">
                        {{ config('app.name', 'Laravel') }} • {{ __('OTT •  DTH Communications System') }}
                    </h1>
                    <p class="text-sm text-{{ $areaColor }}-100/80 mt-0.5">
                        {{ __('Welcome back') }}, <span class="font-semibold text-white">{{ Auth::user()->name }}</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
                <span class="inline-flex items-center gap-1.5 px-3 h-8 rounded-full bg-white/10 backdrop-blur ring-1 ring-white/15 text-xs font-medium text-white">
                    <i class="fa-regular fa-calendar text-[11px]"></i>
                    {{ ucfirst(now()->translatedFormat('l, d M Y')) }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-6">
        <div class="rounded-2xl bg-white dark:bg-gray-800/60 border border-gray-200/70 dark:border-gray-700/60 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
                <div>
                    <h3 class="flex items-center text-[13px] font-semibold tracking-tight text-gray-900 dark:text-white">
                        <i class="fa-solid fa-arrow-right-to-bracket mr-2 text-gray-500 dark:text-gray-400 text-xs"></i>
                        {{ __('Quick actions') }}
                    </h3>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 tracking-tight">
                        {{ __('Common shortcuts') }}
                    </p>
                </div>
            </div>

            <div class="p-5 grid grid-cols-3 sm:grid-cols-4 gap-x-2 gap-y-4">
                @php
                    $currentUser = Auth::user();
                    $isMaster = $currentUser && ($currentUser->id === 1 || $currentUser->hasRole('master'));
                    $canSwitching = false;
                    $canDataCenters = false;
                    if ($currentUser) {
                        try {
                            \Spatie\Permission\Models\Permission::firstOrCreate([
                                'name' => 'switches.admin',
                                'guard_name' => 'web',
                            ]);
                            \Spatie\Permission\Models\Permission::firstOrCreate([
                                'name' => 'data-centers.admin',
                                'guard_name' => 'web',
                            ]);

                            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

                            $currentUser->unsetRelation('permissions');
                            $currentUser->unsetRelation('roles');
                            $currentUser->load('permissions');
                            $currentUser->load('roles');

                            $canSwitching = $currentUser->hasPermissionTo('switches.admin');
                            $canDataCenters = $currentUser->hasPermissionTo('data-centers.admin');
                        } catch (\Throwable $e) {
                            $canSwitching = false;
                            $canDataCenters = false;
                        }
                    }

                    $actions = [
                        [
                            'label' => __('Profile'),
                            'sub' => __('Personal'),
                            'icon' => 'fa-user',
                            'route' => route('profile.show'),
                            'gradient' => 'from-sky-400 via-sky-500 to-blue-600',
                            'shadow' => 'shadow-sky-500/40',
                            'icon_color' => 'text-white',
                            'visible' => true,
                        ],
                        [
                            'label' => __('Users'),
                            'sub' => __('Manage'),
                            'icon' => 'fa-user-group',
                            'route' => route('admin.users.index'),
                            'gradient' => 'from-violet-400 via-violet-500 to-purple-600',
                            'shadow' => 'shadow-violet-500/40',
                            'icon_color' => 'text-white',
                            'visible' => $isMaster,
                        ],
                        [
                            'label' => __('Switching'),
                            'sub' => __('Conmutaciones'),
                            'icon' => 'fa-tower-broadcast',
                            'route' => route('admin.modulators.index'),
                            'gradient' => 'from-indigo-400 via-indigo-500 to-blue-700',
                            'shadow' => 'shadow-indigo-500/40',
                            'icon_color' => 'text-white',
                            'visible' => $canSwitching,
                        ],
                        [
                            'label' => __('Data centers'),
                            'sub' => __('Locations'),
                            'icon' => 'fa-building',
                            'route' => route('admin.dashboard'),
                            'gradient' => 'from-amber-400 via-orange-500 to-rose-500',
                            'shadow' => 'shadow-orange-500/40',
                            'icon_color' => 'text-white',
                            'visible' => $isMaster || $canDataCenters,
                        ],
                        [
                            'label' => __('Database'),
                            'sub' => __('phpMyAdmin'),
                            'icon' => 'fa-database',
                            'route' => 'http://172.16.126.166/phpmyadmin/index.php?route=/database/structure&db=aims_database',
                            'gradient' => 'from-emerald-400 via-emerald-500 to-teal-600',
                            'shadow' => 'shadow-emerald-500/40',
                            'icon_color' => 'text-white',
                            'external' => true,
                            'visible' => $isMaster,
                        ],
                    ];
                @endphp
                @foreach (array_values(array_filter($actions, fn ($a) => $a['visible'] ?? true)) as $action)
                    <a href="{{ $action['route'] }}" @if ($action['external'] ?? false) target="_blank" rel="noopener" @endif
                        class="group flex flex-col items-center gap-1.5 transition active:scale-[0.96]"
                        title="{{ $action['label'] }}">
                        <span class="relative inline-flex items-center justify-center w-[88px] h-[88px] rounded-[22px] bg-gradient-to-br {{ $action['gradient'] }} {{ $action['icon_color'] }} shadow-lg {{ $action['shadow'] }} ring-1 ring-white/25 backdrop-blur-sm group-hover:scale-[1.06] group-hover:-translate-y-0.5 group-hover:shadow-xl transition-all duration-300 ease-out">
                            <span class="absolute inset-0 rounded-[22px] bg-gradient-to-t from-black/10 to-white/25 pointer-events-none"></span>
                            <span class="absolute top-[10px] left-[14px] right-[14px] h-1/2 rounded-t-[18px] bg-white/15 blur-md pointer-events-none"></span>
                            <i class="fa-solid {{ $action['icon'] }} text-[28px] drop-shadow-sm transition-transform duration-300 group-hover:scale-110"></i>
                        </span>
                        <span class="text-[11px] font-medium text-center text-gray-800 dark:text-gray-100 group-hover:text-gray-900 dark:group-hover:text-white leading-tight tracking-tight">
                            {{ $action['label'] }}
                        </span>
                        <span class="text-[10px] text-center text-gray-400 dark:text-gray-500 leading-tight tracking-tight">
                            {{ $action['sub'] }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl bg-white dark:bg-gray-800/60 border border-gray-200/70 dark:border-gray-700/60 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60 flex items-start justify-between gap-3">
                <div>
                    <h3 class="flex items-center text-sm font-semibold text-gray-900 dark:text-white">
                        <i class="fa-solid fa-circle-info mr-2 text-gray-500 dark:text-gray-400"></i>
                        {{ __('System info') }}
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ __('Application stack') }}
                    </p>
                </div>
                <span class="inline-flex items-center gap-1 px-2 h-6 rounded-full {{ $chipBg }} {{ $chipText }} ring-1 {{ $chipRing }} text-[11px] font-semibold shrink-0">
                    v{{ config('app.version', '3.0') }}
                </span>
            </div>
            <dl class="divide-y divide-gray-100 dark:divide-gray-700/60">
                @php
                    $infoItems = [
                        ['label' => __('PHP'), 'value' => $systemInfo['php'], 'icon' => 'fa-php', 'icon-type' => 'brands'],
                        ['label' => __('Laravel'), 'value' => $systemInfo['laravel'], 'icon' => 'fa-laravel', 'icon-type' => 'brands'],
                        ['label' => __('Tailwind'), 'value' => 'Flowbite 3.1.2', 'icon' => 'fa-tailwind-css', 'icon-type' => 'brands'],
                        ['label' => __('phpMyAdmin'), 'value' => '5.2.3', 'icon' => 'fa-server', 'icon-type' => 'solid'],
                        ['label' => __('Database'), 'value' => 'Maria DB 10.4.32', 'icon' => 'fa-database', 'icon-type' => 'solid'],
                        ['label' => __('Locale'), 'value' => strtoupper($systemInfo['locale']), 'icon' => 'fa-language', 'icon-type' => 'solid'],
                    ];
                @endphp
                @foreach ($infoItems as $item)
                    <div class="flex items-center justify-between px-5 py-2.5">
                        <dt class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <i class="fa-{{ $item['icon-type'] }} {{ $item['icon'] }} text-xs w-4 text-center text-gray-400 dark:text-gray-500"></i>
                            <span class="uppercase tracking-wider font-medium">{{ $item['label'] }}</span>
                        </dt>
                        <dd class="text-[10px] font-semibold text-gray-900 dark:text-white flex items-center gap-3">
                            <span class="px-2 py-1 rounded-xl bg-gray-100 dark:bg-gray-700/60 text-gray-800 dark:text-gray-100 shadow-sm">{{ $item['value'] }}</span>
                        </dd>
                    </div>
                @endforeach
            </dl>
            <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700/60 bg-white dark:bg-gray-800/60 flex items-center justify-between text-[11px]">
                <span class="text-gray-500 dark:text-gray-400">{{ __('Last update') }}</span>
                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ __('October') }} 2026</span>
            </div>
        </div>
    </div>

    <div class="mt-6 rounded-2xl bg-white dark:bg-gray-800/60 border border-gray-200/70 dark:border-gray-700/60 shadow-sm p-6 sm:p-7">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-8">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700/60">
                    <div class="flex items-center gap-2.5">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-md {{ $chipBg }} {{ $chipText }}">
                            <i class="fa-solid fa-people-group text-[11px]"></i>
                        </span>
                        <div>
                            <p class="text-[11px] font-semibold tracking-[0.14em] text-gray-500 dark:text-gray-400 uppercase leading-none">{{ __('Development Team') }}</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white leading-tight">{{ __('Engineering and Technology Department') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 text-[11px] text-gray-500 dark:text-gray-400">
                            <i class="fa-solid fa-user-group text-[10px]"></i>
                            5 {{ __('members') }}
                        </span>
                    </div>
                </div>

                <ul class="mt-2 divide-y divide-gray-100 dark:divide-gray-700/60">
                    <li class="group flex items-center gap-4 py-3 cursor-default">
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-700/60 {{ $chipText }} shrink-0">
                            <i class="fa-solid fa-user text-[12px]"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[14.5px] font-semibold text-gray-900 dark:text-white leading-tight truncate">Edgar Leonel Acevedo Cuevas</p>
                        </div>
                        <span class="hidden sm:inline-flex items-center gap-1 text-[11px] text-gray-400 dark:text-gray-500 opacity-0 group-hover:opacity-100 transition">
                            <span class="w-1 h-1 rounded-full bg-emerald-500"></span>
                            {{ __('Active') }}
                        </span>
                    </li>
                    <li class="group flex items-center gap-4 py-3 cursor-default">
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-700/60 {{ $chipText }} shrink-0">
                            <i class="fa-solid fa-user text-[12px]"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[14.5px] font-semibold text-gray-900 dark:text-white leading-tight truncate">Erasmo Rodriguez Cardiel</p>
                        </div>
                        <span class="hidden sm:inline-flex items-center gap-1 text-[11px] text-gray-400 dark:text-gray-500 opacity-0 group-hover:opacity-100 transition">
                            <span class="w-1 h-1 rounded-full bg-emerald-500"></span>
                            {{ __('Active') }}
                        </span>
                    </li>
                    <li class="group flex items-center gap-4 py-3 cursor-default">
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-700/60 {{ $chipText }} shrink-0">
                            <i class="fa-solid fa-user text-[12px]"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[14.5px] font-semibold text-gray-900 dark:text-white leading-tight truncate">Pamela Marlen Escobedo Ramirez</p>
                        </div>
                        <span class="hidden sm:inline-flex items-center gap-1 text-[11px] text-gray-400 dark:text-gray-500 opacity-0 group-hover:opacity-100 transition">
                            <span class="w-1 h-1 rounded-full bg-emerald-500"></span>
                            {{ __('Active') }}
                        </span>
                    </li>
                    <li class="group flex items-center gap-4 py-3 cursor-default">
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-700/60 {{ $chipText }} shrink-0">
                            <i class="fa-solid fa-user text-[12px]"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[14.5px] font-semibold text-gray-900 dark:text-white leading-tight truncate">Saul Rodriguez de la Rosa</p>
                        </div>
                        <span class="hidden sm:inline-flex items-center gap-1 text-[11px] text-gray-400 dark:text-gray-500 opacity-0 group-hover:opacity-100 transition">
                            <span class="w-1 h-1 rounded-full bg-emerald-500"></span>
                            {{ __('Active') }}
                        </span>
                    </li>
                    <li class="group flex items-center gap-4 py-3 cursor-default">
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-700/60 {{ $chipText }} shrink-0">
                            <i class="fa-solid fa-user text-[12px]"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[14.5px] font-semibold text-gray-900 dark:text-white leading-tight truncate">Juan Pablo Ortega Marquez</p>
                        </div>
                        <span class="hidden sm:inline-flex items-center gap-1 text-[11px] text-gray-400 dark:text-gray-500 opacity-0 group-hover:opacity-100 transition">
                            <span class="w-1 h-1 rounded-full bg-emerald-500"></span>
                            {{ __('Active') }}
                        </span>
                    </li>
                </ul>
            </div>

            <div class="lg:col-span-4 lg:border-l lg:border-gray-100 lg:dark:border-gray-700/60 lg:pl-6">
                <div class="flex items-center gap-2.5 pb-4 border-b border-gray-100 dark:border-gray-700/60">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-md {{ $chipBg }} {{ $chipText }}">
                        <i class="fa-solid fa-envelope text-[12px]"></i>
                    </span>
                    <div>
                        <p class="text-[11px] font-semibold tracking-[0.14em] text-gray-500 dark:text-gray-400 uppercase leading-none">{{ __('Support Contact') }}</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white leading-tight">{{ __('Engineering Support') }}</p>
                    </div>
                </div>

                <p class="mt-4 text-[13px] text-gray-600 dark:text-gray-300 leading-relaxed">
                    {{ __('Reach our engineering team for technical inquiries, system reports and platform assistance.') }}
                </p>

                <a href="mailto:atencionott@stargroup.com.mx"
                    class="mt-5 group flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:{{ $accentBorder }} hover:shadow-sm transition-all">
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg {{ $chipBg }} {{ $chipText }} group-hover:{{ $accentBg }} group-hover:text-white transition shrink-0">
                        <i class="fa-solid fa-envelope text-xs"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-none">Email</p>
                        <p class="mt-1 text-[13.5px] font-semibold text-gray-900 dark:text-white truncate">atencionott@stargroup.com.mx</p>
                    </div>
                    <i class="fa-solid fa-arrow-up-right-from-square text-[11px] text-gray-400 group-hover:{{ $accentText }} group-hover:-translate-y-0.5 group-hover:translate-x-0.5 transition-all"></i>
                </a>

                <div class="mt-3 flex items-center gap-2 text-[11.5px] text-gray-500 dark:text-gray-400">
                    <span class="inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    {{ __('Response within 24 hours') }}
                </div>
            </div>
        </div>
    </div>

</x-admin-layout>
