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

    @php
        $area = auth()->user()->area ?? session('area') ?? 'OTT';
        $color = $area === 'DTH' ? 'secondary' : 'primary';
    @endphp

    <div class="w-full mx-auto space-y-6">
        <div
            class="bg-gradient-to-br from-white to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-lg shadow-lg overflow-hidden border border-{{ $color }}-200 dark:border-gray-700">
            <form id="pdf-upload-form" class="p-8">
                @csrf
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                        <i class="fa-solid fa-file-pdf text-{{ $color }}-400"></i>
                        <span>{{ __('Upload PDF') }}</span>
                    </h2>
                </div>

                <div class="relative group">
                    <div class="border-3 border-dashed border-{{ $color }}-300 dark:border-{{ $color }}-600 rounded-xl p-12 text-center cursor-pointer transition-all duration-300 hover:border-{{ $color }}-400 dark:hover:border-{{ $color }}-500 hover:bg-gray-200/50 dark:hover:bg-gray-800"
                        id="drop-zone">
                        <input type="file" id="pdf-file" name="pdf_file" accept=".pdf" class="hidden" />

                        <div class="space-y-4 transition-all duration-300" id="upload-prompt">
                            <div
                                class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-{{ $color }}-200 dark:bg-{{ $color }}-900 text-{{ $color }}-600 dark:text-{{ $color }}-300">
                                <i class="fa-solid fa-cloud-arrow-up text-3xl"></i>
                            </div>
                            <div>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ __('Drop your PDF here') }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    {{ __('or click to browse') }}
                                </p>
                            </div>
                        </div>

                        <div class="hidden space-y-3" id="upload-info">
                            <div
                                class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-200 dark:bg-green-900 text-green-600 dark:text-green-300">
                                <i class="fa-solid fa-check text-3xl"></i>
                            </div>
                            <div>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ __('Ready to process') }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1" id="file-name-display"></p>
                            </div>
                        </div>
                    </div>

                    <div id="upload-progress" class="hidden mt-4 space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300 font-medium">{{ __('Processing...') }}</span>
                            <span id="progress-percent" class="text-gray-500 dark:text-gray-400">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                            <div id="progress-bar"
                                class="bg-gradient-to-r from-{{ $color }}-500 to-{{ $color }}-600 h-full rounded-full transition-all duration-300"
                                style="width: 0%"></div>
                        </div>
                    </div>
                </div>

                <button type="submit"
                    class="w-full mt-6 bg-{{ $color }}-600 hover:bg-{{ $color }}-700 text-white font-bold py-3 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    id="submit-btn" disabled>
                    <i class="fa-solid fa-gears text-lg"></i>
                    <span>{{ __('Process PDF') }}</span>
                </button>

                <p class="text-center text-xs text-gray-600 dark:text-gray-400 mt-4">
                    {{ __('Maximum file size: 50 MB') }}
                </p>
            </form>
        </div>

        <div id="loading-indicator" class="hidden bg-{{ $color }}-50 dark:bg-{{ $color }}-900/30 rounded-lg p-4">
            <div class="flex items-center space-x-3">
                <div class="animate-spin">
                    <i class="fa-solid fa-spinner text-{{ $color }}-600 dark:text-{{ $color }}-400 text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Processing PDF...') }}</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        {{ __('Extracting package and customer information') }}
                    </p>
                </div>
            </div>
        </div>

        @livewire('admin.devices.packages.package-manager')


        <div id="error-container"
            class="hidden bg-red-50 dark:bg-red-900/30 rounded-lg p-4 border border-red-200 dark:border-red-800">
            <p class="text-sm font-semibold text-red-800 dark:text-red-200">
                <i class="fa-solid fa-circle-exclamation mr-2"></i>
                <span id="error-message"></span>
            </p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dropZone = document.getElementById('drop-zone');
            const pdfFileInput = document.getElementById('pdf-file');
            const fileInfo = document.getElementById('file-info');
            const fileName = document.getElementById('file-name');
            const form = document.getElementById('pdf-upload-form');
            const loadingIndicator = document.getElementById('loading-indicator');
            const errorContainer = document.getElementById('error-container');
            const errorMessage = document.getElementById('error-message');
            const uploadPrompt = document.getElementById('upload-prompt');
            const uploadInfo = document.getElementById('upload-info');
            const fileNameDisplay = document.getElementById('file-name-display');
            const uploadProgress = document.getElementById('upload-progress');
            const progressBar = document.getElementById('progress-bar');
            const progressPercent = document.getElementById('progress-percent');
            const submitBtn = document.getElementById('submit-btn');


            function showError(message) {
                errorMessage.textContent = message;
                errorContainer.classList.remove('hidden');
                setTimeout(() => errorContainer.classList.add('hidden'), 5000);
            }

            function resetForm() {
                pdfFileInput.value = '';
                uploadPrompt.classList.remove('hidden');
                uploadInfo.classList.add('hidden');
                submitBtn.disabled = true;
                uploadProgress.classList.add('hidden');
                progressBar.style.width = '0%';
                progressPercent.textContent = '0%';
            }

            dropZone.addEventListener('click', () => pdfFileInput.click());
            dropZone.addEventListener('dragover', e => {
                e.preventDefault();
                dropZone.style.borderColor = 'var(--color-primary)';
                dropZone.style.backgroundColor = 'rgba(59, 130, 246, 0.05)';
            });
            dropZone.addEventListener('dragleave', () => {
                dropZone.style.borderColor = '';
                dropZone.style.backgroundColor = '';
            });
            dropZone.addEventListener('drop', e => {
                e.preventDefault();
                dropZone.style.borderColor = '';
                dropZone.style.backgroundColor = '';
                const files = e.dataTransfer.files;
                if (files.length) pdfFileInput.files = files;
                handleFileSelect();
            });

            pdfFileInput.addEventListener('change', handleFileSelect);

            function handleFileSelect() {
                const file = pdfFileInput.files[0];
                if (!file || !file.name.endsWith('.pdf')) {
                    showError('{{ __('Please select a PDF file') }}');
                    resetForm();
                    return;
                }
                const sizeMB = file.size / (1024 * 1024);
                if (sizeMB > 50) {
                    showError('{{ __('File size must be less than 50 MB') }}');
                    resetForm();
                    return;
                }
                fileNameDisplay.textContent = file.name;
                uploadPrompt.classList.add('hidden');
                uploadInfo.classList.remove('hidden');
                submitBtn.disabled = false;
            }

            form.addEventListener('submit', async e => {
                e.preventDefault();
                const file = pdfFileInput.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('pdf_file', file);

                try {
                    submitBtn.disabled = true;
                    uploadProgress.classList.remove('hidden');

                    const xhr = new XMLHttpRequest();
                    xhr.upload.addEventListener('progress', e => {
                        if (e.lengthComputable) {
                            const percentComplete = (e.loaded / e.total) * 100;
                            progressBar.style.width = percentComplete + '%';
                            progressPercent.textContent = Math.round(percentComplete) + '%';
                        }
                    });

                    xhr.addEventListener('load', async () => {
                        if (xhr.status === 200) {
                            const json = JSON.parse(xhr.responseText);
                            if (json.ok) {
                                resetForm();
                                loadingIndicator.classList.add('hidden');
                                errorContainer.classList.add('hidden');
                                Livewire.dispatch('refresh-uploads');
                            } else {
                                showError(json.error || json.message || '{{ __('Failed to process PDF') }}');
                                submitBtn.disabled = false;
                                uploadProgress.classList.add('hidden');
                            }
                        } else {
                            showError(xhr.statusText || '{{ __('Upload failed') }}');
                            submitBtn.disabled = false;
                            uploadProgress.classList.add('hidden');
                        }
                    });

                    xhr.addEventListener('error', () => {
                        showError('{{ __('Upload failed') }}');
                        submitBtn.disabled = false;
                        uploadProgress.classList.add('hidden');
                    });

                    xhr.open('POST', '{{ route("admin.devices.process-pdf") }}');
                    xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                    xhr.send(formData);
                } catch (err) {
                    showError(err.message || '{{ __('Upload failed') }}');
                    submitBtn.disabled = false;
                    uploadProgress.classList.add('hidden');
                }
            });
        });
    </script>
</x-admin-layout>
