<div class="bg-white dark:bg-gray-800 relative shadow-2xl rounded-lg overflow-hidden mb-6"
    x-data="userGrowthChart(@js($chartLabels), @js($chartCustomers), @js($chartDevices))"
    x-init="init()"
    @user-growth-saved.window="refreshChart($event.detail.labels, $event.detail.customers, $event.detail.devices)"
>

<div class="flex flex-col gap-4 p-4 bg-white dark:bg-gray-800 md:flex-row md:items-center md:justify-between">
    <div class="min-w-0 flex-1">
        <h2 class="text-base md:text-lg font-semibold text-gray-900 dark:text-gray-100 truncate leading-tight">
            <i class="fa-solid fa-chart-line mr-2"></i>
            {{ __('User & Device Growth') }}
        </h2>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">
            {{ __('Historical growth of subscribers and registered devices.') }}
        </p>
    </div>

    @unless($showForm)
        <button
            wire:click="openForm"
            type="button"
            class="justify-center items-center text-white {{ Auth::user()?->area === 'DTH'
        ? 'bg-secondary-700 hover:bg-secondary-800 focus:ring-4 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800'
        : 'bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }} font-medium rounded-lg text-sm px-5 py-2 focus:outline-none shadow-xl">
            <i class="fa-solid fa-plus mr-1"></i>
            {{ __('Add record') }}
        </button>
    @endunless
</div>

@if($showForm)
    <div class="m-4 mt-5 p-4 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 shadow-lg">
        <h3 class="text-sm font-semibold mb-4 text-gray-900 dark:text-gray-100 flex items-center">
            <i class="fa-solid fa-{{ $editingId ? 'pen' : 'plus' }} mr-1.5"></i>
            {{ $editingId ? __('Edit record') : __('New record') }}
        </h3>

        <form wire:submit.prevent="save" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <i class="fa-regular fa-calendar mr-2 mb-2"></i>{{ __('Date') }}
                </label>
                <x-input
                    type="date"
                    wire:model.defer="recordedAt"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-600 text-gray-900 dark:text-white text-sm focus:ring-primary-500 focus:border-primary-500"
                />
                @error('recordedAt')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <i class="fa-solid fa-users mr-2 mb-2"></i>{{ __('Customers') }}
                </label>
                <x-input
                    type="number"
                    min="0"
                    wire:model.defer="customers"
                    placeholder="{{ __('Example: 30000') }}"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-600 text-gray-900 dark:text-white text-sm focus:ring-primary-500 focus:border-primary-500"
                />
                @error('customers')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <i class="fa-solid fa-hard-drive mr-2 mb-2"></i>{{ __('Devices') }}
                </label>
                <x-input
                    type="number"
                    min="0"
                    wire:model.defer="devices"
                    placeholder="{{ __('Example: 60000') }}"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-600 text-gray-900 dark:text-white text-sm focus:ring-primary-500 focus:border-primary-500"
                />
                @error('devices')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-3 flex items-center gap-2 justify-end mt-2 mb-1">
                <button
                    type="button"
                    wire:click="cancelForm"
                    class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:border-gray-400 dark:hover:border-gray-500 flex items-center gap-1"
                >
                    <i class="fa-solid fa-xmark mr-1"></i>{{ __('Cancel') }}
                </button>
                <button
                    type="submit"
                    class="ml-1.5 px-4 py-1.5 text-sm font-medium rounded-lg text-white shadow-sm transition-colors {{ Auth::user()?->area === 'DTH' ? 'bg-secondary-600 hover:bg-secondary-700 focus:ring-secondary-300 dark:bg-secondary-600 dark:hover:bg-secondary-700 dark:focus:ring-secondary-800' : 'bg-primary-600 hover:bg-primary-700 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800' }}"
                >
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i>{{ __('Save') }}
                </button>
            </div>
        </form>
    </div>
