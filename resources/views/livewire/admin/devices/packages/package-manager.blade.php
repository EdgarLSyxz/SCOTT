<div class="space-y-6">
    @if (!empty($uploads))
        <div class="bg-gradient-to-br from-white to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-lg shadow-lg overflow-hidden border border-primary-200 dark:border-gray-700 p-8 mb-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        <i class="fa-solid fa-cloud mr-1"></i>
                        {{ __('Uploaded files on the server') }}
                    </p>
                    <select wire:model.live="selectedUploadId" wire:change="loadSelectedUpload"
                        class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white focus:ring-primary-600 focus:border-primary-600 dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        @foreach ($uploads as $u)
                            <option value="{{ $u['id'] }}">
                                {{ $u['filename'] }} — {{ \Carbon\Carbon::parse($u['created_at'])->format('d/m/Y H:i') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="ml-4 flex items-center space-x-4">
                    <button
                        @click="confirmDelete()"
                        class="bg-red-600 hover:bg-red-800 text-white px-4 py-2.5 rounded-lg cursor-pointer transition"
                        x-data>
                        <i class="fa-solid fa-trash mr-1"></i>
                        {{ __('Delete') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($totalPackages || $totalCustomers)
        <div class="bg-gradient-to-br from-white to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-lg shadow-lg overflow-hidden border border-primary-200 dark:border-gray-700 p-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-chart-simple text-primary-400"></i>
                    <span>{{ __('Summary') }}</span>
                </h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="stats-grid">
                <div class="p-4 rounded-lg md:col-span-1 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-900/20">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-300 mr-4">
                            <i class="fa-solid fa-boxes-packing text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Total packages') }}</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalPackages }}</p>
                        </div>
                    </div>
                </div>
                <div class="p-4 rounded-lg md:col-span-1 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-900/20">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center text-green-600 dark:text-green-300 mr-4">
                            <i class="fa-solid fa-user-group text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Total customers') }}</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalCustomers }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (!empty($packages))
            <div class="bg-gradient-to-br from-white to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-lg shadow-lg overflow-hidden border border-primary-200 dark:border-gray-700">
                <div class="flex items-center justify-between p-8">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-boxes-packing text-primary-400"></i>
                        <span>{{ __('Packages') }}</span>
                    </h3>
                    <div class="ml-4 w-full md:w-80 lg:w-96">
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input type="text" wire:model.debounce-300ms="searchTerm"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-xs sm:text-sm rounded-lg block w-full pl-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white focus:ring-primary-500 focus:border-primary-500"
                                placeholder="{{ __('Search by package name or customer ID...') }}">
                            @if ($searchTerm)
                                <button wire:click="$set('searchTerm', '')"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1 text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition">
                                    <i class="fa-solid fa-times"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 relative shadow-2xl rounded-lg overflow-hidden border-t border-gray-300/50 dark:border-none">
                    <div class="overflow-x-auto">
                    @php
                        $filteredPackages = $this->getFilteredPackages();
                    @endphp
                    </div>
                    @if (!empty($filteredPackages))
                        <table class="w-full text-sm text-gray-600 dark:text-gray-400">
                            <thead class="text-xs dark:text-white uppercase dark:bg-gray-600">
                                <tr>
                                    <th class="px-4 py-3 text-left"><i class="fa-solid fa-id-card mr-1.5 text-xs"></i>{{ __('ID') }}</th>
                                    <th class="px-4 py-3 text-left"><i class="fa-solid fa-boxes-packing mr-1.5 text-xs"></i>{{ __('Package name') }}</th>
                                    <th class="px-4 py-3 text-right"><i class="fa-solid fa-user-group mr-1.5 text-xs"></i>{{ __('Customers') }}</th>
                                    <th class="px-4 py-3 text-center w-12"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($filteredPackages as $pkg)
                                    <tr @click="$wire.openModal('{{ $pkg['id'] }}')" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-600 text-black dark:text-white cursor-pointer group transition" title="{{ __('Click to view customer IDs') }}">
                                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $pkg['id'] }}</td>
                                        <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">{{ $pkg['name'] }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                                                {{ count($pkg['customers_list'] ?? []) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <i class="fa-solid fa-chevron-right transition-colors text-gray-300 group-hover:text-gray-700 dark:text-gray-500 dark:group-hover:text-gray-400"></i>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="p-8 text-center">
                            <p class="flex items-center text-gray-600 dark:text-gray-400 justify-center">
                                <i class="fa-solid fa-circle-info mr-2"></i>
                                {{ __('No packages match your search.') }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
    @endif

    @if (!empty($packages))
        <div wire:ignore x-data="packagesChart(@entangle('packages'))" x-init="init()"
            class="bg-gradient-to-br from-white to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-lg shadow-lg overflow-hidden border border-primary-200 dark:border-gray-700 p-8">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-primary-400"></i>
                <span>{{ __('Package distribution') }}</span>
            </h3>
            <canvas id="packages-chart" x-ref="chartCanvas" class="max-w-full"></canvas>
        </div>
    @endif

    <div x-show="$wire.modalOpen" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center transition-opacity duration-300"
        style="display: none">
        <div @click="$wire.closeModal()" class="absolute inset-0 bg-black bg-opacity-50 transition-opacity"></div>

        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-2xl max-w-2xl w-full mx-4 z-50 transform transition-all duration-300">
            @if ($selectedPackage)
                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                    <div>
                        <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ $selectedPackage['name'] ?? '' }}</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ count($allCustomerIds) }} {{ count($allCustomerIds) === 1 ? __('Customer') : __('Customers') }}
                        </p>
                    </div>
                    <button @click="$wire.closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors w-8 h-8 flex items-center justify-center rounded hover:bg-gray-100 dark:hover:bg-gray-700">
                        <i class="fa-solid fa-times text-xl"></i>
                    </button>
                </div>

                <div class="p-6">
                    <div class="relative w-full mb-4">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="text" wire:model.live="modalSearchTerm"
                            class="bg-white border border-gray-300 text-gray-900 text-xs sm:text-sm rounded-lg block w-full pl-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                            placeholder="{{ __('Search customer ID...') }}">
                        @if ($modalSearchTerm)
                            <button @click="$wire.set('modalSearchTerm', '')" class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1 text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        @endif
                    </div>

                    <ul class="space-y-1 max-h-96 overflow-auto bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        @forelse ($filteredCustomerIds as $id)
                            <li
                                class="flex items-center justify-between py-2 px-3 rounded hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                <span class="text-gray-700 dark:text-gray-300 font-mono text-sm">{{ $id }}</span>
                                <span class="text-xs text-gray-400 dark:text-gray-500">#{{ $loop->index + 1 }}</span>
                            </li>
                        @empty
                            <li class="py-4 text-center text-gray-500 dark:text-gray-400 flex items-center justify-center">
                                <i class="fa-solid fa-info-circle mr-2"></i>
                                {{ __('No customer IDs available.') }}
                            </li>
                        @endforelse
                    </ul>
                </div>

                <div class="flex items-center justify-between p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 rounded-b-lg">
                    <span id="copy-feedback" class="text-sm text-green-600 dark:text-green-400 opacity-0 transition-opacity duration-300 inline-flex items-center">
                        <i class="fa-solid fa-circle-check mt-[2px] mr-2"></i>{{ __('Copied!') }}
                    </span>
                    <div class="flex space-x-4">
                        <button onclick="copyToClipboard()" class="px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium transition-colors flex items-center space-x-2">
                            <i class="fa-solid fa-copy"></i>
                            <span>{{ __('Copy all') }}</span>
                        </button>
                        <button @click="$wire.closeModal()" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white text-sm font-medium transition-colors flex items-center">
                            <i class="fa-solid fa-circle-xmark mr-2"></i>
                            {{ __('Close') }}
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@once
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
@endonce

