<div
    x-data="userGrowthChart(@js($chartLabels), @js($chartCustomers), @js($chartDevices))"
    x-init="init()"
    @user-growth-saved.window="refreshChart(@js($chartLabels), @js($chartCustomers), @js($chartDevices))"
>

<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div>
        <h2 class="text-base md:text-lg font-semibold text-gray-900 dark:text-gray-100">
            <i class="fa-solid fa-chart-line mr-2 text-primary-500"></i>
            {{ __('User & Device Growth') }}
        </h2>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
            {{ __('Historical growth of subscribers and registered devices.') }}
        </p>
    </div>
    @unless($showForm)
        <button
            wire:click="openForm"
            type="button"
            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-700 dark:hover:bg-primary-600 transition-colors shadow-sm"
        >
            <i class="fa-solid fa-plus"></i>
            {{ __('Add record') }}
        </button>
    @endunless
</div>

@if($showForm)
    <div class="mb-5 p-4 rounded-xl border border-primary-200 dark:border-primary-800 bg-primary-50/40 dark:bg-primary-900/10 shadow-sm">
        <h3 class="text-sm font-semibold text-primary-700 dark:text-primary-300 mb-3">
            <i class="fa-solid fa-{{ $editingId ? 'pen' : 'plus' }} mr-1.5"></i>
            {{ $editingId ? __('Edit record') : __('New record') }}
        </h3>

        <form wire:submit.prevent="save" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <i class="fa-regular fa-calendar mr-1"></i>{{ __('Date') }}
                </label>
                <input
                    type="date"
                    wire:model.defer="recordedAt"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-primary-500 focus:border-primary-500"
                >
                @error('recordedAt')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <i class="fa-solid fa-users mr-1"></i>{{ __('Customers') }}
                </label>
                <input
                    type="number"
                    min="0"
                    wire:model.defer="customers"
                    placeholder="e.g. 35000"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-primary-500 focus:border-primary-500"
                >
                @error('customers')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <i class="fa-solid fa-microchip mr-1"></i>{{ __('Devices') }}
                </label>
                <input
                    type="number"
                    min="0"
                    wire:model.defer="devices"
                    placeholder="e.g. 60000"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-primary-500 focus:border-primary-500"
                >
                @error('devices')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-3 flex items-center gap-2 justify-end">
                <button
                    type="button"
                    wire:click="cancelForm"
                    class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                >
                    <i class="fa-solid fa-xmark mr-1"></i>{{ __('Cancel') }}
                </button>
                <button
                    type="submit"
                    class="px-4 py-1.5 text-sm font-medium rounded-lg bg-primary-600 hover:bg-primary-700 text-white shadow-sm transition-colors"
                >
                    <i class="fa-solid fa-floppy-disk mr-1"></i>{{ __('Save') }}
                </button>
            </div>
        </form>
    </div>
@endif

@if($records->isEmpty())
    <div class="flex flex-col items-center justify-center py-14 text-gray-400 dark:text-gray-500">
        <i class="fa-solid fa-chart-line text-4xl mb-3 opacity-30"></i>
        <p class="text-sm">{{ __('No growth records yet. Add the first one!') }}</p>
    </div>
@else

