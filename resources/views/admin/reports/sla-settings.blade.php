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

    <div class="max-w-5xl mx-auto space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-traffic-light"></i>
                <span>{{ __('Report SLA parameters') }}</span>
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                {{ __('SLA is calculated from the latest activity (report creation or latest comment).') }}
            </p>
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
                @endphp

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border {{ $cardRing }} p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <h3 class="text-lg font-semibold {{ $titleColor }}">{{ __('Area') }}: {{ $area }}</h3>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="hidden" name="settings[{{ $area }}][is_active]" value="0">
                            <input type="checkbox" name="settings[{{ $area }}][is_active]" value="1"
                                {{ (bool) $values['is_active'] ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span>{{ __('Enable SLA for this area') }}</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Level 1 (minutes)') }}</label>
                            <input type="number" min="1" max="10080" name="settings[{{ $area }}][level_1_minutes]"
                                value="{{ $values['level_1_minutes'] }}"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            @error("settings.$area.level_1_minutes")
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Level 2 (minutes)') }}</label>
                            <input type="number" min="1" max="10080" name="settings[{{ $area }}][level_2_minutes]"
                                value="{{ $values['level_2_minutes'] }}"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Level 3 (minutes)') }}</label>
                            <input type="number" min="1" max="10080" name="settings[{{ $area }}][level_3_minutes]"
                                value="{{ $values['level_3_minutes'] }}"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>

                    <div class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('Semaforo: L1 = seguimiento, L2 = advertencia, L3 = prioridad alta.') }}
                    </div>
                </div>
            @endforeach

            <div class="flex justify-end">
                <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-gray-900 hover:bg-black text-white">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>{{ __('Save SLA settings') }}</span>
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