<script>
    let __packagesChartInstance = null;

    function renderChart(packages) {
        const canvas = document.getElementById('packages-chart');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        const data = Array.isArray(packages) ? packages : [];
        const topPackages = data.slice(0, 15);
        const labels = topPackages.map(pkg => pkg.name || 'Unknown');
        const values = topPackages.map(pkg => {
            const ids = pkg.customers_list && Array.isArray(pkg.customers_list) ? pkg.customers_list : pkg.customer_ids && Array.isArray(pkg.customer_ids) ? pkg.customer_ids : [];
            return ids.length || pkg.customers || 0;
        });

        canvas.style.display = 'block';
        canvas.style.width = '100%';
        canvas.style.height = '160px';
        canvas.height = 160;

        if (__packagesChartInstance) {
            try { __packagesChartInstance.destroy(); } catch (e) { }
            __packagesChartInstance = null;
        }

        __packagesChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: '{{ __('Customers') }}',
                        data: values,
                        backgroundColor: 'rgba(59, 130, 246, 0.8)'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 13 },
                        callbacks: {
                            label: function (context) {
                                return `{{ __('Customers') }}: ${context.parsed.y}`;
                            }
                        }
                    },
                    legend: {
                        display: true,
                        position: 'top',
                        labels: { usePointStyle: true, padding: 15 }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                        title: { display: true, text: '{{ __('Number of Customers') }}', font: { size: 12, weight: 'bold' } },
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }
                    },
                    x: { grid: { display: false }, ticks: { maxRotation: 45, minRotation: 45 } }
                }
            }
        });
    }

    function packagesChart(packages) {
        return {
            packages,
            init() {
                renderChart(this.packages);
                this.$watch('packages', (v) => renderChart(v));
                this.$watch('$wire.modalOpen', () => renderChart(this.packages));
            }
        };
    }

    function copyToClipboard() {
        const ids = @json($filteredCustomerIds);
        const text = ids.join('\n');
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(() => {
                const feedback = document.getElementById('copy-feedback');
                feedback.classList.remove('opacity-0');
                feedback.classList.add('opacity-100');
                setTimeout(() => {
                    feedback.classList.add('opacity-0');
                    feedback.classList.remove('opacity-100');
                }, 2000);
            });
        } else {
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.left = '-9999px';
            document.body.appendChild(ta);
            ta.focus();
            ta.select();
            try {
                document.execCommand('copy');
                const feedback = document.getElementById('copy-feedback');
                feedback.classList.remove('opacity-0');
                feedback.classList.add('opacity-100');
                setTimeout(() => {
                    feedback.classList.add('opacity-0');
                    feedback.classList.remove('opacity-100');
                }, 2000);
            } catch (err) {
                console.warn('Copy failed', err);
            }
            document.body.removeChild(ta);
        }
    }

    function confirmDelete() {
        Swal.fire({
            title: '{{ __("Delete selected package?") }}',
            text: '{{ __("This action cannot be undone.") }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '{{ __("Yes, delete it!") }}',
            cancelButtonText: '{{ __("Cancel") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.deleteUpload();
            }
        });
    }

    window.addEventListener('package-upload-deleted', function (e) {
        const msg = (e && e.detail && e.detail.message) ? e.detail.message : '{{ __('Deleted') }}';
        Swal.fire({
            icon: 'success',
            title: msg,
            timer: 2000,
            showConfirmButton: false,
            toast: true,
            position: 'top-right'
        });
    });
</script>
