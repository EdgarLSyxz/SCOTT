<div class="space-y-6">
    @php
        $area = auth()->user()->area ?? session('area') ?? 'OTT';
        $color = $area === 'DTH' ? 'secondary' : 'primary';
    @endphp

    <div
        class="bg-gradient-to-br from-white to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-lg shadow-lg overflow-hidden border border-{{ $color }}-200 dark:border-gray-700">
            <form x-data="uploadForm()" @submit.prevent="submit()" class="p-8">
            @csrf
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                    <i class="fa-solid fa-file-pdf text-{{ $color }}-400"></i>
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

                <div id="upload-progress" class="mt-4 space-y-2" x-show="uploading" x-cloak>
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
        <div class="bg-gradient-to-br from-white to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-lg shadow-lg overflow-hidden border border-{{ $color }}-200 dark:border-gray-700 p-8 mb-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        <i class="fa-solid fa-cloud mr-1"></i>
                        {{ __('Uploaded files on the server') }}
                    </p>
                    <select wire:model.live="selectedUploadId" wire:change="loadSelectedUpload"
                        class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white focus:ring-{{ $color }}-600 focus:border-{{ $color }}-600 dark:focus:ring-{{ $color }}-500 dark:focus:border-{{ $color }}-500">
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

    @if (count($uploads) > 1)
        @php
            $filteredAnalyticsUploads = $this->getFilteredUploadsForAnalytics();
            $availableAnalyticsYears = $this->getAvailableAnalyticsYears();
            $availableAnalyticsMonths = $this->getAvailableAnalyticsMonths();
            $uniqueTopCategories = $this->getUniqueCategoriesForTop();
        @endphp
        <div class="bg-gradient-to-br from-white to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-lg shadow-lg overflow-hidden border border-{{ $color }}-200 dark:border-gray-700 p-8">
            <div class="flex items-start justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-chart-line text-{{ $color }}-400"></i>
                        <span>{{ __('Analytics and comparison') }}</span>
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ __('Focus on essential insights by period: Top by category and file comparison.') }}
                    </p>
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-lg px-3 py-2 border border-gray-200 dark:border-gray-700 whitespace-nowrap">
                    <i class="fa-solid fa-filter mr-1"></i>
                    {{ __('Files in period') }}: <span class="font-semibold">{{ count($filteredAnalyticsUploads) }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fa-solid fa-calendar-days mr-2 text-gray-500 dark:text-gray-400"></i>
                        {{ __('Year') }}
                    </label>
                    <select wire:model.live="analyticsYear"
                        class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white focus:ring-{{ $color }}-600 focus:border-{{ $color }}-600 dark:focus:ring-{{ $color }}-500 dark:focus:border-{{ $color }}-500">
                        <option value="">{{ __('All years') }}</option>
                        @foreach ($availableAnalyticsYears as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fa-solid fa-calendar mr-2 text-gray-500 dark:text-gray-400"></i>
                        {{ __('Month') }}
                    </label>
                    <select wire:model.live="analyticsMonth"
                        class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white focus:ring-{{ $color }}-600 focus:border-{{ $color }}-600 dark:focus:ring-{{ $color }}-500 dark:focus:border-{{ $color }}-500">
                        <option value="">{{ __('All months') }}</option>
                        @foreach ($availableAnalyticsMonths as $month)
                            <option value="{{ $month['value'] }}">{{ ucfirst($month['label']) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fa-solid fa-eye mr-2 text-gray-500 dark:text-gray-400"></i>
                        {{ __('View') }}
                    </label>
                    <div class="grid grid-cols-2 rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden">
                        <button wire:click="switchAnalyticsMode('top')"
                            class="px-3 py-3 text-sm font-medium transition {{ $analyticsMode === 'top' ? 'bg-' . $color . '-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600' }}">
                            {{ __('Top by category') }}
                        </button>
                        <button wire:click="switchAnalyticsMode('compare')"
                            class="px-3 py-3 text-sm font-medium transition {{ $analyticsMode === 'compare' ? 'bg-' . $color . '-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600' }}">
                            {{ __('Compare files') }}
                        </button>
                    </div>
                </div>
            </div>

            @if (empty($filteredAnalyticsUploads))
                <div class="text-sm text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-900 rounded-lg p-3">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                    {{ __('No files found for selected period.') }}
                </div>
            @elseif ($analyticsMode === 'top')
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <i class="fa-solid fa-tags mr-2 text-gray-500 dark:text-gray-400"></i>
                                {{ __('Category') }}
                            </label>
                            <select wire:model.live="selectedCategoryForTop"
                                class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white focus:ring-{{ $color }}-600 focus:border-{{ $color }}-600 dark:focus:ring-{{ $color }}-500 dark:focus:border-{{ $color }}-500">
                                <option value="">{{ __('Select a category...') }}</option>
                                @foreach ($uniqueTopCategories as $cat)
                                    <option value="{{ $cat['key'] }}">{{ $cat['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <i class="fa-solid fa-list-ol mr-2 text-gray-500 dark:text-gray-400"></i>
                                {{ __('Top limit') }}
                            </label>
                            <select wire:model.live="topLimit"
                                class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white focus:ring-{{ $color }}-600 focus:border-{{ $color }}-600 dark:focus:ring-{{ $color }}-500 dark:focus:border-{{ $color }}-500">
                                <option value="5">Top 5</option>
                                <option value="10">Top 10</option>
                                <option value="20">Top 20</option>
                            </select>
                        </div>
                    </div>

                    @if ($selectedCategoryForTop)
                        @php
                            $topItems = $this->getTopByCategory($selectedCategoryForTop);
                            $topCategoryName = collect($uniqueTopCategories)->firstWhere('key', $selectedCategoryForTop)['name'] ?? $selectedCategoryForTop;
                        @endphp
                        @if (!empty($topItems))
                            <div class="space-y-2">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white">
                                    {{ __('Top :limit in :category', ['limit' => $topLimit, 'category' => $topCategoryName]) }}
                                </h4>
                                @foreach ($topItems as $idx => $item)
                                    <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <span class="text-xs font-bold text-{{ $color }}-600 dark:text-{{ $color }}-400 w-6">#{{ $idx + 1 }}</span>
                                            <span class="text-sm text-gray-900 dark:text-white truncate" title="{{ $item['label'] }}">{{ $item['label'] }}</span>
                                        </div>
                                        <span class="text-sm font-semibold text-{{ $color }}-600 dark:text-{{ $color }}-400">{{ number_format($item['value'], 0) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No data available for this category.') }}</p>
                        @endif
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Select a category...') }}</p>
                    @endif
                </div>
            @else
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <i class="fa-solid fa-file mr-2 text-gray-500 dark:text-gray-400"></i>
                                {{ __('File A (Base)') }}
                            </label>
                            <select wire:model.live="compareFileA"
                                class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white focus:ring-{{ $color }}-600 focus:border-{{ $color }}-600 dark:focus:ring-{{ $color }}-500 dark:focus:border-{{ $color }}-500">
                                <option value="">{{ __('Select file...') }}</option>
                                @foreach ($filteredAnalyticsUploads as $u)
                                    <option value="{{ $u['id'] }}">{{ $u['filename'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <i class="fa-solid fa-file mr-2 text-gray-500 dark:text-gray-400"></i>
                                {{ __('File B (Compare)') }}
                            </label>
                            <select wire:model.live="compareFileB"
                                class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white focus:ring-{{ $color }}-600 focus:border-{{ $color }}-600 dark:focus:ring-{{ $color }}-500 dark:focus:border-{{ $color }}-500">
                                <option value="">{{ __('Select file...') }}</option>
                                @foreach ($filteredAnalyticsUploads as $u)
                                    <option value="{{ $u['id'] }}">{{ $u['filename'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @if ($compareFileA && $compareFileB && !empty($comparisonResults))
                        <div class="space-y-2">
                            @foreach ($comparisonResults['categories'] as $catKey => $catResults)
                                @php
                                    $newCount = count($catResults['new'] ?? []);
                                    $removedCount = count($catResults['removed'] ?? []);
                                    $changedCount = count($catResults['changed'] ?? []);
                                    $totalImpact = $newCount + $removedCount + $changedCount;
                                @endphp
                                @if ($totalImpact > 0)
                                    <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $this->formatCategoryName($catKey) }}</span>
                                        <div class="flex items-center gap-2 text-xs">
                                            @if ($newCount > 0)
                                                <span class="px-2 py-1 rounded bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">+{{ $newCount }}</span>
                                            @endif
                                            @if ($removedCount > 0)
                                                <span class="px-2 py-1 rounded bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">-{{ $removedCount }}</span>
                                            @endif
                                            @if ($changedCount > 0)
                                                <span class="px-2 py-1 rounded bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">~{{ $changedCount }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @elseif ($compareFileA && $compareFileB)
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No records available.') }}</p>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Select file...') }}</p>
                    @endif
                </div>
            @endif
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
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $currentReportDate->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    @endif

    @if (!empty($categories))
        <div class="bg-gradient-to-br from-white to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-lg shadow-lg overflow-hidden border border-{{ $color }}-200 dark:border-gray-700 p-8">
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
                                            {{ $cat['count'] ?? 0 }} {{ $cat['count'] === 1 ? __('Record') : __('Records') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 ml-4 flex-shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-{{ $color }}-100 text-{{ $color }}-800 dark:bg-{{ $color }}-900/50 dark:text-{{ $color }}-200">
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
                                            $accordionRecords = $this->getAccordionRecords($cat['key']);
                                        @endphp
                                        @if (!empty($accordionRecords))
                                            <div class="space-y-3">
                                                @foreach ($accordionRecords as $idx => $record)
                                                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700 hover:border-{{ $color }}-300 dark:hover:border-{{ $color }}-600 transition-all group">
                                                        <div class="flex items-start justify-between mb-2">
                                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                                #{{ $idx + 1 }}
                                                            </span>
                                                            @if (isset($record['rank']))
                                                                <span class="text-xs font-bold text-{{ $color }}-600 dark:text-{{ $color }}-400 group-hover:text-{{ $color }}-700 dark:group-hover:text-{{ $color }}-300">
                                                                    <i class="fa-solid fa-medal mr-1"></i>Rank: {{ $record['rank'] }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="text-xs text-gray-700 dark:text-gray-300 space-y-2">
                                                            <div class="flex items-start justify-between gap-2">
                                                                <span class="font-semibold text-gray-600 dark:text-gray-400 flex-shrink-0">
                                                                    <i class="fa-solid fa-tag mr-2 text-gray-500 dark:text-gray-400"></i>
                                                                    {{ __('Label') }}:
                                                                </span>
                                                                <span class="text-gray-800 dark:text-gray-200 text-right break-all">{{ $record['label'] ?? 'N/A' }}</span>
                                                            </div>
                                                            <div class="flex items-start justify-between gap-2">
                                                                <span class="font-semibold text-gray-600 dark:text-gray-400 flex-shrink-0">
                                                                    <i class="fa-solid fa-hashtag mr-2 text-gray-500 dark:text-gray-400"></i>
                                                                    {{ __('Value') }}:
                                                                </span>
                                                                <span class="text-gray-800 dark:text-gray-200 font-mono">{{ $record['value'] ?? 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="py-8 text-center">
                                                <p class="flex items-center justify-center text-gray-500 dark:text-gray-400 text-sm">
                                                    <i class="fa-solid fa-inbox mr-2"></i>
                                                    {{ __('No records match your search.') }}
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
            fileName: '',
            uploading: false,
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

                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        this.error = data.error || data.errors?.file?.[0] || 'Upload failed';
                        this.uploading = false;
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
                    this.fileName = '';
                    if (this.$refs.uploadInput) this.$refs.uploadInput.value = '';
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
