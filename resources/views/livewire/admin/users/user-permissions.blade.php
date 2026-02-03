<div class="w-full mx-auto bg-white dark:bg-gray-800 shadow-xl rounded-lg p-4 sm:p-6">
    @php
        $area = Auth::user()?->area;
        $primaryBtn = $area === 'OTT' ? 'bg-primary-600 hover:bg-primary-700' : ($area === 'DTH' ? 'bg-secondary-600 hover:bg-secondary-700' : 'bg-primary-600 hover:bg-primary-700');
        $primaryBtnText = 'text-white';
    @endphp
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 pb-6">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4 w-full sm:w-auto">
            <img class="h-16 w-16 sm:h-20 sm:w-20 rounded-full object-cover shadow-md"
                src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" />
                <div class="text-center sm:text-left space-y-3 sm:space-y-1 w-full">
                @php
                    $userArea = $user->area ?? null;
                    $userAreaClasses = $userArea === 'DTH'
                        ? 'bg-secondary-200 text-secondary-800 dark:bg-secondary-700 dark:text-secondary-100'
                        : ($userArea === 'OTT' ? 'bg-primary-200 text-primary-800 dark:bg-primary-700 dark:text-primary-100' : 'bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-100');

                    $smallBadgeBase = 'inline-flex items-center justify-center w-7 h-7 rounded-full focus:outline-none';

                    $statusActiveClasses = 'bg-green-200 text-green-800 dark:bg-green-900 dark:text-green-100';
                    $statusInactiveClasses = 'bg-red-200 text-red-800 dark:bg-red-700 dark:text-red-300';

                    $emailVerifiedClasses = $statusActiveClasses;
                    $emailNotVerifiedClasses = 'bg-yellow-200 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100';

                    $canSwitchTrueClasses = 'bg-blue-200 text-blue-800 dark:bg-blue-900 dark:text-blue-100';
                    $canSwitchFalseClasses = 'bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-100';
                                    @endphp
                                    @php
                    $roleName = null;
                    if (method_exists($user, 'getRoleNames')) {
                        $roleName = $user->getRoleNames()->first() ?? null;
                    } elseif (isset($user->roles) && $user->roles->first()) {
                        $roleName = $user->roles->first()->name ?? null;
                    }
                    $role = $user->roles->first()?->name;
                    if ($user->id === 1) {
                        $badge = [
                            'label' => 'Developer',
                            'icon' => 'fa-code',
                            'bg' => 'bg-red-200',
                            'text' => 'text-red-800',
                            'dark_bg' => 'dark:bg-red-800',
                            'dark_text' => 'dark:text-red-100',
                        ];
                    } elseif ($role === 'master') {
                        $badge = [
                            'label' => 'Master',
                            'icon' => 'fa-crown',
                            'bg' => 'bg-yellow-200',
                            'text' => 'text-yellow-800',
                            'dark_bg' => 'dark:bg-yellow-900',
                            'dark_text' => 'dark:text-yellow-100',
                        ];
                    } elseif ($role === 'admin') {
                        $badge = [
                            'label' => 'Admin',
                            'icon' => 'fa-gear',
                            'bg' => 'bg-blue-200',
                            'text' => 'text-blue-800',
                            'dark_bg' => 'dark:bg-blue-900',
                            'dark_text' => 'dark:text-blue-100',
                        ];
                    } elseif ($role === 'user') {
                        $badge = [
                            'label' => 'User',
                            'icon' => 'fa-user',
                            'bg' => 'bg-gray-200',
                            'text' => 'text-gray-800',
                            'dark_bg' => 'dark:bg-gray-700',
                            'dark_text' => 'dark:text-gray-100',
                        ];
                    } else {
                        $badge = null;
                    }
                @endphp
                <h1
                    class="flex flex-col md:flex-row flex-wrap items-center text-lg font-bold text-gray-900 dark:text-white gap-4">
                    <span class="flex items-center gap-2">
                        <span>{{ $user->name }}</span>
                    </span>
                </h1>
                <div class="text-sm text-gray-600 dark:text-gray-300 flex flex-col space-y-2 sm:space-y-1">
                    <span class="flex items-center justify-center sm:justify-start gap-2 mb-0.5 pt-0.5">
                        <i class="fa-solid fa-envelope"></i>
                        <span class="break-words">{{ $user->email }}</span>
                    </span>
                </div>
                @php
                    $auth = auth()->user();
                    $isAuthMaster = $auth->hasRole('master');
                    $isAuthAdmin = $auth->hasRole('admin');
                    $isAuthUser = $auth->hasRole('user');

                    $isTargetMaster = $user->hasRole('master');
                    $isTargetAdmin = $user->hasRole('admin');
                    $isTargetUser = $user->hasRole('user');

                    $isSelf = $auth->id === $user->id;
                    $isFirstMaster = $user->id === 1;

                    $canSave = ($isFirstMaster && $isSelf)
                        || $isAuthMaster
                        || ($isAuthAdmin && $isTargetUser && !$isSelf)
                        || ($isAuthAdmin && $isSelf)
                        || ($isAuthUser && $isSelf);
                @endphp
                <div class="w-full flex justify-center sm:justify-start mt-5 sm:mt-3">
                    @php
                        $prefBtnText = $area === 'OTT' ? 'text-primary-600 hover:text-primary-700' : ($area === 'DTH' ? 'text-secondary-600 hover:text-secondary-700' : 'text-primary-600 hover:text-primary-700');
                    @endphp
                    <div x-data="{ open: false, canSave: {{ $canSave ? 'true' : 'false' }} }"
                        @preferences-saved.window="open = false" class="relative">
                        <button @click="canSave && (open = !open)" :class="{
                                'opacity-50': !canSave,
                                '{{ $area === 'OTT' ? 'hover:text-primary-700' : ($area === 'DTH' ? 'hover:text-secondary-700' : 'hover:text-primary-700') }}': canSave
                            }" :disabled="!canSave" type="button"
                            class="mt-1.5 flex items-center justify-center sm:justify-start text-xs transition font-medium {{ $area === 'OTT' ? 'text-primary-600' : ($area === 'DTH' ? 'text-secondary-600' : 'text-primary-600') }}">
                            <i class="fa-solid fa-reply-all mr-2"></i>
                            <span>{{ __('Report mail preferences') }}</span>
                            <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"
                                class="fa-solid ml-2 transition-transform duration-300"></i>
                        </button>
                        <div x-show="open" x-transition.origin.top.left @click.outside="open = false"
                            class="mt-2 bg-white dark:bg-gray-700 shadow-lg rounded-lg p-4 space-y-4 border dark:border-gray-600 absolute z-10 w-full sm:w-72 left-0 sm:left-auto sm:right-0">
                            <form wire:submit.prevent="saveReportPreferences" class="space-y-3">
                                @php
                                    $authForPrefs = auth()->user();
                                    $reportMailsList = [
                                        'report_created' => __('Report Created'),
                                        'report_updated' => __('Report Updated'),
                                        'report_resolved' => __('Report Resolved'),
                                    ];

                                    $showFunctionsPref = $authForPrefs && (
                                        $authForPrefs->id === 1 ||
                                        strtoupper(trim($authForPrefs->area ?? '')) === 'OTT'
                                    );

                                    if ($showFunctionsPref) {
                                        $reportMailsList['report_functions_created'] = __('Report Functions Created');
                                    }
                                @endphp
                                @foreach ($reportMailsList as $key => $label)
                                    <x-label class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                                        <x-checkbox type="checkbox" wire:model.defer="reportMails.{{ $key }}" />
                                        <span class="truncate leading-tight">{{ $label }}</span>
                                    </x-label>
                                @endforeach
                                <div class="flex justify-end pt-2">
                                    <button type="submit" {{ !$canSave ? 'disabled' : '' }}
                                        class="text-xs px-4 py-2 {{ $primaryBtn }} {{ $primaryBtnText }} rounded-md transition font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
                                        <i class="fa-solid fa-floppy-disk mr-1"></i>
                                        {{ __('Save') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-sm text-center sm:text-right w-full sm:w-auto mt-1">
            <div class="text-gray-500 dark:text-gray-300 flex items-center justify-center sm:justify-end gap-2">
                <i class="fa-solid fa-calendar-day"></i>
                <span>{{ __('Joined on') }} {{ $user->created_at->format('d M Y') }}</span>
            </div>

            <div class="mt-7 flex flex-wrap gap-3 justify-center sm:justify-end">
                @php $auth = auth()->user();
                    $isSelfFirstAdmin = $auth->id === 1 && $user->id === 1 && $auth->hasRole('master');
                @endphp
                @php
                    $resetBtnBg = $area === 'OTT' ? 'bg-gray-50 hover:bg-gray-100 dark:bg-gray-900' : ($area === 'DTH' ? 'bg-gray-50 hover:bg-gray-100 dark:bg-gray-900' : 'bg-gray-50 hover:bg-gray-100 dark:bg-gray-900');
                    $resetBtnText = $area === 'OTT' ? 'text-gray-700 dark:text-gray-200' : ($area === 'DTH' ? 'text-gray-700 dark:text-gray-200' : 'text-gray-700 dark:text-gray-200');
                @endphp
                @if($badge)
                    <div class="relative" x-data="{ tip: false }">
                        <span @mouseenter="tip = true" @mouseleave="tip = false"
                            class="{{ $smallBadgeBase }} {{ $badge['bg'] }} {{ $badge['text'] }} {{ $badge['dark_bg'] }} {{ $badge['dark_text'] }}">
                            <i class="fa-solid {{ $badge['icon'] }} text-xs" aria-hidden="true"></i>
                        </span>
                        <div x-show="tip" x-cloak x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute z-50 left-1/2 transform -translate-x-1/2 mt-2 w-max px-2 py-1 rounded-md text-xs text-white bg-gray-800 dark:bg-gray-100 dark:text-gray-900">
                            {{ $badge['label'] }}
                            @if (in_array($user->id, [2, 5, 7, 8]))
                                ({{ __('Engineering') }})
                            @endif
                        </div>
                    </div>
                @endif
                <div class="relative" x-data="{ tip: false }">
                    <button @if($isSelfFirstAdmin) disabled @else wire:click="toggleStatus" @endif
                        @mouseenter="tip = true" @mouseleave="tip = false" type="button"
                        aria-pressed="{{ $user->status ? 'true' : 'false' }}"
                        class="{{ $smallBadgeBase }} {{ $user->status ? $statusActiveClasses : $statusInactiveClasses }}">
                        <i class="fa-solid {{ $user->status ? 'fa-circle-check' : 'fa-circle-xmark' }} text-xs"></i>
                    </button>
                    <div x-show="tip" x-cloak x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute z-50 left-1/2 transform -translate-x-1/2 mt-2 w-max px-2 py-1 rounded-md text-xs text-white bg-gray-800 dark:bg-gray-100 dark:text-gray-900">
                        {{ $user->status ? __('Active') : __('Inactive') }}
                    </div>
                </div>
                @if($user->hasVerifiedEmail())
                    <div class="relative" x-data="{ tip: false }">
                        <span @mouseenter="tip = true" @mouseleave="tip = false"
                            class="{{ $smallBadgeBase }} {{ $emailVerifiedClasses }}">
                            <i class="fa-solid fa-envelope text-xs" aria-hidden="true"></i>
                        </span>
                        <div x-show="tip" x-cloak x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute z-50 left-1/2 transform -translate-x-1/2 mt-2 w-max px-2 py-1 rounded-md text-xs text-white bg-gray-800 dark:bg-gray-100 dark:text-gray-900">
                            {{ __('Email verified') }}
                        </div>
                    </div>
                @else
                    <div class="relative" x-data="{ tip: false }">
                        <span @mouseenter="tip = true" @mouseleave="tip = false"
                            class="{{ $smallBadgeBase }} {{ $emailNotVerifiedClasses }}">
                            <i class="fa-solid fa-circle-xmark text-xs" aria-hidden="true"></i>
                        </span>
                        <div x-show="tip" x-cloak x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute z-50 left-1/2 transform -translate-x-1/2 mt-2 w-max px-2 py-1 rounded-md text-xs text-white bg-gray-800 dark:bg-gray-100 dark:text-gray-900">
                            {{ __('Email not verified') }}
                        </div>
                    </div>
                @endif
                @if ($auth && $auth->id === 1 && $auth->id !== $user->id)
                    <div class="relative" x-data="{ tip: false }">
                        <button @mouseenter="tip = true" @mouseleave="tip = false" wire:click="sendResetPasswordEmail" type="button"
                                class="{{ $smallBadgeBase }} {{ $canSwitchFalseClasses }}">
                            <i class="fa-solid fa-key text-xs" aria-hidden="true"></i>
                        </button>
                        <div x-show="tip" x-cloak x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute z-50 left-1/2 transform -translate-x-1/2 mt-2 w-max px-2 py-1 rounded-md text-xs text-white bg-gray-800 dark:bg-gray-100 dark:text-gray-900">
                            {{ __('Reset password') }}
                        </div>
                    </div>
                @endif
                <div class="relative" x-data="{ tip: false }">
                    @php $canSwitch = $user->can_switch_area ?? false; @endphp
                    <span @mouseenter="tip = true" @mouseleave="tip = false"
                        @if($canEditPermissions) wire:click="toggleCanSwitch" @endif
                        role="button"
                        aria-pressed="{{ $canSwitch ? 'true' : 'false' }}"
                        class="{{ $smallBadgeBase }} {{ $canSwitch ? $canSwitchTrueClasses : $canSwitchFalseClasses }} {{ $canEditPermissions ? 'cursor-pointer' : '' }}">
                        <i class="fa-solid {{ $canSwitch ? 'fa-repeat' : 'fa-lock' }} text-xs" aria-hidden="true"></i>
                    </span>
                    <div x-show="tip" x-cloak x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute z-50 left-1/2 transform -translate-x-1/2 mt-2 w-max px-2 py-1 rounded-md text-xs text-white bg-gray-800 dark:bg-gray-100 dark:text-gray-900">
                        {{ $canSwitch ? __('Can switch area') : __('Cannot switch area') }}
                    </div>
                </div>
                @if($userArea)
                    <div class="relative" x-data="{ tip: false }">
                        @php
                            $isDefaultArea = isset($user->default_area) && $user->default_area === $userArea;
                            $defaultDotClasses = $userArea === 'DTH'
                                ? 'bg-secondary-600 dark:bg-secondary-300'
                                : 'bg-primary-600 dark:bg-primary-300';
                        @endphp
                        @php $authUser = auth()->user(); @endphp
                        <span @mouseenter="tip = true" @mouseleave="tip = false"
                            @if($authUser && $authUser->id === 1) wire:click="toggleArea" @endif
                            class="{{ $smallBadgeBase }} {{ $userAreaClasses }} {{ ($authUser && $authUser->id === 1) ? 'cursor-pointer' : '' }}">
                            <i class="fa-solid {{ $userArea === 'DTH' ? 'fa-satellite-dish' : 'fa-cube' }} text-xs"></i>
                        </span>
                        @if($isDefaultArea)
                            <span class="absolute top-0 right-0 transform translate-x-1/2 -translate-y-1/2 w-2 h-2 rounded-full {{ $defaultDotClasses }} ring-1 ring-white dark:ring-gray-800" aria-hidden="true"></span>
                        @endif
                        <div x-show="tip" x-cloak x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute z-50 left-1/2 transform -translate-x-1/2 mt-2 w-max px-2 py-1 rounded-md text-xs text-white bg-gray-800 dark:bg-gray-100 dark:text-gray-900">
                            {{ $userArea }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="space-y-2">
        <label for="role" class="text-sm font-medium text-gray-700 dark:text-gray-200">
            <i class="fa-solid fa-shield-halved mr-2"></i>{{ __('Role') }}
        </label>
        <select id="role" wire:model="role" x-data x-on:change="$wire.onRoleChanged($event.target.value)"
            class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white {{ Auth::user()->area === 'DTH' ? 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500 dark:focus:border-secondary-500' : 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500' }}"
            @disabled(!$canEditRoles)>
            @foreach ($allRoles as $r)
                <option value="{{ $r->name }}">{{ ucfirst($r->name) }}</option>
            @endforeach
        </select>
    </div>

    <div class="mt-6 space-y-2">
        <h2 class="text-sm font-medium text-gray-800 dark:text-white flex items-center">
            <i class="fa-solid fa-key mr-2"></i>
            {{ __('Permissions') }}
        </h2>
        <div class="flex flex-col md:flex-row md:flex-nowrap gap-4">
            @php
                $grouped = collect($allPermissions)->groupBy(fn($perm) => explode('.', $perm->name)[0]);
                $icons = [
                    'channels' => 'fa-tv',
                    'stages' => 'fa-bars-staggered',
                    'devices' => 'fa-hard-drive',
                    'radios' => 'fa-radio',
                    'grafana' => 'fa-chart-pie',
                    'roles' => 'fa-shield-halved',
                    'permissions' => 'fa-key',
                ];

                $auth = Auth::user();
                $authIsDthOrSuper = $auth && ($auth->id === 1 || ($auth->area ?? '') === 'DTH');
            @endphp
            @php
                $preferredOrder = ['channels', 'stages', 'devices', 'radios', 'grafana'];
                $allGroups = $grouped->keys()->toArray();

                $ordered = [];
                foreach ($preferredOrder as $g) {
                    if (in_array($g, $allGroups)) {
                        $ordered[] = $g;
                        $allGroups = array_values(array_diff($allGroups, [$g]));
                    }
                }

                foreach ($allGroups as $g) {
                    if (! in_array($g, ['roles', 'permissions'])) {
                        $ordered[] = $g;
                    }
                }
            @endphp

            @foreach ($ordered as $group)
                @php $perms = $grouped[$group]; @endphp
                @if($group === 'radios' && ! $authIsDthOrSuper)
                    @continue
                @endif
                <div class="flex-1 min-w-0 p-4 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">
                    <h3 class="text-sm font-bold text-gray-700 dark:text-white uppercase mb-3 flex items-center gap-2 {{ !$canEditPermissions ? 'opacity-50' : '' }}">
                        <i class="fa-solid {{ $icons[$group] ?? 'fa-lock' }}"></i>{{ __(ucfirst($group)) }}
                    </h3>
                    <div class="space-y-2">
                        @php
                            $sortedPerms = collect($perms)->sortBy(function ($p) use ($group) {
                                $parts = explode('.', $p->name, 2);
                                $action = $parts[1] ?? '';
                                return $action === 'view' ? 0 : 1;
                            })->values();
                        @endphp
                        @foreach ($sortedPerms as $permission)
                            @php
                                $primaryCheckbox = $area === 'OTT' ? 'text-primary-600 focus:ring-primary-500' : ($area === 'DTH' ? 'text-secondary-600 focus:ring-secondary-500' : 'text-primary-600 focus:ring-primary-500');
                                $isRadioPerm = substr($permission->name, 0, 6) === 'radios.';
                                $disabledForArea = $isRadioPerm && ! $authIsDthOrSuper;
                            @endphp
                            <label class="flex items-center gap-2 {{ !$canEditPermissions ? 'opacity-50' : '' }}">
                                <input type="checkbox" value="{{ $permission->name }}" wire:model="permissions"
                                    class="w-4 h-4 {{ $primaryCheckbox }} bg-white border-gray-300 rounded focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                    @disabled(!$canEditPermissions || in_array($permission->name, $forbiddenPermissions) || $disabledForArea)>
                                <span class="text-sm text-gray-800 dark:text-gray-200">
                                    {{ __(ucfirst(str_replace($group . '.', '', $permission->name))) }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
            @if(isset($grouped['roles']) || isset($grouped['permissions']))
                <div class="flex-1 min-w-0 p-4 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">
                    <h3
                        class="text-sm font-bold text-gray-700 dark:text-white uppercase mb-3 flex items-center gap-2 {{ !$canEditPermissions ? 'opacity-50' : '' }}">
                        <i class="fa-solid fa-shield-halved"></i>{{ __('Roles & Permissions') }}
                    </h3>
                    <div class="space-y-2">
                        @if(isset($grouped['roles']))
                            @foreach ($grouped['roles'] as $permission)
                                @php
                                    $primaryCheckbox = $area === 'OTT' ? 'text-primary-600 focus:ring-primary-500' : ($area === 'DTH' ? 'text-secondary-600 focus:ring-secondary-500' : 'text-primary-600 focus:ring-primary-500');
                                @endphp
                                <label class="flex items-center gap-2 {{ !$canEditPermissions ? 'opacity-50' : '' }}">
                                    <input type="checkbox" value="{{ $permission->name }}" wire:model="permissions"
                                        class="w-4 h-4 {{ $primaryCheckbox }} bg-white border-gray-300 rounded focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                        @disabled(!$canEditPermissions || $role === 'master' || in_array($permission->name, $forbiddenPermissions))>
                                    <span class="text-sm text-gray-800 dark:text-gray-200">
                                        {{ __(ucfirst(str_replace('roles.', '', $permission->name))) }}
                                    </span>
                                </label>
                            @endforeach
                        @endif
                        @if(isset($grouped['permissions']))
                            @foreach ($grouped['permissions'] as $permission)
                                @php
                                    $primaryCheckbox = $area === 'OTT' ? 'text-primary-600 focus:ring-primary-500' : ($area === 'DTH' ? 'text-secondary-600 focus:ring-secondary-500' : 'text-primary-600 focus:ring-primary-500');
                                @endphp
                                <label class="flex items-center gap-2 {{ !$canEditPermissions ? 'opacity-50' : '' }}">
                                    <input type="checkbox" value="{{ $permission->name }}" wire:model="permissions"
                                        class="w-4 h-4 {{ $primaryCheckbox }} bg-white border-gray-300 rounded focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                        @disabled(!$canEditPermissions || $role === 'master' || in_array($permission->name, $forbiddenPermissions))>
                                    <span class="text-sm text-gray-800 dark:text-gray-200">
                                        {{ __(ucfirst(str_replace('permissions.', '', $permission->name))) }}
                                    </span>
                                </label>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if (!$canEditRoles && !$canEditPermissions)
        <div class="text-center mt-5 text-sm text-gray-500 dark:text-gray-400 italic">
            {{ __("You don't have permission to edit this user's roles or permissions.") }}
        </div>
    @endif

    @if ($canEditRoles || $canEditPermissions)
        <div class="mt-5 text-right">
            <button wire:click="save"
                class="{{ $primaryBtn }} {{ $primaryBtnText }} px-6 py-2 rounded-md shadow font-semibold">
                <i class="fa-solid fa-floppy-disk mr-1"></i>
                {{ __('Save changes') }}
            </button>
        </div>
    @endif
</div>
