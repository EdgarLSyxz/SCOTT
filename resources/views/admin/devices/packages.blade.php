<x-admin-layout :breadcrumbs="[
        ['name' => __('Dashboard'), 'icon' => 'fa-solid fa-wrench', 'route' => route('admin.dashboard')],
        ['name' => __('Devices'), 'icon' => 'fa-solid fa-hard-drive', 'route' => route('admin.devices.index')],
        ['name' => __('Packages'), 'icon' => 'fa-solid fa-boxes-packing'],
    ]">

    <x-slot name="action">
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.devices.index') }}"
                class="hidden lg:block w-full sm:w-auto justify-center items-center text-white bg-gray-600 hover:bg-gray-500 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:focus:ring-gray-800 font-medium rounded-lg text-sm px-4 py-2 text-center">
                <i class="fa-solid fa-arrow-left mr-1.5"></i>
                {{ __('Go back') }}
            </a>
        </div>
    </x-slot>
    <a href="{{ route('admin.devices.index') }}"
        class="mb-4 lg:hidden block w-full sm:w-auto justify-center items-center text-white bg-gray-600 hover:bg-gray-500 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:focus:ring-gray-800 font-medium rounded-lg text-sm px-4 py-2 text-center">
        <i class="fa-solid fa-arrow-left mr-1.5"></i>
        {{ __('Go back') }}
    </a>

    <div class="w-full mx-auto space-y-6">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                <i class="fa-solid fa-file-pdf mr-2 text-red-500"></i>
                {{ __('Upload Package Data PDF') }}
            </h2>

            <form id="pdf-upload-form" class="space-y-4">
                @csrf
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center cursor-pointer hover:border-blue-500 transition"
                     id="drop-zone">
                    <input type="file" id="pdf-file" name="pdf_file" accept=".pdf" class="hidden" />
                    <div class="space-y-2">
                        <i class="fa-solid fa-cloud-arrow-up text-4xl text-gray-400"></i>
                        <p class="text-gray-600 dark:text-gray-400">
                            {{ __('Drag and drop your PDF here or click to select') }}
                        </p>
                        <p class="text-sm text-gray-500">
                            {{ __('PDF file with package and customer information') }}
                        </p>
                    </div>
                </div>

                <div id="file-info" class="hidden p-4 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        {{ __('Selected file:') }} <strong id="file-name"></strong>
                    </p>
                </div>

                <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800 font-medium rounded-lg text-sm px-4 py-2">
                    <i class="fa-solid fa-arrow-up mr-2"></i>
                    {{ __('Process PDF') }}
                </button>
            </form>
        </div>

        <div id="loading-indicator" class="hidden bg-blue-50 dark:bg-blue-900/30 rounded-lg p-4">
            <div class="flex items-center space-x-3">
                <div class="animate-spin">
                    <i class="fa-solid fa-spinner text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Processing PDF...') }}</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('Extracting package and customer information') }}</p>
                </div>
            </div>
        </div>

        <div id="results-container" class="hidden space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">{{ __('Summary') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="stats-grid">
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">{{ __('Search Packages') }}</h3>
                <div class="space-y-4">
                    <div class="relative">
                        <input type="text" id="search-input" placeholder="{{ __('Search by package name or ID...') }}"
                            class="w-full px-4 py-2 pr-20 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        <button id="clear-search" class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1 text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hidden">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                    <div id="search-results-count" class="text-sm text-gray-600 dark:text-gray-400 hidden"></div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-gray-600 dark:text-gray-400">
                            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white font-semibold">
                                <tr>
                                    <th class="px-4 py-3 text-left">{{ __('Package ID') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('Package Name') }}</th>
                                    <th class="px-4 py-3 text-right">{{ __('Customers') }}</th>
                                    <th class="px-4 py-3 text-center w-12">
                                        <i class="fa-solid fa-hand-pointer text-xs opacity-60" title="{{ __('Click row to view customer IDs') }}"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="packages-table-body">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">{{ __('Package Distribution') }}</h3>
                <canvas id="packages-chart" class="max-w-full"></canvas>
            </div>
        </div>

        <div id="error-container" class="hidden bg-red-50 dark:bg-red-900/30 rounded-lg p-4 border border-red-200 dark:border-red-800">
            <p class="text-sm font-semibold text-red-800 dark:text-red-200">
                <i class="fa-solid fa-circle-exclamation mr-2"></i>
                <span id="error-message"></span>
            </p>
        </div>
    </div>

    <div id="customers-modal" class="fixed inset-0 z-50 hidden items-center justify-center transition-opacity duration-300">
        <div id="customers-modal-backdrop" class="absolute inset-0 bg-black bg-opacity-50 transition-opacity"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-2xl max-w-2xl w-full mx-4 z-50 transform transition-all duration-300 scale-95" id="customers-modal-content">
            <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h4 id="customers-modal-title" class="text-xl font-bold text-gray-900 dark:text-white"></h4>
                    <p id="customers-modal-count" class="text-sm text-gray-500 dark:text-gray-400 mt-1"></p>
                </div>
                <button id="customers-modal-close" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors w-8 h-8 flex items-center justify-center rounded hover:bg-gray-100 dark:hover:bg-gray-700">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            <div id="customers-modal-body" class="p-6">
                <div class="relative">
                    <input type="text" id="modal-search" placeholder="{{ __('Filter customer IDs...') }}"
                        class="w-full px-4 py-2 mb-4 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>
                <ul id="customers-list" class="space-y-1 max-h-96 overflow-auto bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700"></ul>
            </div>
            <div class="flex items-center justify-between p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 rounded-b-lg">
                <span id="copy-feedback" class="text-sm text-green-600 dark:text-green-400 opacity-0 transition-opacity duration-300">
                    <i class="fa-solid fa-check mr-1"></i>{{ __('Copied!') }}
                </span>
                <div class="flex space-x-2">
                    <button id="customers-modal-copy" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition-colors flex items-center space-x-2">
                        <i class="fa-solid fa-copy"></i>
                        <span>{{ __('Copy All') }}</span>
                    </button>
                    <button id="customers-modal-close-2" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white text-sm font-medium transition-colors">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('drop-zone');
    const pdfFileInput = document.getElementById('pdf-file');
    const fileInfo = document.getElementById('file-info');
    const fileName = document.getElementById('file-name');
    const form = document.getElementById('pdf-upload-form');
    const loadingIndicator = document.getElementById('loading-indicator');
    const resultsContainer = document.getElementById('results-container');
    const errorContainer = document.getElementById('error-container');
    const errorMessage = document.getElementById('error-message');
    const searchInput = document.getElementById('search-input');
    let packageData = [];

    dropZone.addEventListener('click', () => pdfFileInput.click());

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-blue-500', 'bg-blue-50');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-blue-500', 'bg-blue-50');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-blue-500', 'bg-blue-50');

        const files = e.dataTransfer.files;
        if (files.length > 0 && files[0].type === 'application/pdf') {
            pdfFileInput.files = files;
            updateFileInfo();
        } else {
            showError('{{ __("Please select a valid PDF file") }}');
        }
    });

    pdfFileInput.addEventListener('change', updateFileInfo);

    function updateFileInfo() {
        if (pdfFileInput.files.length > 0) {
            fileName.textContent = pdfFileInput.files[0].name;
            fileInfo.classList.remove('hidden');
        } else {
            fileInfo.classList.add('hidden');
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!pdfFileInput.files.length) {
            showError('{{ __("Please select a PDF file") }}');
            return;
        }

        const formData = new FormData();
        formData.append('pdf_file', pdfFileInput.files[0]);
        formData.append('_token', document.querySelector('input[name="_token"]').value);

        loadingIndicator.classList.remove('hidden');
        errorContainer.classList.add('hidden');
        resultsContainer.classList.add('hidden');

        try {
            const response = await fetch('{{ route("admin.devices.process-pdf") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || '{{ __("Error processing PDF") }}');
            }

            packageData = data.packages || [];
            displayResults(data);
            resultsContainer.classList.remove('hidden');
        } catch (error) {
            showError(error.message);
        } finally {
            loadingIndicator.classList.add('hidden');
        }
    });

    function displayResults(data) {
        const raw = data.packages || data || [];
        let packages = [];

        function normalizeItem(key, item) {
            const ids = item.customers || item.customers_list || item.customer_ids || [];
            const count = item.customers_count || (Array.isArray(ids) ? ids.length : (item.customers || 0));

            return {
                id: item.service_id || item.service_id === 0 ? String(item.service_id) : String(key),
                name: item.name || item.title || '',
                customers: count,
                customers_list: Array.isArray(ids) ? ids : [],
            };
        }

        if (Array.isArray(raw)) {
            packages = raw.map((it, idx) => {
                if (it.customers_list || it.customer_ids || Array.isArray(it.customers)) {
                    return {
                        id: it.id || it.service_id || String(idx),
                        name: it.name || it.title || '',
                        customers: it.customers_count || (Array.isArray(it.customers) ? it.customers.length : it.customers || 0),
                        customers_list: it.customers_list || it.customer_ids || (Array.isArray(it.customers) ? it.customers : []),
                    };
                }
                return normalizeItem(idx, it);
            });
        } else if (raw && typeof raw === 'object') {
            packages = Object.keys(raw).map(key => normalizeItem(key, raw[key]));
        }

        const totalPackages = packages.length;
        const totalCustomers = packages.reduce((sum, p) => sum + (p.customers || (p.customers_list ? p.customers_list.length : 0)), 0);

        packageData = packages;

        const statsGrid = document.getElementById('stats-grid');
        statsGrid.innerHTML = `
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-900/20 p-4 rounded-lg">
                <p class="text-gray-600 dark:text-gray-400 text-sm">{{ __('Total Packages') }}</p>
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">${totalPackages}</p>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-900/20 p-4 rounded-lg">
                <p class="text-gray-600 dark:text-gray-400 text-sm">{{ __('Total Customers') }}</p>
                <p class="text-3xl font-bold text-green-600 dark:text-green-400">${totalCustomers}</p>
            </div>
        `;

        renderTable(packages);
        renderChart(packages);
    }

    function renderTable(packages) {
        const tbody = document.getElementById('packages-table-body');

        function fillTable(data) {
            tbody.innerHTML = data.map(pkg => {
                const ids = pkg.customers_list && Array.isArray(pkg.customers_list) ? pkg.customers_list : pkg.customer_ids && Array.isArray(pkg.customer_ids) ? pkg.customer_ids : [];
                const count = ids.length || pkg.customers || 0;

                return `
                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-gray-700 cursor-pointer transition-colors group" data-pkg-id="${pkg.id || ''}" title="{{ __('Click to view customer IDs') }}">
                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">${pkg.id || '-'}</td>
                    <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">${pkg.name || '-'}</td>
                    <td class="px-4 py-3 text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            ${count}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <i class="fa-solid fa-chevron-right text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors text-xs"></i>
                    </td>
                </tr>
                `;
            }).join('');
        }

        fillTable(packages);

        const clearSearchBtn = document.getElementById('clear-search');
        const searchResultsCount = document.getElementById('search-results-count');

        searchInput.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase();
            const filtered = packages.filter(pkg =>
                (pkg.name && pkg.name.toLowerCase().includes(term)) ||
                (pkg.id && pkg.id.toString().toLowerCase().includes(term)) ||
                (pkg.customer_ids && pkg.customer_ids.join(' ').toLowerCase().includes(term)) ||
                (pkg.customers_list && pkg.customers_list.join(' ').toLowerCase().includes(term))
            );
            fillTable(filtered);

            if (term) {
                clearSearchBtn.classList.remove('hidden');
                searchResultsCount.classList.remove('hidden');
                searchResultsCount.textContent = `{{ __('Showing') }} ${filtered.length} {{ __('of') }} ${packages.length} {{ __('packages') }}`;
            } else {
                clearSearchBtn.classList.add('hidden');
                searchResultsCount.classList.add('hidden');
            }
        });

        clearSearchBtn.addEventListener('click', () => {
            searchInput.value = '';
            fillTable(packages);
            clearSearchBtn.classList.add('hidden');
            searchResultsCount.classList.add('hidden');
        });

        tbody.addEventListener('click', (e) => {
            const tr = e.target.closest('tr');
            if (!tr) return;
            const pkgId = tr.getAttribute('data-pkg-id');
            if (!pkgId) return;
            const pkg = packageData.find(p => String(p.id) === String(pkgId));
            if (!pkg) return;
            openCustomersModal(pkg);
        });
    }

    function renderChart(packages) {
        const ctx = document.getElementById('packages-chart');

        const topPackages = packages.slice(0, 15);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: topPackages.map(pkg => pkg.name || 'Unknown'),
                datasets: [
                    {
                        label: '{{ __("Customers") }}',
                        data: topPackages.map(pkg => {
                            const ids = pkg.customers_list && Array.isArray(pkg.customers_list) ? pkg.customers_list : pkg.customer_ids && Array.isArray(pkg.customer_ids) ? pkg.customer_ids : [];
                            return ids.length || pkg.customers || 0;
                        }),
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
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                return `{{ __('Customers') }}: ${context.parsed.y}`;
                            }
                        }
                    },
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        title: {
                            display: true,
                            text: '{{ __('Number of Customers') }}',
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });
    }

    function showError(message) {
        errorMessage.textContent = message;
        errorContainer.classList.remove('hidden');
        resultsContainer.classList.add('hidden');
    }

    const customersModal = document.getElementById('customers-modal');
    const customersBackdrop = document.getElementById('customers-modal-backdrop');
    const customersTitle = document.getElementById('customers-modal-title');
    const customersList = document.getElementById('customers-list');
    const customersClose = document.getElementById('customers-modal-close');
    const customersClose2 = document.getElementById('customers-modal-close-2');
    const customersCopy = document.getElementById('customers-modal-copy');

    const modalContent = document.getElementById('customers-modal-content');
    const modalSearch = document.getElementById('modal-search');
    const customersModalCount = document.getElementById('customers-modal-count');
    const copyFeedback = document.getElementById('copy-feedback');
    let allCustomerIds = [];

    function openCustomersModal(pkg) {
        customersTitle.textContent = pkg.name || String(pkg.id);
        const ids = pkg.customers_list && Array.isArray(pkg.customers_list) ? pkg.customers_list : pkg.customer_ids && Array.isArray(pkg.customer_ids) ? pkg.customer_ids : [];
        allCustomerIds = ids;
        customersModalCount.textContent = `${ids.length} ${ids.length === 1 ? '{{ __('customer') }}' : '{{ __('customers') }}'}`;

        renderCustomersList(ids);
        modalSearch.value = '';

        customersModal.classList.remove('hidden');
        customersModal.classList.add('flex');
        setTimeout(() => {
            modalContent.classList.remove('scale-95');
            modalContent.classList.add('scale-100');
        }, 10);
        document.body.style.overflow = 'hidden';
    }

    function closeCustomersModal() {
        modalContent.classList.remove('scale-100');
        modalContent.classList.add('scale-95');
        setTimeout(() => {
            customersModal.classList.add('hidden');
            customersModal.classList.remove('flex');
        }, 200);
        document.body.style.overflow = '';
    }

    function renderCustomersList(ids) {
        customersList.innerHTML = ids.length ? ids.map((id, idx) => `
            <li class="flex items-center justify-between py-2 px-3 rounded hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                <span class="text-gray-700 dark:text-gray-300 font-mono text-sm">${id}</span>
                <span class="text-xs text-gray-400 dark:text-gray-500">#${idx + 1}</span>
            </li>
        `).join('') : `<li class="py-4 text-center text-gray-500 dark:text-gray-400">{{ __('No customer IDs available') }}</li>`;
    }

    modalSearch.addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase();
        const filtered = allCustomerIds.filter(id => id.toLowerCase().includes(term));
        renderCustomersList(filtered);
    });

    customersClose.addEventListener('click', closeCustomersModal);
    customersClose2.addEventListener('click', closeCustomersModal);
    customersBackdrop.addEventListener('click', closeCustomersModal);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !customersModal.classList.contains('hidden')) {
            closeCustomersModal();
        }
    });

    customersCopy.addEventListener('click', () => {
        const text = allCustomerIds.join('\n');
        navigator.clipboard.writeText(text).then(() => {
            copyFeedback.classList.remove('opacity-0');
            copyFeedback.classList.add('opacity-100');
            customersCopy.innerHTML = '<i class="fa-solid fa-check"></i><span>{{ __('Copied!') }}</span>';
            setTimeout(() => {
                copyFeedback.classList.remove('opacity-100');
                copyFeedback.classList.add('opacity-0');
                customersCopy.innerHTML = '<i class="fa-solid fa-copy"></i><span>{{ __('Copy All') }}</span>';
            }, 2000);
        }).catch(() => {
            alert('{{ __('Failed to copy to clipboard') }}');
        });
    });
});
</script>
</x-admin-layout>
