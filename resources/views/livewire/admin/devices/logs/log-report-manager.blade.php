<div class="space-y-6">
    @php
        $area = auth()->user()->area ?? session('area') ?? 'OTT';
        $color = $area === 'DTH' ? 'secondary' : 'primary';
    @endphp

    <div class="bg-gradient-to-br from-white to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-lg shadow-lg overflow-hidden border border-{{ $color }}-200 dark:border-gray-700 p-8 mb-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    <i class="fa-solid fa-file-upload mr-1"></i>
                    {{ __('Upload log report file') }}
                </p>
                <form x-data="uploadForm()" @submit.prevent="submit()" class="space-y-4">
                    <div class="flex flex-col md:flex-row gap-4">
                        <input type="file" @change="file = $event.target.files[0]" accept=".txt"
                            class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block flex-1 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white focus:ring-{{ $color }}-600 focus:border-{{ $color }}-600"
                            placeholder="{{ __('Select .txt file...') }}">
                        <button type="submit" :disabled="uploading || !file"
                            class="bg-{{ $color }}-600 hover:bg-{{ $color }}-700 disabled:bg-gray-400 text-white px-6 py-2.5 rounded-lg cursor-pointer transition font-bold flex items-center justify-center gap-2">
                            <template x-if="!uploading">
                                <span><i class="fa-solid fa-upload mr-1"></i>{{ __('Upload') }}</span>
                            </template>
                            <template x-if="uploading">
                                <span><i class="fa-solid fa-spinner animate-spin mr-1"></i>{{ __('Uploading...') }}</span>
                            </template>
                        </button>
                    </div>
                    <div x-show="error" x-cloak class="bg-red-50 border border-red-200 rounded-lg p-3 text-red-700 dark:bg-red-900/20 dark:border-red-900 dark:text-red-200 text-sm">
                        <span x-text="error"></span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if (!empty($uploads))
        <div class="bg-gradient-to-br from-white to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-lg shadow-lg overflow-hidden border border-{{ $color }}-200 dark:border-gray-700 p-8 mb-4">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        <i class="fa-solid fa-cloud mr-1"></i>
                        {{ __('Uploaded reports on the server') }}
                    </p>
                    <select wire:model.live="selectedUploadId" wire:change="loadSelectedUpload"
                        class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white focus:ring-{{ $color }}-600 focus:border-{{ $color }}-600 dark:focus:ring-{{ $color }}-500 dark:focus:border-{{ $color }}-500">
                        @foreach ($uploads as $u)
                            <option value="{{ $u['id'] }}">
                                {{ $u['filename'] }} — {{ $u['report_date'] }} ({{ $u['created_at'] }})
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

    @if ($totalRecords)
        <div class="bg-gradient-to-br from-white to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-lg shadow-lg overflow-hidden border border-{{ $color }}-200 dark:border-gray-700 p-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-chart-simple text-{{ $color }}-400"></i>
                    <span>{{ __('Summary') }}</span>
                </h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="stats-grid">
                <div class="p-4 rounded-lg md:col-span-1 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-900/20">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-300 mr-4">
                            <i class="fa-solid fa-layer-group text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Total categories') }}</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalRecords }}</p>
                        </div>
                    </div>
                </div>
                @if ($currentReportDate)
                <div class="p-4 rounded-lg md:col-span-1 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-900/20">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center text-green-600 dark:text-green-300 mr-4">
                            <i class="fa-solid fa-calendar text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Report date') }}</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $currentReportDate }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    @endif

    @if (!empty($categories))
        <div class="bg-gradient-to-br from-white to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-lg shadow-lg overflow-hidden border border-{{ $color }}-200 dark:border-gray-700">
            <div class="flex items-center justify-between p-8">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-layer-group text-{{ $color }}-400"></i>
                    <span>{{ __('Report categories') }}</span>
                </h3>
                <div class="ml-4 w-full md:w-80 lg:w-96">
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="text" wire:model.live="searchTerm"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xs sm:text-sm rounded-lg block w-full pl-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white focus:ring-{{ $color }}-500 focus:border-{{ $color }}-500"
                            placeholder="{{ __('Search category...') }}">
                        @if ($searchTerm)
                            <button wire:click="$set('searchTerm', '')"
                                class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1 text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 relative shadow-2xl rounded-lg overflow-hidden border-t border-{{ $color }}-300/50 dark:border-none">
                @php
                    $filteredCategories = $this->getFilteredCategories();
                @endphp
                @if (!empty($filteredCategories))
                    <table class="w-full text-sm text-gray-600 dark:text-gray-400">
                        <thead class="text-xs dark:text-white uppercase dark:bg-gray-600">
                            <tr>
                                <th class="px-4 py-3 text-left"><i class="fa-solid fa-layer-group mr-1.5 text-xs"></i>{{ __('Category') }}</th>
                                <th class="px-4 py-3 text-right inline-flex"><i class="fa-solid fa-bars mr-1.5 text-xs"></i>{{ __('Records') }}</th>
                                <th class="px-4 py-3 text-center w-12"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($filteredCategories as $cat)
                                <tr @click="$wire.openModal('{{ $cat['key'] }}')" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-600 text-black dark:text-white cursor-pointer group transition" title="{{ __('Click to view records') }}">
                                    <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">{{ $cat['name'] }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800 dark:bg-{{ $color }}-900 dark:text-{{ $color }}-200">
                                            {{ $cat['count'] ?? 0 }}
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
                            {{ __('No categories match your search.') }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div x-show="$wire.modalOpen" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center transition-opacity duration-300"
        style="display: none">
        <div @click="$wire.closeModal()" class="fixed inset-0 bg-black bg-opacity-50 transition-opacity z-40"></div>

        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-2xl max-w-4xl w-full mx-4 z-50 transform transition-all duration-300 max-h-96 overflow-y-auto">
            @if ($selectedCategory)
                <div class="flex items-center justify-between p-6 border-b border-{{ $color }}-200 dark:border-{{ $color }}-700 sticky top-0 bg-white dark:bg-gray-800">
                    <div>
                        <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ $selectedCategory['name'] ?? '' }}</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ $allRecordsCount }} {{ $allRecordsCount === 1 ? __('Record') : __('Records') }}
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
                            class="bg-white border border-gray-300 text-gray-900 text-xs sm:text-sm rounded-lg block w-full pl-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white focus:ring-2 focus:ring-{{ $color }}-500 focus:border-transparent transition"
                            placeholder="{{ __('Search records...') }}">
                        @if ($modalSearchTerm)
                            <button @click="$wire.set('modalSearchTerm', '')" class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1 text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        @endif
                    </div>

                    <div id="log-records-list" class="space-y-2 max-h-64 overflow-y-auto bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-{{ $color }}-200 dark:border-{{ $color }}-700">
                        @forelse ($filteredRecords as $record)
                            <div class="py-3 px-3 rounded hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors border border-gray-200 dark:border-gray-700 text-xs text-gray-700 dark:text-gray-300 font-mono break-words max-w-full">
                                <pre class="whitespace-pre-wrap word-break">{{ json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            </div>
                        @empty
                            <div class="py-4 text-center text-gray-500 dark:text-gray-400 flex items-center justify-center">
                                <i class="fa-solid fa-info-circle mr-2"></i>
                                {{ __('No records available.') }}
                            </div>
                        @endforelse
                    </div>
                    @if($modalHasMore)
                        <div class="py-2 text-center">
                            <button wire:click="loadMoreRecords"
                                class="inline-flex items-center gap-2 px-3 py-1.5 mt-4 text-xs font-medium text-gray-600 dark:text-gray-300 border border-{{ $color }}-200 dark:border-{{ $color }}-700 rounded-full hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                                <span>{{ __('Load more') }}</span>
                            </button>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end p-6 border-t border-{{ $color }}-200 dark:border-{{ $color }}-700 bg-gray-50 dark:bg-gray-900 rounded-b-lg sticky bottom-0">
                    <button @click="$wire.closeModal()" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white text-sm font-medium transition-colors flex items-center">
                        <i class="fa-solid fa-circle-xmark mr-2"></i>
                        {{ __('Close') }}
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function uploadForm() {
        return {
            file: null,
            uploading: false,
            error: '',
            async submit() {
                if (!this.file) return;
                this.uploading = true;
                this.error = '';

                const formData = new FormData();
                formData.append('file', this.file);

                try {
                    const response = await fetch('/admin/log-analytics/upload', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        }
                    });

                    const data = await response.json();
                    if (!response.ok) {
                        this.error = data.error || data.errors?.file?.[0] || 'Upload failed';
                        return;
                    }

                    Livewire.dispatch('refresh-log-uploads');

                    Swal.fire({
                        icon: 'success',
                        title: '{{ __('Success') }}',
                        text: data.message || '{{ __('File uploaded successfully') }}',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-right'
                    });

                    this.file = null;
                    this.$el.reset?.();
                } catch (err) {
                    this.error = err.message || 'Upload failed';
                }
                this.uploading = false;
            }
        };
    }

    function confirmDelete() {
        Swal.fire({
            title: '{{ __("Delete selected report?") }}',
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

    window.addEventListener('log-upload-deleted', function (e) {
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
