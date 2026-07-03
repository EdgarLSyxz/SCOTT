<div class="space-y-6">
    @php
        $area = auth()->user()->area ?? session('area') ?? 'OTT';
        $color = $area === 'DTH' ? 'secondary' : 'primary';
    @endphp

    <div
        class="bg-gradient-to-br from-white to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-lg shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700">
            <form x-data="uploadForm()" @submit.prevent="submit()" class="p-8">
            @csrf
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                    <i class="fa-solid fa-file text-{{ $color }}-400"></i>
                    <span>{{ __('Upload file') }}</span>
                </h2>
            </div>

            <div class="relative group">
                <div
                    class="border-3 border-dashed border-{{ $color }}-300 dark:border-{{ $color }}-600 rounded-xl p-12 text-center cursor-pointer transition-all duration-300 hover:border-{{ $color }}-400 dark:hover:border-{{ $color }}-500 hover:bg-gray-200/50 dark:hover:bg-gray-800"
                    id="drop-zone"
                    @click="$refs.uploadInput.click()"
                    @dragover.prevent="$el.classList.add('bg-gray-100')"
                    @dragleave.prevent="$el.classList.remove('bg-gray-100')"
                    @drop.prevent="setFileFromFiles($event.dataTransfer.files); $el.classList.remove('bg-gray-100')">

                    <input type="file" id="file-input" name="file" accept=".txt" x-ref="uploadInput" class="hidden"
                        @change="setFileFromFiles($event.target.files)" />

                    <div class="space-y-4 transition-all duration-300" id="upload-prompt" x-show="!file" x-cloak>
                        <div
                            class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-{{ $color }}-200 dark:bg-{{ $color }}-900 text-{{ $color }}-600 dark:text-{{ $color }}-300">
                            <i class="fa-solid fa-cloud-arrow-up text-3xl"></i>
                        </div>
                        <div>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ __('Drop your file here') }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                {{ __('or click to browse') }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-3" id="upload-info" x-show="file" x-cloak>
                        <div
                            class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-200 dark:bg-green-900 text-green-600 dark:text-green-300">
                            <i class="fa-solid fa-check text-3xl"></i>
                        </div>
                        <div>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ __('Ready to process') }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1" x-text="fileName"></p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 space-y-2" x-show="uploading" x-cloak>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-700 dark:text-gray-300 font-medium">{{ __('Processing...') }}</span>
                        <span x-text="progress + '%'" class="text-gray-500 dark:text-gray-400">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                        <div
                            class="bg-gradient-to-r from-{{ $color }}-500 to-{{ $color }}-600 h-full rounded-full transition-all duration-300"
                            :style="'width: ' + progress + '%'"></div>
                    </div>
                </div>
            </div>

            <button type="submit"
                :disabled="uploading || !file"
                class="w-full mt-6 bg-{{ $color }}-600 hover:bg-{{ $color }}-700 text-white font-bold py-3 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed"
                id="submit-btn">
                <i class="fa-solid fa-gears text-lg"></i>
                <span>{{ __('Process file') }}</span>
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
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Processing file...') }}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400">
                    {{ __('Extracting information...') }}
                </p>
            </div>
        </div>
    </div>

    @if (!empty($uploads))
        <div class="bg-gradient-to-br from-white to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-lg shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700 p-4 sm:p-8 mb-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="w-full">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                        <i class="fa-solid fa-cloud mr-1"></i>
                        {{ __('Uploaded files on the server') }}
                    </p>
                    <select wire:model.live="selectedUploadId" wire:change="loadSelectedUpload"
                        class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full md:w-auto p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white focus:ring-{{ $color }}-600 focus:border-{{ $color }}-600 dark:focus:ring-{{ $color }}-500 dark:focus:border-{{ $color }}-500">
                        @foreach ($uploads as $u)
                            <option value="{{ $u['id'] }}">
                                {{ $u['filename'] }} — {{ $u['created_at_display'] ?? $u['created_at'] ?? $u['created_at_raw'] ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mt-3 sm:mt-0 sm:ml-4 flex flex-col sm:flex-row items-center w-full sm:w-auto gap-2">
                    <button
                        @click="confirmDelete()"
                        class="w-full sm:w-auto inline-flex items-center justify-center bg-red-600 hover:bg-red-800 text-white px-4 py-2.5 rounded-lg cursor-pointer transition"
                        x-data
                        aria-label="{{ __('Delete selected report') }}">
                        <i class="fa-solid fa-trash mr-2"></i>
                        <span>{{ __('Delete') }}</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($totalRecords)
        <div class="bg-gradient-to-br from-white to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-lg shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700 p-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-chart-simple text-{{ $color }}-400 mr-1"></i>
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
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $currentReportDate->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    @endif

    @php
        $selectedFileChartCategories = $this->getChartCategoriesForSelectedFile();
        $selectedFileChartData = $this->getSelectedCategoryChartData();
    @endphp

    @if (!empty($categories))
        <div class="bg-gradient-to-br from-white to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-lg shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700 p-8">
            <div class="flex items-center justify-between mb-6">
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

            @php
                $filteredCategories = $this->getFilteredCategories();
            @endphp

            @if (!empty($filteredCategories))
                <div class="space-y-3">
                    @foreach ($filteredCategories as $cat)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden bg-gradient-to-br from-white to-gray-100 dark:from-gray-800 dark:to-gray-900 transition-all duration-200 hover:shadow-sm dark:hover:shadow-lg">
                            <button wire:click="toggleAccordion('{{ $cat['key'] }}')"
                                class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors group">

                                <div class="flex items-center gap-4 flex-1 text-left min-w-0">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-{{ $color }}-100 dark:bg-{{ $color }}-900 flex items-center justify-center text-{{ $color }}-600 dark:text-{{ $color }}-300">
                                        <i class="fa-solid fa-folder-open text-sm"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-semibold text-gray-900 dark:text-white truncate group-hover:text-{{ $color }}-600 dark:group-hover:text-{{ $color }}-400 transition-colors">
                                            {{ $cat['name'] }}
                                        </h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            <span class="text-xs">{{ $cat['count'] ?? 0 }}</span>
                                            <span class="text-xs">{{ __('Records') }}</span>
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 ml-4 flex-shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-{{ $color }}-100 text-{{ $color }}-800 dark:bg-{{ $color }}-900/50 dark:text-{{ $color }}-200" title="{{ __('Total records') }}">
                                        {{ $cat['count'] ?? 0 }}
                                    </span>
                                    <i class="fa-solid fa-chevron-down text-{{ $color }}-500 dark:text-{{ $color }}-400 transition-transform duration-300 text-sm {{ $expandedCategoryKey === $cat['key'] ? 'rotate-180' : '' }}"></i>
                                </div>
                            </button>

                            @if ($expandedCategoryKey === $cat['key'])
                                <div class="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 animate-in fade-in slide-in-from-up-4 duration-300">
                                    <div class="px-6 pt-4 pb-3">
                                        <div class="relative w-full pr-[6px]">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                                <svg aria-hidden="true" class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <input type="text" wire:model.live="accordionSearchTerm"
                                                class="bg-white border border-gray-300 text-gray-900 text-xs sm:text-sm rounded-lg block w-full pl-9 pr-9 py-2 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-500 dark:text-white focus:ring-{{ $color }}-500 focus:border-{{ $color }}-500 transition"
                                                placeholder="{{ __('Search within category...') }}">
                                            @if ($accordionSearchTerm)
                                                <button wire:click="$set('accordionSearchTerm', '')"
                                                    class="absolute right-2 top-1/2 -translate-y-1/2 px-2 py-1 text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                                    <i class="fa-solid fa-times"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="px-6 pb-4 max-h-96 overflow-y-auto">
                                        @php
                                            $accordionRecords = $this->getAccordionRawRecords($cat['key']);
                                            $isCumulative = in_array($cat['key'], ['CUMULATIVE STREAMING EGRESS', 'CUMULATIVE REQUESTS SERVED'], true);
                                        @endphp
                                        @if (!empty($accordionRecords))
                                            <div class="space-y-3">
                                                @foreach ($accordionRecords as $idx => $record)
                                                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700 hover:border-{{ $color }}-300 dark:hover:border-{{ $color }}-600 transition-all group">
                                                        <div class="flex items-start justify-between mb-2">
                                                            @if ($isCumulative)
                                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-bold bg-{{ $color }}-100 text-{{ $color }}-800 dark:bg-{{ $color }}-900/50 dark:text-{{ $color }}-200">
                                                                    <i class="fa-solid fa-sigma mr-1"></i>{{ __('Total accumulated') }}
                                                                </span>
                                                            @else
                                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                                    #{{ $idx + 1 }}
                                                                </span>
                                                            @endif
                                                            @if (isset($record['rank']) && !$isCumulative)
                                                                <span class="text-xs font-bold text-{{ $color }}-600 dark:text-{{ $color }}-400 group-hover:text-{{ $color }}-700 dark:group-hover:text-{{ $color }}-300">
                                                                    <i class="fa-solid fa-medal mr-1"></i>Rank: {{ $record['rank'] }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="text-xs text-gray-700 dark:text-gray-300 space-y-1.5">
                                                            @php
                                                                $displayLabel = $record['label'] ?? $record['name'] ?? null;
                                                                if ($isCumulative) {
                                                                    $displayLabel = $record['unit'] ?? null;
                                                                } elseif ($displayLabel === null || $displayLabel === '') {
                                                                    $displayLabel = $record['unit'] ?? $record['bucket'] ?? null;
                                                                }
                                                                $displayValue = $record['value'] ?? $record['count'] ?? null;
                                                                $extraPairs = [];
                                                                foreach (['unit', 'bucket', 'at'] as $extraKey) {
                                                                    if (isset($record[$extraKey]) && $record[$extraKey] !== '' && $record[$extraKey] !== $displayLabel && $record[$extraKey] !== $displayValue) {
                                                                        $extraPairs[$extraKey] = $record[$extraKey];
                                                                    }
                                                                }
                                                            @endphp
                                                            <div class="flex items-start justify-between gap-3">
                                                                <span class="text-gray-800 dark:text-gray-200 break-all flex-1 min-w-0">
                                                                    @if ($displayLabel !== null && $displayLabel !== '')
                                                                        {{ $displayLabel }}
                                                                    @else
                                                                        <span class="italic text-gray-400 dark:text-gray-500">—</span>
                                                                    @endif
                                                                </span>
                                                                <span class="text-gray-800 dark:text-gray-200 font-mono whitespace-nowrap text-right">
                                                                    <span class="font-bold uppercase tracking-wider text-{{ $color }}-600 dark:text-{{ $color }}-400 mr-1">{{ __('Value') }}:</span>
                                                                    @if ($displayValue !== null)
                                                                        @if (is_numeric($displayValue))
                                                                            {{ rtrim(rtrim(number_format((float) $displayValue, 6, '.', ''), '0'), '.') }}
                                                                        @else
                                                                            {{ $displayValue }}
                                                                        @endif
                                                                    @else
                                                                        <span class="italic text-gray-400 dark:text-gray-500">—</span>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                            @if (!empty($extraPairs))
                                                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 pt-1 border-t border-gray-100 dark:border-gray-700">
                                                                    @foreach ($extraPairs as $ek => $ev)
                                                                        <span class="inline-flex items-center gap-1 text-[11px] text-gray-500 dark:text-gray-400">
                                                                            <span class="font-semibold uppercase tracking-wide">{{ __($ek) }}:</span>
                                                                            <span class="font-mono">{{ $ev }}</span>
                                                                        </span>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            @php
                                                $hasSearch = !empty($this->accordionSearchTerm);
                                            @endphp
                                            <div class="py-8 text-center">
                                                <p class="flex items-center justify-center text-gray-500 dark:text-gray-400 text-sm">
                                                    <i class="fa-solid fa-inbox mr-2"></i>
                                                    @if ($hasSearch)
                                                        {{ __('No records match your search.') }}
                                                    @else
                                                        {{ __('The parser returned no data for this category.') }}
                                                    @endif
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-12 text-center">
                    <p class="flex items-center text-gray-600 dark:text-gray-400 justify-center">
                        <i class="fa-solid fa-circle-info mr-2"></i>
                        {{ __('No categories match your search.') }}
                    </p>
                </div>
            @endif
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
                            <div class="py-3 px-3 rounded hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors border border-gray-200 dark:border-gray-700 text-sm text-gray-700 dark:text-gray-300 break-words max-w-full">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="truncate font-medium text-gray-900 dark:text-white" title="{{ $record['label'] ?? '' }}">{{ $record['label'] ?? '' }}</div>
                                        @if(!empty($record['examples']))
                                            <div class="text-xs text-gray-500 mt-1 truncate">{{ implode(' — ', $record['examples'] ?? []) }}</div>
                                        @endif
                                    </div>
                                    <div class="text-right ml-4">
                                        <div class="font-mono text-sm text-gray-900 dark:text-white">{{ number_format($record['value'] ?? 0, 0) }}</div>
                                        @if(!empty($record['occurrences']) && $record['occurrences'] > 1)
                                            <div class="text-xs text-gray-500">{{ $record['occurrences'] }} agrup.</div>
                                        @endif
                                    </div>
                                </div>
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

@once
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
@endonce

<script>
    let __logAnalyticsChartInstance = null;

    function renderLogAnalyticsChart(payload) {
        const canvas = document.getElementById('log-analytics-chart');
        if (!canvas || typeof Chart === 'undefined') return;

        const labels = Array.isArray(payload?.labels) ? payload.labels : [];
        const values = Array.isArray(payload?.values) ? payload.values : [];

        if (!labels.length || !values.length) {
            if (__logAnalyticsChartInstance) {
                try { __logAnalyticsChartInstance.destroy(); } catch (e) { }
                __logAnalyticsChartInstance = null;
            }
            return;
        }

        canvas.style.display = 'block';
        canvas.style.width = '100%';
        canvas.style.height = '180px';
        canvas.height = 180;

        const ctx = canvas.getContext('2d');

        if (__logAnalyticsChartInstance) {
            try { __logAnalyticsChartInstance.destroy(); } catch (e) { }
            __logAnalyticsChartInstance = null;
        }

        __logAnalyticsChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: '{{ __('Total value') }}',
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
                                return `{{ __('Total value') }}: ${Number(context.parsed.y || 0).toLocaleString()}`;
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
                        title: { display: true, text: '{{ __('Total value') }}', font: { size: 12, weight: 'bold' } },
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { maxRotation: 35, minRotation: 20 }
                    }
                }
            }
        });
    }

    function logAnalyticsChart(payload) {
        return {
            payload,
            init() {
                renderLogAnalyticsChart(this.payload);
            }
        };
    }
