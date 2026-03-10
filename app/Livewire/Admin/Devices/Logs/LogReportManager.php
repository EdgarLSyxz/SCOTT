<?php

namespace App\Livewire\Admin\Devices\Logs;

use App\Models\LogAnalytic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

class LogReportManager extends Component
{
    use WithFileUploads;

    public $uploads = [];
    public $selectedUploadId = null;
    public $categories = [];
    public $totalRecords = 0;
    public $searchTerm = '';
    public $selectedCategory = null;
    public $modalOpen = false;
    public $allRecordsCount = 0;
    public $filteredRecords = [];
    public $currentCategoryKey = null;
    public $currentUploadId = null;
    public $recordPage = 1;
    public $recordPageSize = 200;
    public $modalHasMore = false;
    public $modalSearchTerm = '';
    public $currentReportDate = null;
    public $expandedCategoryKey = null;
    public $accordionSearchTerm = '';
    public $analyticsMode = 'top';
    public $analyticsYear = '';
    public $analyticsMonth = '';
    public $selectedFileIds = [];
    public $topLimit = 10;
    public $selectedCategoryForTop = null;
    public $selectedCategoryForChart = null;
    public $consolidatedData = [];
    public $compareFileA = null;
    public $compareFileB = null;
    public $comparisonResults = [];

    public function mount()
    {
        $this->loadUploads();
    }

    #[On('refresh-log-uploads')]
    public function handleRefreshUploads()
    {
        $this->loadUploads();
    }

