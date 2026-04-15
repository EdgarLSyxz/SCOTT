<x-admin-layout :breadcrumbs="[
        ['name' => __('Dashboard'), 'icon' => 'fa-solid fa-wrench', 'route' => route('admin.dashboard')],
        ['name' => __('Reports SLA'), 'icon' => 'fa-solid fa-traffic-light'],
    ]">

    @php
        $defaultByArea = [
            'OTT' => ['is_active' => true, 'level_1_minutes' => 30, 'level_2_minutes' => 90, 'level_3_minutes' => 180],
            'DTH' => ['is_active' => true, 'level_1_minutes' => 30, 'level_2_minutes' => 90, 'level_3_minutes' => 180],
        ];
    @endphp

    <x-slot name="action">
        <a href="{{ route('admin.dashboard') }}"
            class="hidden sm:flex justify-center items-center text-white bg-gray-600 hover:bg-gray-500 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:focus:ring-gray-800 font-medium rounded-lg text-sm px-5 py-2 text-center">
            <i class="fa-solid fa-arrow-left mr-1.5"></i>
            {{ __('Go back') }}
        </a>
    </x-slot>

    <div class="max-w-6xl mx-auto space-y-6">
        <div class="w-full bg-white rounded-lg shadow-2xl dark:border dark:bg-gray-800 dark:border-gray-700 p-6 md:p-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-traffic-light"></i>
                        {{ __('Report SLA parameters') }}
                    </h1>
                    <p class="mt-2 text-sm font-light text-gray-500 dark:text-gray-400 max-w-3xl">
                        {{ __('SLA is calculated from the latest activity (report creation or latest comment).') }}
                    </p>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-green-100 text-green-700 dark:bg-green-900/60 dark:text-green-300">
                        <i class="fa-solid fa-circle text-[8px]"></i>
                        {{ __('Level 1') }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-900/60 dark:text-yellow-300">
                        <i class="fa-solid fa-circle text-[8px]"></i>
                        {{ __('Level 2') }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-red-100 text-red-700 dark:bg-red-900/60 dark:text-red-300">
                        <i class="fa-solid fa-circle text-[8px]"></i>
                        {{ __('Level 3') }}
                    </span>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.reports.sla.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            @foreach ($areas as $area)
                @php
                    $existing = $settings[$area] ?? null;
                    $values = [
                        'is_active' => old("settings.$area.is_active", $existing->is_active ?? $defaultByArea[$area]['is_active']),
                        'level_1_minutes' => old("settings.$area.level_1_minutes", $existing->level_1_minutes ?? $defaultByArea[$area]['level_1_minutes']),
                        'level_2_minutes' => old("settings.$area.level_2_minutes", $existing->level_2_minutes ?? $defaultByArea[$area]['level_2_minutes']),
                        'level_3_minutes' => old("settings.$area.level_3_minutes", $existing->level_3_minutes ?? $defaultByArea[$area]['level_3_minutes']),
                    ];
                    $isDth = $area === 'DTH';
                    $titleColor = $isDth ? 'text-secondary-700 dark:text-secondary-300' : 'text-primary-700 dark:text-primary-300';
                    $cardRing = $isDth ? 'border-secondary-200 dark:border-secondary-800' : 'border-primary-200 dark:border-primary-800';
                    $focusRingClass = $isDth
                        ? 'focus:ring-secondary-600 focus:border-secondary-600 dark:focus:ring-secondary-500 dark:focus:border-secondary-500'
                        : 'focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500';
                    $headerBg = $isDth ? 'bg-secondary-50 dark:bg-secondary-900/20' : 'bg-primary-50 dark:bg-primary-900/20';
                @endphp

                <section class="w-full bg-white rounded-lg shadow-2xl dark:border dark:bg-gray-800 {{ $cardRing }} overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 {{ $headerBg }}">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-layer-group {{ $titleColor }}"></i>
                                <h2 class="text-lg font-semibold {{ $titleColor }}">{{ __('Area') }}: {{ $area }}</h2>
                            </div>

                            <label class="inline-flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300 select-none">
                                <input type="hidden" name="settings[{{ $area }}][is_active]" value="0">
                                <input type="checkbox" name="settings[{{ $area }}][is_active]" value="1"
                                    {{ (bool) $values['is_active'] ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span>{{ __('Enable SLA for this area') }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-5">
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-gray-50/70 dark:bg-gray-900/30">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <i class="fa-solid fa-circle text-green-500 text-[10px] mr-1"></i>
                                {{ __('Level 1 (minutes)') }}
                            </label>
                            <input type="number" min="1" max="10080" name="settings[{{ $area }}][level_1_minutes]"
                                value="{{ $values['level_1_minutes'] }}"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white {{ $focusRingClass }}">
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('Normal follow-up threshold.') }}</p>
                            @error("settings.$area.level_1_minutes")
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-gray-50/70 dark:bg-gray-900/30">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <i class="fa-solid fa-circle text-yellow-500 text-[10px] mr-1"></i>
                                {{ __('Level 2 (minutes)') }}
                            </label>
                            <input type="number" min="1" max="10080" name="settings[{{ $area }}][level_2_minutes]"
                                value="{{ $values['level_2_minutes'] }}"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white {{ $focusRingClass }}">
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('Warning threshold for delayed attention.') }}</p>
                            @error("settings.$area.level_2_minutes")
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-gray-50/70 dark:bg-gray-900/30">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <i class="fa-solid fa-circle text-red-500 text-[10px] mr-1"></i>
                                {{ __('Level 3 (minutes)') }}
                            </label>
                            <input type="number" min="1" max="10080" name="settings[{{ $area }}][level_3_minutes]"
                                value="{{ $values['level_3_minutes'] }}"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white {{ $focusRingClass }}">
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('High priority threshold.') }}</p>
                            @error("settings.$area.level_3_minutes")
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="px-6 pb-5 text-xs text-gray-500 dark:text-gray-400 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info"></i>
                        <span>{{ __('Semaforo: L1 = seguimiento, L2 = advertencia, L3 = prioridad alta.') }}</span>
                    </div>
                </section>
            @endforeach

            <div class="sticky bottom-3 z-10">
                <div class="bg-white/95 dark:bg-gray-800/95 backdrop-blur rounded-lg border border-gray-200 dark:border-gray-700 shadow-lg px-4 py-3">
                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3">
                        <a href="{{ route('admin.dashboard') }}"
                            class="inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200">
                            <i class="fa-solid fa-xmark"></i>
                            <span>{{ __('Cancel') }}</span>
                        </a>
                        <button type="submit"
                            class="inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-lg bg-gray-900 hover:bg-black text-white">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>{{ __('Save SLA settings') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-admin-layout>