</script>

<script>
    function uploadForm() {
        return {
            file: null,
            fileName: '',
            uploading: false,
            progress: 0,
            error: '',

            humanFileSize(bytes) {
                const thresh = 1024;
                if (Math.abs(bytes) < thresh) return bytes + ' B';
                const units = ['KB', 'MB', 'GB', 'TB'];
                let u = -1;
                do {
                    bytes /= thresh;
                    ++u;
                } while (Math.abs(bytes) >= thresh && u < units.length - 1);
                return bytes.toFixed(1) + ' ' + units[u];
            },

            setFileFromFiles(files) {
                if (!files || !files.length) {
                    this.file = null;
                    this.fileName = '';
                    return;
                }

                const f = files[0];
                if (!f) {
                    this.file = null;
                    this.fileName = '';
                    return;
                }

                const maxBytes = 10240 * 1024;
                if (f.size > maxBytes) {
                    this.error = '{{ __('File size must be less than 10 MB') }}';
                    return;
                }

                this.file = f;
                this.fileName = `${f.name} (${this.humanFileSize(f.size)})`;
                this.error = '';
            },

            submit() {
                if (!this.file) return;
                this.uploading = true;
                this.progress = 0;
                this.error = '';

                const self = this;
                const formData = new FormData();
                formData.append('file', this.file);

                const xhr = new XMLHttpRequest();

                xhr.upload.addEventListener('progress', function (e) {
                    if (e.lengthComputable) {
                        self.progress = Math.round((e.loaded / e.total) * 90);
                    }
                });

                xhr.addEventListener('load', function () {
                    let data = {};
                    try { data = JSON.parse(xhr.responseText); } catch (_) {}

                    if (xhr.status < 200 || xhr.status >= 300) {
                        self.error = data.error || data.errors?.file?.[0] || 'Upload failed';
                        self.uploading = false;
                        self.progress = 0;
                        return;
                    }

                    self.progress = 100;
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

                    self.file = null;
                    self.fileName = '';
                    self.progress = 0;
                    self.uploading = false;
                    if (self.$refs.uploadInput) self.$refs.uploadInput.value = '';
                });

                xhr.addEventListener('error', function () {
                    self.error = 'Upload failed';
                    self.uploading = false;
                    self.progress = 0;
                });

                xhr.open('POST', '/admin/log-analytics/upload');
                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]')?.content || '');
                xhr.send(formData);
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

    window.addEventListener('notify', function (e) {
        const type = (e && e.detail && e.detail.type) ? e.detail.type : 'info';
        const msg = (e && e.detail && e.detail.message) ? e.detail.message : '{{ __('Action completed') }}';

        Swal.fire({
            icon: type,
            title: msg,
            timer: type === 'error' ? 4000 : 2500,
            showConfirmButton: false,
            toast: true,
            position: 'top-right'
        });
    });

    window.addEventListener('report-generated', function (e) {
        const url = (e && e.detail && e.detail.url) ? e.detail.url : null;
        const msg = (e && e.detail && e.detail.message) ? e.detail.message : '{{ __('PDF Generated successfully') }}';

        Swal.fire({
            icon: 'success',
            title: msg,
            timer: 2200,
            showConfirmButton: false,
            toast: true,
            position: 'top-right'
        });

        if (url) {
            window.open(url, '_blank');
        }
    });
</script>
