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
        <!-- Formulario de Carga PDF -->
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

        <!-- Indicador de Carga -->
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

        <!-- Contenedor de Resultados -->
        <div id="results-container" class="hidden space-y-6">
            <!-- Estadísticas Generales -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">{{ __('Summary') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="stats-grid">
                    <!-- Llenaremos con JS -->
                </div>
            </div>

            <!-- Búsqueda de Datos -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">{{ __('Search Packages') }}</h3>
                <div class="space-y-4">
                    <input type="text" id="search-input" placeholder="{{ __('Search by package name or ID...') }}"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400">

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-gray-600 dark:text-gray-400">
                            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white font-semibold">
                                <tr>
                                    <th class="px-4 py-3 text-left">{{ __('Package ID') }}</th>
                                    <th class="px-4 py-3 text-left">{{ __('Package Name') }}</th>
                                    <th class="px-4 py-3 text-right">{{ __('Customers') }}</th>
                                    <th class="px-4 py-3 text-right">{{ __('Revenue') }}</th>
                                </tr>
                            </thead>
                            <tbody id="packages-table-body">
                                <!-- Llenaremos con JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Gráfico de Datos -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">{{ __('Package Distribution') }}</h3>
                <canvas id="packages-chart" class="max-w-full"></canvas>
            </div>
        </div>

        <!-- Mensajes de Error -->
        <div id="error-container" class="hidden bg-red-50 dark:bg-red-900/30 rounded-lg p-4 border border-red-200 dark:border-red-800">
            <p class="text-sm font-semibold text-red-800 dark:text-red-200">
                <i class="fa-solid fa-circle-exclamation mr-2"></i>
                <span id="error-message"></span>
            </p>
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

    // Drag and Drop
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

    // Submit Form
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

    // Display Results
    function displayResults(data) {
        // Mostrar estadísticas
        const statsGrid = document.getElementById('stats-grid');
        statsGrid.innerHTML = `
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-900/20 p-4 rounded-lg">
                <p class="text-gray-600 dark:text-gray-400 text-sm">{{ __('Total Packages') }}</p>
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">${data.total_packages || 0}</p>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-900/20 p-4 rounded-lg">
                <p class="text-gray-600 dark:text-gray-400 text-sm">{{ __('Total Customers') }}</p>
                <p class="text-3xl font-bold text-green-600 dark:text-green-400">${data.total_customers || 0}</p>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/30 dark:to-purple-900/20 p-4 rounded-lg">
                <p class="text-gray-600 dark:text-gray-400 text-sm">{{ __('Total Revenue') }}</p>
                <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">$${(data.total_revenue || 0).toFixed(2)}</p>
            </div>
        `;

        // Renderizar tabla
        renderTable(data.packages || []);

        // Renderizar gráfico
        renderChart(data.packages || []);
    }

    function renderTable(packages) {
        const tbody = document.getElementById('packages-table-body');

        function fillTable(data) {
            tbody.innerHTML = data.map(pkg => `
                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-4 py-3">${pkg.id || '-'}</td>
                    <td class="px-4 py-3 font-semibold">${pkg.name || '-'}</td>
                    <td class="px-4 py-3 text-right">${pkg.customers || 0}</td>
                    <td class="px-4 py-3 text-right">$${(pkg.revenue || 0).toFixed(2)}</td>
                </tr>
            `).join('');
        }

        fillTable(packages);

        // Search functionality
        searchInput.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase();
            const filtered = packages.filter(pkg =>
                (pkg.name && pkg.name.toLowerCase().includes(term)) ||
                (pkg.id && pkg.id.toString().toLowerCase().includes(term))
            );
            fillTable(filtered);
        });
    }

    function renderChart(packages) {
        const ctx = document.getElementById('packages-chart');

        // Limitar a top 15 paquetes si hay muchos
        const topPackages = packages.slice(0, 15);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: topPackages.map(pkg => pkg.name || 'Unknown'),
                datasets: [
                    {
                        label: '{{ __("Customers") }}',
                        data: topPackages.map(pkg => pkg.customers || 0),
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        yAxisID: 'y'
                    },
                    {
                        label: '{{ __("Revenue") }}',
                        data: topPackages.map(pkg => (pkg.revenue || 0) / 1000), // En miles para escala
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: '{{ __("Customers") }}'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: '{{ __("Revenue (thousands)") }}'
                        },
                        grid: {
                            drawOnChartArea: false
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
});
</script>
</x-admin-layout>
