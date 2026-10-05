@php
    use Illuminate\Support\Str;
@endphp

<x-admin-layout :breadcrumbs="[
        [
            'name' => __('Admin panel'),
            'icon' => 'fa-solid fa-hammer',
        ]
    ]">

    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900 px-6 py-7 sm:px-8 sm:py-9 shadow-xl shadow-primary-900/10">
        <div class="absolute inset-0 opacity-30 pointer-events-none" aria-hidden="true">
            <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-primary-400 blur-3xl"></div>
            <div class="absolute -bottom-24 -left-12 w-64 h-64 rounded-full bg-primary-300 blur-3xl"></div>
        </div>

        <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4 min-w-0">
                <span class="inline-flex items-center justify-center w-20 h-20 rounded-xl bg-white/15 backdrop-blur ring-1 ring-white/20 shadow-lg">
                    <i class="fa-solid fa-cube text-white text-5xl"></i>
                </span>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="text-[10px] uppercase tracking-[0.18em] font-semibold text-primary-100/80">
                            {{ __('Admin panel') }}
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight truncate">
                        {{ config('app.name', 'Laravel') }} • {{ __('OTT •  DTH Communications System') }}
                    </h1>
                    <p class="text-sm text-primary-100/80 mt-0.5">
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
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
                <h3 class="flex items-center text-sm font-semibold text-gray-900 dark:text-white">
                    <i class="fa-solid fa-circle-info mr-2 text-gray-500 dark:text-gray-400"></i>
                    {{ __('System info') }}
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ __('Application stack') }}
                </p>
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
                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ __('July') }} 2026</span>
            </div>
        </div>
    </div>

    {{-- <div class="mt-6 rounded-2xl bg-white dark:bg-gray-800/60 border border-gray-200/70 dark:border-gray-700/60 shadow-sm overflow-hidden p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-gray-800 shadow-sm border border-gray-200/70 dark:border-gray-700/60">
                    <i class="fa-solid fa-code text-gray-500 dark:text-gray-400 text-sm"></i>
                </span>
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ __('Ing. Edgar Leonel Acevedo Cuevas') }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('Developer') }} ·
                        <a href="mailto:atencionott@stargroup.com.mx" class="hover:text-primary-600 dark:hover:text-primary-400">
                            ecuevas@stargroup.com.mx
                        </a>
                    </p>
                </div>
            </div>
            <span class="px-3 h-7 inline-flex items-center rounded-full bg-primary-50 dark:bg-primary-500/20 text-primary-700 dark:text-primary-300 text-xs font-semibold">
                {{ __('Version') }} {{ config('app.version', '0.1') }}
            </span>
        </div>
    </div> --}}

</x-admin-layout>