    public function loadUploads()
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $models = LogAnalytic::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'user_id', 'filename', 'report_date', 'created_at']);

        $this->uploads = $models->map(function ($m) {
            $attrs = $m->getAttributes();
            return [
                'id' => $m->id,
                'user_id' => $m->user_id,
                'filename' => $m->filename,
                'report_date' => $m->report_date ? $m->report_date->format('Y-m-d') : 'N/A',
                'created_at' => $m->created_at ? $m->created_at->format('d/m/Y H:i:s') : ($attrs['created_at'] ?? null),
                'created_at_raw' => $attrs['created_at'] ?? null,
            ];
        })->toArray();

        if (!empty($this->uploads)) {
            $this->selectedUploadId = $this->uploads[0]['id'];
            $this->loadSelectedUpload();
        }

        $this->syncSelectedFileIds();
    }

    public function loadSelectedUpload()
    {
        $user = Auth::user();
        if (!$user || !$this->selectedUploadId) {
            return;
        }

        $upload = LogAnalytic::where('id', $this->selectedUploadId)
            ->where('user_id', $user->id)
            ->first();

        if ($upload) {
            $this->currentReportDate = $upload->report_date;
            $this->normalizeCategories($upload->data);
        }
    }

    public function normalizeCategories($data)
    {
        $raw = is_array($data) ? $data : [];
        $categories = [];

        if (is_array($raw)) {
            foreach ($raw as $categoryKey => $items) {
                if (is_array($items) && !empty($items)) {
                    $categoryItems = [];
                    foreach ($items as $idx => $item) {
                        if (is_array($item)) {
                            $categoryItems[] = $item;
                        }
                    }

                    if (!empty($categoryItems)) {
                        $categories[] = [
                            'key' => $categoryKey,
                            'name' => $this->formatCategoryName($categoryKey),
                            'items' => $categoryItems,
                            'count' => count($categoryItems),
                        ];
                    }
                }
            }
        }

        $this->categories = $categories;
        $this->totalRecords = count($categories);

        $availableCategoryKeys = array_column($categories, 'key');
        if (empty($availableCategoryKeys)) {
            $this->selectedCategoryForChart = null;
        } elseif (!in_array($this->selectedCategoryForChart, $availableCategoryKeys, true)) {
            $this->selectedCategoryForChart = $availableCategoryKeys[0];
        }
    }

    protected function formatCategoryName($key)
    {
        $formatted = str_replace(['_', '-'], ' ', (string) $key);
        $formatted = preg_replace('/\s+/', ' ', trim($formatted));
        return ucfirst($formatted);
    }

    protected function normalizeCategoryKey($key)
    {
        $normalized = Str::of((string) $key)
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9]+/', '_')
            ->trim('_')
            ->lower()
            ->value();

        return $normalized;
    }

    protected function categoryFingerprint($value)
    {
        return Str::of((string) $value)
            ->ascii()
            ->replace(['_', '-'], ' ')
            ->squish()
            ->lower()
            ->value();
    }

    protected function normalizeLabelKey($label)
    {
        return Str::of((string) $label)
            ->ascii()
            ->squish()
            ->lower()
            ->value();
    }

    public function deleteUpload()
    {
        $user = Auth::user();
        if (!$user || !$this->selectedUploadId) {
            return;
        }

        $upload = LogAnalytic::where('id', $this->selectedUploadId)
            ->where('user_id', $user->id)
            ->first();

        if ($upload) {
            $upload->delete();
            $this->loadUploads();
            $this->selectedCategory = null;
            $this->categories = [];
            $this->totalRecords = 0;
            $this->dispatch('log-upload-deleted', [
                'message' => __('Log report deleted successfully'),
            ]);
        }
    }

    public function openModal($categoryKey)
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $uploadId = $this->selectedUploadId;
        if (!$uploadId) {
            return;
        }

        $upload = LogAnalytic::where('id', $uploadId)
            ->where('user_id', $user->id)
            ->first();

        if (!$upload) {
            return;
        }

        $items = $upload->getCategory($categoryKey) ?? [];

        $cacheKey = 'log_records_' . $uploadId . '_' . $categoryKey . '_' . $user->id;
        Cache::put($cacheKey, $items, now()->addMinutes(10));

        $this->currentCategoryKey = $categoryKey;
        $this->currentUploadId = $uploadId;
        $this->selectedCategory = [
            'key' => $categoryKey,
            'name' => $this->formatCategoryName($categoryKey),
            'records' => count($items),
        ];
        $this->allRecordsCount = count($items);
        $this->recordPage = 1;
        $this->modalSearchTerm = '';
        $this->modalHasMore = $this->allRecordsCount > $this->recordPageSize;
        $this->filteredRecords = array_slice($items, 0, $this->recordPageSize);
        $this->modalOpen = true;
    }

    public function closeModal()
    {
        if ($this->currentCategoryKey && $this->currentUploadId) {
            $user = Auth::user();
            $cacheKey = 'log_records_' . $this->currentUploadId . '_' . $this->currentCategoryKey . '_' . ($user?->id ?? '');
            Cache::forget($cacheKey);
        }

        $this->modalOpen = false;
        $this->selectedCategory = null;
        $this->allRecordsCount = 0;
        $this->filteredRecords = [];
        $this->modalSearchTerm = '';
        $this->currentCategoryKey = null;
        $this->currentUploadId = null;
        $this->recordPage = 1;
        $this->modalHasMore = false;
    }

    public function filterModalSearch($term)
    {
        $this->modalSearchTerm = $term;
        $term = strtolower($term);

        if (!$this->currentCategoryKey || !$this->currentUploadId) {
            $this->filteredRecords = [];
            return;
        }

        $user = Auth::user();
        $cacheKey = 'log_records_' . $this->currentUploadId . '_' . $this->currentCategoryKey . '_' . $user->id;
        $records = Cache::get($cacheKey, []);

        if ($term === '') {
            $this->recordPage = 1;
            $this->modalHasMore = count($records) > $this->recordPageSize;
            $this->filteredRecords = array_slice($records, 0, $this->recordPageSize);
            return;
        }

        $filtered = array_values(array_filter($records, function ($record) use ($term) {
            $searchable = json_encode($record);
            return strpos(strtolower($searchable), $term) !== false;
        }));

        $this->filteredRecords = $filtered;
        $this->modalHasMore = false;
    }

    public function updatedModalSearchTerm($value)
    {
        $this->filterModalSearch($value ?? '');
    }

    public function loadMoreRecords()
    {
        if (!$this->currentCategoryKey || !$this->currentUploadId) return;
        $user = Auth::user();
        $cacheKey = 'log_records_' . $this->currentUploadId . '_' . $this->currentCategoryKey . '_' . $user->id;
        $records = Cache::get($cacheKey, []);
        if (empty($records)) return;

        $this->recordPage++;
        $offset = ($this->recordPage - 1) * $this->recordPageSize;
        $next = array_slice($records, $offset, $this->recordPageSize);
        if (!empty($next)) {
            $this->filteredRecords = array_merge($this->filteredRecords, $next);
        }
        $this->modalHasMore = count($records) > ($this->recordPage * $this->recordPageSize);
    }

    public function getFilteredCategories()
    {
        $term = strtolower($this->searchTerm);
        return collect($this->categories)
            ->filter(function ($cat) use ($term) {
                $nameMatch = strpos(strtolower($cat['name'] ?? ''), $term) !== false;
                $keyMatch = strpos(strtolower($cat['key'] ?? ''), $term) !== false;
                return $nameMatch || $keyMatch;
            })
            ->values()
            ->toArray();
    }

    public function toggleAccordion($categoryKey)
    {
        if ($this->expandedCategoryKey === $categoryKey) {
            $this->expandedCategoryKey = null;
            $this->accordionSearchTerm = '';
        } else {
            $this->expandedCategoryKey = $categoryKey;
            $this->accordionSearchTerm = '';
        }
    }

    public function getAccordionRecords($categoryKey)
    {
        $user = Auth::user();
        if (!$user || !$this->selectedUploadId) {
            return [];
        }

        $upload = LogAnalytic::where('id', $this->selectedUploadId)
            ->where('user_id', $user->id)
            ->first();

        if (!$upload) {
            return [];
        }

        $items = $upload->getCategory($categoryKey) ?? [];
        $term = strtolower($this->accordionSearchTerm);

        if ($term === '') {
            return $items;
        }

        return array_values(array_filter($items, function ($record) use ($term) {
            $searchable = json_encode($record);
            return strpos(strtolower($searchable), $term) !== false;
        }));
    }

    public function render()
    {
        return view('livewire.admin.devices.logs.log-report-manager');
    }

    public function updatedAnalyticsYear()
    {
        $this->analyticsMonth = '';
        $this->resetAnalyticsState();
    }

    public function updatedAnalyticsMonth()
    {
        $this->resetAnalyticsState();
    }

    public function updatedCompareFileA()
    {
        if ($this->compareFileA && $this->compareFileB) {
            $this->compareFiles();
        }
    }

    public function updatedCompareFileB()
    {
        if ($this->compareFileA && $this->compareFileB) {
            $this->compareFiles();
        }
    }

    protected function resetAnalyticsState()
    {
        $this->consolidatedData = [];
        $this->comparisonResults = [];
        $this->selectedCategoryForTop = null;
        $this->compareFileA = null;
        $this->compareFileB = null;
        $this->syncSelectedFileIds();
    }

    protected function syncSelectedFileIds()
    {
        $this->selectedFileIds = array_column($this->getFilteredUploadsForAnalytics(), 'id');
    }

    public function getFilteredUploadsForAnalytics()
    {
        return collect($this->uploads)
            ->filter(function ($upload) {
                if (empty($upload['report_date']) || $upload['report_date'] === 'N/A') {
                    return false;
                }

                try {
                    $date = \Carbon\Carbon::parse($upload['report_date']);
                } catch (\Exception $e) {
                    return false;
                }

                if ($this->analyticsYear !== '' && (int) $date->year !== (int) $this->analyticsYear) {
                    return false;
                }

                if ($this->analyticsMonth !== '' && (int) $date->month !== (int) $this->analyticsMonth) {
                    return false;
                }

                return true;
            })
            ->values()
            ->toArray();
    }

    public function getAvailableAnalyticsYears()
    {
        return collect($this->uploads)
            ->pluck('report_date')
            ->filter(fn($d) => !empty($d) && $d !== 'N/A')
            ->map(function ($d) {
                try {
                    return (int) \Carbon\Carbon::parse($d)->year;
                } catch (\Exception $e) {
                    return null;
                }
            })
            ->filter()
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();
    }

    public function getAvailableAnalyticsMonths()
    {
        $months = collect($this->uploads)
            ->filter(function ($upload) {
                if (empty($upload['report_date']) || $upload['report_date'] === 'N/A') {
                    return false;
                }

                if ($this->analyticsYear === '') {
                    return true;
                }

                try {
                    return (int) \Carbon\Carbon::parse($upload['report_date'])->year === (int) $this->analyticsYear;
                } catch (\Exception $e) {
                    return false;
                }
            })
            ->pluck('report_date')
            ->map(function ($d) {
                try {
                    return (int) \Carbon\Carbon::parse($d)->month;
                } catch (\Exception $e) {
                    return null;
                }
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return $months->map(function ($month) {
            return [
                'value' => $month,
                'label' => \Carbon\Carbon::createFromDate(null, $month, 1)->translatedFormat('F'),
            ];
        })->toArray();
    }

    public function getUniqueCategoriesForTop()
    {
        if (empty($this->consolidatedData)) {
            $this->consolidateData();
        }

        $unique = [];
        foreach (array_keys($this->consolidatedData) as $categoryKey) {
            $displayName = $this->formatCategoryName($categoryKey);
            $fingerprint = $this->categoryFingerprint($displayName);

            if ($fingerprint === '') {
                continue;
            }

            if (!isset($unique[$fingerprint])) {
                $unique[$fingerprint] = [
                    'key' => $categoryKey,
                    'name' => $displayName,
                ];
            }
        }

        uasort($unique, fn($a, $b) => strcasecmp($a['name'], $b['name']));

        return array_values($unique);
    }

    public function getChartCategoriesForSelectedFile()
    {
        $unique = [];

        foreach ($this->categories as $category) {
            $categoryKey = $category['key'] ?? null;
            $categoryName = $category['name'] ?? null;
            if (!$categoryKey || !$categoryName) {
                continue;
            }

            $fingerprint = $this->categoryFingerprint($categoryName);
            if ($fingerprint === '') {
                continue;
            }

            if (!isset($unique[$fingerprint])) {
                $unique[$fingerprint] = [
                    'key' => $categoryKey,
                    'name' => $categoryName,
                ];
            }
        }

        uasort($unique, fn($a, $b) => strcasecmp($a['name'], $b['name']));

        return array_values($unique);
    }

    protected function analyticsScopedUploadsQuery($user)
    {
        $query = LogAnalytic::where('user_id', $user->id);

        if ($this->analyticsYear !== '') {
            $query->whereYear('report_date', (int) $this->analyticsYear);
        }

        if ($this->analyticsMonth !== '') {
            $query->whereMonth('report_date', (int) $this->analyticsMonth);
        }

        return $query;
    }

    public function switchAnalyticsMode($mode)
    {
        $this->analyticsMode = $mode;
        $this->consolidatedData = [];
        $this->comparisonResults = [];
        $this->syncSelectedFileIds();
    }

    public function consolidateData()
    {
        $user = Auth::user();
        if (!$user || empty($this->selectedFileIds)) {
            return [];
        }

        $uploads = $this->analyticsScopedUploadsQuery($user)
            ->whereIn('id', $this->selectedFileIds)
            ->get();

        $consolidated = [];

        foreach ($uploads as $upload) {
            $data = $upload->data ?? [];
            foreach ($data as $categoryKey => $items) {
                $normalizedCategoryKey = $this->normalizeCategoryKey($categoryKey);
                if ($normalizedCategoryKey === '') {
                    continue;
                }

                if (!isset($consolidated[$normalizedCategoryKey])) {
                    $consolidated[$normalizedCategoryKey] = [];
                }

                foreach ($items as $item) {
                    if (!is_array($item)) continue;

                    $label = $item['label'] ?? $item['name'] ?? null;
                    $value = floatval($item['value'] ?? $item['count'] ?? 0);

                    if (!$label) continue;

                    $normalizedLabelKey = $this->normalizeLabelKey($label);
                    if ($normalizedLabelKey === '') {
                        continue;
                    }

                    if (!isset($consolidated[$normalizedCategoryKey][$normalizedLabelKey])) {
                        $consolidated[$normalizedCategoryKey][$normalizedLabelKey] = [
                            'label' => $label,
                            'value' => 0,
                            'sources' => []
                        ];
                    }

                    $consolidated[$normalizedCategoryKey][$normalizedLabelKey]['value'] += $value;
                    $consolidated[$normalizedCategoryKey][$normalizedLabelKey]['sources'][$upload->id] = $upload->filename;
                }
            }
        }

        foreach ($consolidated as $catKey => $items) {
            $items = array_values(array_map(function ($item) {
                $item['sources'] = array_values($item['sources']);
                return $item;
            }, $items));

            usort($items, fn($a, $b) => $b['value'] <=> $a['value']);
            $consolidated[$catKey] = $items;
        }

        $this->consolidatedData = $consolidated;
        return $consolidated;
    }

    public function getAnalyticsChartData($limit = 8)
    {
        if (empty($this->consolidatedData)) {
            $this->consolidateData();
        }

        $totalsByCategory = [];
        foreach ($this->consolidatedData as $categoryKey => $items) {
            $totalsByCategory[$categoryKey] = collect($items)->sum(fn($item) => (float) ($item['value'] ?? 0));
        }

        arsort($totalsByCategory);
        $top = array_slice($totalsByCategory, 0, (int) $limit, true);

        return [
            'labels' => array_map(fn($key) => $this->formatCategoryName($key), array_keys($top)),
            'values' => array_values($top),
        ];
    }

    public function getSelectedCategoryChartData()
    {
        if (!$this->selectedCategoryForChart || empty($this->categories)) {
            return [
                'labels' => [],
                'values' => [],
                'categoryName' => null,
            ];
        }

        $selectedCategory = collect($this->categories)
            ->firstWhere('key', $this->selectedCategoryForChart);

        if (!$selectedCategory) {
            return [
                'labels' => [],
                'values' => [],
                'categoryName' => null,
            ];
        }

        $aggregated = [];
        foreach (($selectedCategory['items'] ?? []) as $item) {
            if (!is_array($item)) {
                continue;
            }

            $label = $item['label'] ?? $item['name'] ?? null;
            if (!$label) {
                continue;
            }

            $value = (float) ($item['value'] ?? $item['count'] ?? 0);
            $normalizedLabelKey = $this->normalizeLabelKey($label);
            if ($normalizedLabelKey === '') {
                continue;
            }

            if (!isset($aggregated[$normalizedLabelKey])) {
                $aggregated[$normalizedLabelKey] = [
                    'label' => $label,
                    'value' => 0,
                ];
            }

            $aggregated[$normalizedLabelKey]['value'] += $value;
        }

        $rows = array_values($aggregated);
        usort($rows, fn($a, $b) => ($b['value'] ?? 0) <=> ($a['value'] ?? 0));

        return [
            'labels' => array_map(fn($row) => $row['label'], $rows),
            'values' => array_map(fn($row) => (float) ($row['value'] ?? 0), $rows),
            'categoryName' => $selectedCategory['name'] ?? $this->formatCategoryName($this->selectedCategoryForChart),
        ];
    }

    public function getTopByCategory($categoryKey, $limit = null)
    {
        $limit = $limit ?? $this->topLimit;

        if (empty($this->consolidatedData)) {
            $this->consolidateData();
        }

        $categoryData = $this->consolidatedData[$categoryKey] ?? [];
        return array_slice($categoryData, 0, $limit);
    }

    public function getGlobalTop($limit = null)
    {
        $limit = $limit ?? $this->topLimit;

        if (empty($this->consolidatedData)) {
            $this->consolidateData();
        }

        $allItems = [];
        foreach ($this->consolidatedData as $categoryKey => $items) {
            foreach ($items as $item) {
                $allItems[] = array_merge($item, ['category' => $categoryKey]);
            }
        }

        usort($allItems, fn($a, $b) => $b['value'] <=> $a['value']);
        return array_slice($allItems, 0, $limit);
    }

    public function compareFiles()
    {
        $user = Auth::user();
        if (!$user || !$this->compareFileA || !$this->compareFileB) {
            return;
        }

        $fileA = LogAnalytic::where('id', $this->compareFileA)
            ->where('user_id', $user->id)
            ->first();

        $fileB = LogAnalytic::where('id', $this->compareFileB)
            ->where('user_id', $user->id)
            ->first();

        if (!$fileA || !$fileB) {
            return;
        }

        $dataA = $fileA->data ?? [];
        $dataB = $fileB->data ?? [];

        $results = [];

        $allCategories = array_unique(array_merge(array_keys($dataA), array_keys($dataB)));

        foreach ($allCategories as $categoryKey) {
            $itemsA = $dataA[$categoryKey] ?? [];
            $itemsB = $dataB[$categoryKey] ?? [];

            $indexA = [];
            foreach ($itemsA as $item) {
                if (!is_array($item)) continue;
                $label = $item['label'] ?? $item['name'] ?? null;
                if ($label) {
                    $indexA[$label] = floatval($item['value'] ?? $item['count'] ?? 0);
                }
            }

            $indexB = [];
            foreach ($itemsB as $item) {
                if (!is_array($item)) continue;
                $label = $item['label'] ?? $item['name'] ?? null;
                if ($label) {
                    $indexB[$label] = floatval($item['value'] ?? $item['count'] ?? 0);
                }
            }

            $categoryResults = [
                'new' => [],
                'removed' => [],
                'changed' => [],
                'unchanged' => []
            ];

            foreach ($indexB as $label => $valueB) {
                if (!isset($indexA[$label])) {
                    $categoryResults['new'][] = ['label' => $label, 'value' => $valueB];
                }
            }

            foreach ($indexA as $label => $valueA) {
                if (!isset($indexB[$label])) {
                    $categoryResults['removed'][] = ['label' => $label, 'value' => $valueA];
                }
            }

            foreach ($indexA as $label => $valueA) {
                if (isset($indexB[$label])) {
                    $valueB = $indexB[$label];
                    $diff = $valueB - $valueA;
                    $percentChange = $valueA > 0 ? (($diff / $valueA) * 100) : 0;

                    if (abs($diff) > 0.01) {
                        $categoryResults['changed'][] = [
                            'label' => $label,
                            'valueA' => $valueA,
                            'valueB' => $valueB,
                            'diff' => $diff,
                            'percentChange' => $percentChange
                        ];
                    } else {
                        $categoryResults['unchanged'][] = [
                            'label' => $label,
                            'value' => $valueA
                        ];
                    }
                }
            }

            usort($categoryResults['changed'], fn($a, $b) => abs($b['diff']) <=> abs($a['diff']));
            usort($categoryResults['new'], fn($a, $b) => ($b['value'] ?? 0) <=> ($a['value'] ?? 0));
            usort($categoryResults['removed'], fn($a, $b) => ($b['value'] ?? 0) <=> ($a['value'] ?? 0));

            $results[$categoryKey] = $categoryResults;
        }

        $this->comparisonResults = [
            'fileA' => ['id' => $fileA->id, 'filename' => $fileA->filename, 'date' => $fileA->report_date],
            'fileB' => ['id' => $fileB->id, 'filename' => $fileB->filename, 'date' => $fileB->report_date],
            'categories' => $results
        ];
    }
}