@endif

    <div class="grid grid-cols-1 xl:grid-cols-5 gap-5 p-4 pb-5">
        <div class="xl:col-span-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-3">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide flex items-center gap-2">
                    <i class="fa-solid fa-wave-square"></i>
                    {{ __('Growth over time') }}
                </p>
                @if($records->isNotEmpty())
                    <div class="flex flex-wrap items-center gap-2 md:justify-end">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            <i class="fa-solid fa-table-list"></i>
                            {{ __('Records:') }} {{ $records->count() }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            <i class="fa-regular fa-clock"></i>
                            {{ __('Last update:') }} {{ optional($records->last()?->recorded_at)->format('d/m/Y') }}
                        </span>
                    </div>
                @endif
            </div>

            <div class="relative" style="height: 380px;" wire:ignore>
                <canvas id="user-growth-chart"></canvas>
            </div>
            @if($records->isEmpty())
                <p class="text-xs text-center text-gray-500 dark:text-gray-400 mt-2">
                    {{ __('No growth records yet. Add the first one!') }}
                </p>
            @endif
            <div class="flex items-center justify-center gap-5 pt-4">
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
            <div class="px-4 py-3 bg-white dark:bg-gray-600">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-300 flex items-center gap-2">
                    <i class="fa-solid fa-table"></i>
                    {{ __('Growth records') }}
                </p>
            </div>
            <div class="overflow-y-auto max-h-[388px]">
                <table class="min-w-full text-sm text-left">
                    <thead class="dark:bg-gray-600 bg-white sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">
                                <i class="fa-solid fa-calendar mr-1"></i>
                                {{ __('Date') }}
                            </th>
                            <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300 text-right">
                                <i class="fa-solid fa-user-group mr-1"></i>
                                {{ __('Customers') }}
                            </th>
                            <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300 text-right">
                                <i class="fa-solid fa-hard-drive mr-1"></i>
                                {{ __('Devices') }}
                            </th>
                            <th class="px-3 py-2.5 w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($records->sortByDesc('recorded_at') as $record)
                        <tr class="dark:hover:bg-gray-600 transition-colors group">
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-200 whitespace-nowrap font-medium text-xs">
                                {{ $record->recorded_at->format('d-m-Y') }}
                            </td>
                            <td class="px-4 py-2 text-right text-gray-800 dark:text-gray-100 text-xs font-mono">
                                {{ number_format($record->customers) }}
                            </td>
                            <td class="px-4 py-2 text-right text-gray-800 dark:text-gray-100 text-xs font-mono">
                                {{ number_format($record->devices) }}
                            </td>
                            <td class="px-2 py-2 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity">
                                    <button
                                        wire:click="openForm({{ $record->id }})"
                                        type="button"
                                        title="{{ __('Edit') }}"
                                        class="p-1 rounded text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
                                    >
                                        <i class="fa-solid fa-pen text-[11px]"></i>
                                    </button>
                                    <button
                                        x-on:click="Swal.fire({
                                            title: '{{ addslashes(__('Are you sure?')) }}',
                                            text: '{{ addslashes(__('Are you sure you want to delete this record?')) }}',
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonColor: '#ef4444',
                                            cancelButtonColor: '#6b7280',
                                            confirmButtonText: '{{ addslashes(__('Yes, delete it!')) }}',
                                            cancelButtonText: '{{ addslashes(__('Cancel')) }}',
                                        }).then(result => { if (result.isConfirmed) $wire.delete({{ $record->id }}) })"
                                        type="button"
                                        title="{{ __('Delete') }}"
                                        class="p-1 rounded text-gray-400 hover:text-gray-500 dark:hover:text-gray-200 transition-colors"
                                    >
                                        <i class="fa-solid fa-trash text-[11px]"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-center text-xs text-gray-500 dark:text-gray-400">
                                {{ __('No records available yet.') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @php
                $latestTwo = $records->sortByDesc('recorded_at')->take(2)->values();
                $latest = $latestTwo->get(0);
                $previous = $latestTwo->get(1);
                $custGrowth = ($latest && $previous) ? ($latest->customers - $previous->customers) : 0;
                $devGrowth = ($latest && $previous) ? ($latest->devices - $previous->devices) : 0;
            @endphp

            <div class="border-t border-gray-100 dark:border-gray-700 px-4 py-2 bg-gray-100 dark:bg-gray-600 grid grid-cols-2 gap-3">
                <div class="text-center">
                    <p class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-gray-300 mb-0.5 flex items-center justify-center gap-1">
                        <i class="fa-solid fa-user-group text-[10px]"></i>
                        {{ __('Customer growth') }}
                    </p>
                    <p class="text-sm font-semibold {{ $custGrowth >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }}">
                        {{ $custGrowth >= 0 ? '+' : '' }}{{ number_format($custGrowth) }}
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-gray-300 mb-0.5 flex items-center justify-center gap-1">
                        <i class="fa-solid fa-hard-drive text-[10px]"></i>
                        {{ __('Device growth') }}
                    </p>
                    <p class="text-sm font-semibold {{ $devGrowth >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }}">
                        {{ $devGrowth >= 0 ? '+' : '' }}{{ number_format($devGrowth) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

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