<div class="grid grid-cols-1 xl:grid-cols-5 gap-5">
    <div class="xl:col-span-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-3 uppercase tracking-wide">
            {{ __('Growth over time') }}
        </p>
        <div class="relative" style="height: 280px;">
            <canvas id="user-growth-chart"></canvas>
        </div>
        <div class="flex items-center justify-center gap-5 mt-3">
            <span class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                <span class="inline-block w-5 h-0.5 rounded-full bg-[#1d6fa4]"></span>
                {{ __('Customers') }}
            </span>
            <span class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                <span class="inline-block w-5 h-0.5 rounded-full bg-[#e07b39]"></span>
                {{ __('Devices') }}
            </span>
        </div>
    </div>

    <div class="xl:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-y-auto max-h-[340px]">
            <table class="min-w-full text-sm text-left divide-y divide-gray-100 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0 z-10">
                    <tr>
                        <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">
                            {{ __('Date') }}
                        </th>
                        <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-[#1d6fa4] dark:text-sky-300 text-right">
                            {{ __('Customers') }}
                        </th>
                        <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-[#e07b39] dark:text-orange-300 text-right">
                            {{ __('Devices') }}
                        </th>
                        <th class="px-3 py-2.5 w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                    @foreach($records->sortByDesc('recorded_at') as $record)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors group">
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-200 whitespace-nowrap font-medium text-xs">
                            {{ $record->recorded_at->format('d-M-Y') }}
                        </td>
                        <td class="px-4 py-2 text-right text-gray-800 dark:text-gray-100 text-xs font-mono">
                            {{ number_format($record->customers) }}
                        </td>
                        <td class="px-4 py-2 text-right text-gray-800 dark:text-gray-100 text-xs font-mono">
                            {{ number_format($record->devices) }}
                        </td>
                        <td class="px-2 py-2 text-right">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button
                                    wire:click="openForm({{ $record->id }})"
                                    type="button"
                                    title="{{ __('Edit') }}"
                                    class="p-1 rounded text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                                >
                                    <i class="fa-solid fa-pen text-[11px]"></i>
                                </button>
                                <button
                                    wire:click="delete({{ $record->id }})"
                                    wire:confirm="{{ __('Are you sure you want to delete this record?') }}"
                                    type="button"
                                    title="{{ __('Delete') }}"
                                    class="p-1 rounded text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors"
                                >
                                    <i class="fa-solid fa-trash text-[11px]"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($records->count() >= 2)
        @php
            $first = $records->first();
            $last  = $records->last();
            $custGrowth   = $last->customers - $first->customers;
            $devGrowth    = $last->devices   - $first->devices;
        @endphp
        <div class="border-t border-gray-100 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-700/50 grid grid-cols-2 gap-3 mt-auto">
            <div class="text-center">
                <p class="text-[10px] uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-0.5">{{ __('Customer growth') }}</p>
                <p class="text-sm font-semibold {{ $custGrowth >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }}">
                    {{ $custGrowth >= 0 ? '+' : '' }}{{ number_format($custGrowth) }}
                </p>
            </div>
            <div class="text-center">
                <p class="text-[10px] uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-0.5">{{ __('Device growth') }}</p>
                <p class="text-sm font-semibold {{ $devGrowth >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }}">
                    {{ $devGrowth >= 0 ? '+' : '' }}{{ number_format($devGrowth) }}
                </p>
            </div>
        </div>
        @endif
    </div>
</div>
@endif

</div>

@push('js')
    <script>
        function userGrowthChart(labels, customers, devices) {
            let chart = null;

            function isDark() {
                return document.documentElement.classList.contains('dark');
            }

            function gridColor() {
                return isDark() ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.06)';
            }

            function tickColor() {
                return isDark() ? '#9ca3af' : '#6b7280';
            }

            function buildChart(lbls, custs, devs) {
                const ctx = document.getElementById('user-growth-chart');
                if (!ctx) return;

                if (chart) chart.destroy();

                chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: lbls,
                        datasets: [
                            {
                                label: '{{ __("Customers") }}',
                                data: custs,
                                borderColor: '#1d6fa4',
                                backgroundColor: 'rgba(29,111,164,0.08)',
                                pointBackgroundColor: '#1d6fa4',
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                borderWidth: 2.5,
                                tension: 0.35,
                                fill: true,
                            },
                            {
                                label: '{{ __("Devices") }}',
                                data: devs,
                                borderColor: '#e07b39',
                                backgroundColor: 'rgba(224,123,57,0.08)',
                                pointBackgroundColor: '#e07b39',
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                borderWidth: 2.5,
                                tension: 0.35,
                                fill: true,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString()}`,
                                },
                            },
                        },
                        scales: {
                            x: {
                                grid: { color: gridColor() },
                                ticks: { color: tickColor(), font: { size: 11 }, maxRotation: 45 },
                            },
                            y: {
                                grid: { color: gridColor() },
                                ticks: {
                                    color: tickColor(),
                                    font: { size: 11 },
                                    callback: val => val >= 1000 ? (val / 1000).toFixed(0) + 'k' : val,
                                },
                                beginAtZero: false,
                            },
                        },
                        interaction: { mode: 'nearest', axis: 'x', intersect: false },
                    },
                });
            }

            return {
                init() {
                    buildChart(labels, customers, devices);

                    const observer = new MutationObserver(() => buildChart(labels, customers, devices));
                    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
                },
                refreshChart(lbls, custs, devs) {
                    labels    = lbls;
                    customers = custs;
                    devices   = devs;
                    this.$nextTick(() => buildChart(lbls, custs, devs));
                },
            };
        }
    </script>
@endpush
