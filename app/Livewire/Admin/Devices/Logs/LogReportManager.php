<?php

namespace App\Livewire\Admin\Devices\Logs;

use App\Models\LogAnalytic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
    public $auditApiUrl;
    public $auditApiToken;

    public function mount()
    {
        $services = config('services', []);
        $this->auditApiUrl = data_get($services, 'audit_api.url')
            ?: data_get($services, 'python_log_analytics_api.url')
            ?: data_get($services, 'log_parser.url');
        $this->auditApiToken = data_get($services, 'audit_api.token');
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

            $reportDateFormatted = 'N/A';
            try {
                $rd = $m->report_date;
                if ($rd instanceof \DateTimeInterface) {
                    $reportDateFormatted = $rd->format('Y-m-d');
                } elseif (!empty($rd)) {
                    try {
                        $reportDateFormatted = \Carbon\Carbon::parse($rd)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $formats = [
                            'd/m/Y H:i:s', 'd/m/Y', 'd-m-Y H:i:s', 'd-m-Y', 'd.m.Y H:i:s', 'd.m.Y', 'Y-m-d H:i:s', 'Y-m-d'
                        ];
                        foreach ($formats as $fmt) {
                            try {
                                $dt = \Carbon\Carbon::createFromFormat($fmt, $rd);
                                if ($dt !== false) {
                                    $reportDateFormatted = $dt->format('Y-m-d');
                                    break;
                                }
                            } catch (\Exception $_) {
                            }
                        }

                        if ($reportDateFormatted === 'N/A' && is_string($rd)) {
                            $reportDateFormatted = $rd;
                        }
                    }
                }
            } catch (\Exception $_) {
                $reportDateFormatted = 'N/A';
            }

            $createdAtDisplay = null;
            try {
                if ($m->created_at instanceof \DateTimeInterface) {
                    $createdAtDisplay = $m->created_at->format('d/m/Y H:i');
                } elseif (!empty($attrs['created_at'])) {
                    try {
                        $createdAtDisplay = \Carbon\Carbon::parse($attrs['created_at'])->format('d/m/Y H:i');
                    } catch (\Exception $_) {
                        $createdAtDisplay = $attrs['created_at'];
                    }
                }
            } catch (\Exception $_) {
                $createdAtDisplay = $attrs['created_at'] ?? null;
            }

            return [
                'id' => $m->id,
                'user_id' => $m->user_id,
                'filename' => $m->filename,
                'report_date' => $reportDateFormatted,
                'created_at' => $m->created_at ? $m->created_at->format('d/m/Y H:i:s') : ($attrs['created_at'] ?? null),
                'created_at_raw' => $attrs['created_at'] ?? null,
                'created_at_display' => $createdAtDisplay,
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
                $categoryItems = [];
                if (is_array($items)) {
                    foreach ($items as $item) {
                        if (is_array($item)) {
                            $categoryItems[] = $item;
                        }
                    }
                }

                $aggForCount = $this->aggregateRecords($categoryItems);
                $categories[] = [
                    'key' => (string) $categoryKey,
                    'name' => $this->formatCategoryName($categoryKey),
                    'items' => $categoryItems,
                    'count' => count($categoryItems),
                    'unique_count' => count($categoryItems) > 0 ? count($aggForCount) : 0,
                ];
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

        $grouped = $this->getChartCategoriesForSelectedFile();
        if (!empty($grouped)) {
            $groupKeys = array_column($grouped, 'key');
            if (!in_array($this->selectedCategoryForChart, $groupKeys, true)) {
                foreach ($grouped as $g) {
                    if (in_array($this->selectedCategoryForChart, $g['sourceKeys'] ?? [], true)) {
                        $this->selectedCategoryForChart = $g['key'];
                        break;
                    }
                }

                if (!in_array($this->selectedCategoryForChart, $groupKeys, true)) {
                    $this->selectedCategoryForChart = $grouped[0]['key'] ?? $this->selectedCategoryForChart;
                }
            }
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
        $s = trim((string) $label);

        $isUrl = false;
        if (filter_var($s, FILTER_VALIDATE_URL)) {
            $isUrl = true;
            $url = $s;
        } else {
            if (strpos($s, '://') !== false || (strpos($s, '.') !== false && strpos($s, '/') !== false)) {
                $isUrl = true;
                $url = $s;
            }
        }

        if ($isUrl) {
            if (!preg_match('/^https?:\/\//i', $url)) {
                $url = 'https://' . ltrim($url, '/');
            }

            $parts = parse_url($url);
            if ($parts && isset($parts['host'])) {
                $host = strtolower($parts['host']);
                $path = $parts['path'] ?? '';
                $path = rtrim($path, '/');
                $path = rawurldecode($path);
                $canon = $host . ($path !== '' ? '/' . ltrim($path, '/') : '');
                return Str::of($canon)->ascii()->squish()->lower()->value();
            }
        }

        return Str::of($s)
            ->ascii()
            ->squish()
            ->lower()
            ->value();
    }

    protected function safeParseDateToCarbon($dateValue)
    {
        if (empty($dateValue) || $dateValue === 'N/A') {
            return null;
        }

        if ($dateValue instanceof \DateTimeInterface) {
            return $dateValue instanceof \Carbon\Carbon ? $dateValue : \Carbon\Carbon::instance($dateValue);
        }

        $dateStr = (string) $dateValue;

        try {
            return \Carbon\Carbon::parse($dateStr);
        } catch (\Exception $_) {
        }

        $formats = [
            'd/m/Y H:i:s', 'd/m/Y H:i', 'd/m/Y',
            'd-m-Y H:i:s', 'd-m-Y H:i', 'd-m-Y',
            'd.m.Y H:i:s', 'd.m.Y H:i', 'd.m.Y',
            'Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d',
            'm/d/Y H:i:s', 'm/d/Y',
            'Y/m/d H:i:s', 'Y/m/d'
        ];

        foreach ($formats as $fmt) {
            try {
                $dt = \Carbon\Carbon::createFromFormat($fmt, $dateStr);
                if ($dt !== false) {
                    return $dt;
                }
            } catch (\Exception $_) {
            }
        }

        return null;
    }

    protected function aggregateRecords(array $items): array
    {
        $agg = [];

        foreach ($items as $item) {
            if (!is_array($item)) continue;
            $label = $item['label'] ?? $item['name'] ?? null;
            if (!$label) continue;
            $value = (float) ($item['value'] ?? $item['count'] ?? 0);

            $key = $this->normalizeLabelKey($label);
            if ($key === '') continue;

            if (!isset($agg[$key])) {
                $agg[$key] = [
                    'label' => $label,
                    'value' => 0.0,
                    'occurrences' => 0,
                    'examples' => [],
                ];
            }

            $agg[$key]['value'] += $value;
            $agg[$key]['occurrences'] += 1;
            if (count($agg[$key]['examples']) < 3) {
                $agg[$key]['examples'][] = $label;
            }
        }

        $rows = array_values($agg);
        usort($rows, fn($a, $b) => ($b['value'] ?? 0) <=> ($a['value'] ?? 0));
        foreach ($rows as $i => &$r) {
            $r['rank'] = $i + 1;
        }
        unset($r);
        return $rows;
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

            $this->selectedUploadId = null;
            $this->categories = [];
            $this->totalRecords = 0;
            $this->currentReportDate = null;
            $this->selectedCategory = null;

            $this->loadUploads();

            $this->dispatch('log-upload-deleted', [
                'message' => __('Log report deleted successfully.'),
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

        $agg = $this->aggregateRecords($items);
        $cacheAggKey = $cacheKey . '_agg';
        Cache::put($cacheAggKey, $agg, now()->addMinutes(10));

        $this->currentCategoryKey = $categoryKey;
        $this->currentUploadId = $uploadId;
        $this->selectedCategory = [
            'key' => $categoryKey,
            'name' => $this->formatCategoryName($categoryKey),
            'records' => count($agg),
        ];
        $this->allRecordsCount = count($agg);
        $this->recordPage = 1;
        $this->modalSearchTerm = '';
        $this->modalHasMore = $this->allRecordsCount > $this->recordPageSize;
        $this->filteredRecords = array_slice($agg, 0, $this->recordPageSize);
        $this->modalOpen = true;
    }

    public function closeModal()
    {
        if ($this->currentCategoryKey && $this->currentUploadId) {
            $user = Auth::user();
            $cacheKey = 'log_records_' . $this->currentUploadId . '_' . $this->currentCategoryKey . '_' . ($user?->id ?? '');
            Cache::forget($cacheKey);
            Cache::forget($cacheKey . '_agg');
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
        $cacheAggKey = $cacheKey . '_agg';
        $records = Cache::get($cacheAggKey, []);

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
        $records = Cache::get($cacheKey . '_agg', []);
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
            ->sortBy(fn($cat) => $cat['name'] ?? '', SORT_NATURAL | SORT_FLAG_CASE)
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
        $aggregated = $this->aggregateRecords($items);

        $term = strtolower($this->accordionSearchTerm);
        if ($term === '') {
            return $aggregated;
        }

        return array_values(array_filter($aggregated, function ($record) use ($term) {
            $searchable = json_encode($record);
            return strpos(strtolower($searchable), $term) !== false;
        }));
    }

    public function getAccordionRawRecords($categoryKey)
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
            if (!is_array($record)) {
                return strpos(strtolower((string) $record), $term) !== false;
            }
            $searchable = json_encode($record, JSON_UNESCAPED_UNICODE);
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

                $date = $this->safeParseDateToCarbon($upload['report_date']);
                if (!$date) {
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
                $date = $this->safeParseDateToCarbon($d);
                return $date ? (int) $date->year : null;
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

                $date = $this->safeParseDateToCarbon($upload['report_date']);
                if (!$date) {
                    return false;
                }

                return (int) $date->year === (int) $this->analyticsYear;
            })
            ->pluck('report_date')
            ->map(function ($d) {
                $date = $this->safeParseDateToCarbon($d);
                return $date ? (int) $date->month : null;
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
            $count = (int) ($category['unique_count'] ?? $category['count'] ?? 0);
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
                    'count' => $count,
                    'sourceKeys' => [$categoryKey],
                ];
            } else {
                $unique[$fingerprint]['count'] += $count;
                $unique[$fingerprint]['sourceKeys'][] = $categoryKey;
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

        $grouped = $this->getChartCategoriesForSelectedFile();
        $groupEntry = collect($grouped)->firstWhere('key', $this->selectedCategoryForChart);
        $sourceKeys = $groupEntry['sourceKeys'] ?? [$this->selectedCategoryForChart];
        $categoryName = $groupEntry['name'] ?? $this->formatCategoryName($this->selectedCategoryForChart);

        $aggregated = [];
        foreach ($this->categories as $cat) {
            if (!in_array($cat['key'] ?? null, $sourceKeys, true)) continue;
            foreach (($cat['items'] ?? []) as $item) {
                if (!is_array($item)) continue;
                $label = $item['label'] ?? $item['name'] ?? null;
                if (!$label) continue;
                $value = (float) ($item['value'] ?? $item['count'] ?? 0);
                $normalizedLabelKey = $this->normalizeLabelKey($label);
                if ($normalizedLabelKey === '') continue;

                if (!isset($aggregated[$normalizedLabelKey])) {
                    $aggregated[$normalizedLabelKey] = ['label' => $label, 'value' => 0];
                }
                $aggregated[$normalizedLabelKey]['value'] += $value;
            }
        }

        $rows = array_values($aggregated);
        usort($rows, fn($a, $b) => ($b['value'] ?? 0) <=> ($a['value'] ?? 0));

        return [
            'labels' => array_map(fn($row) => $row['label'], $rows),
            'values' => array_map(fn($row) => (float) ($row['value'] ?? 0), $rows),
            'categoryName' => $categoryName,
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

    protected function buildImageManifestFromUpload($upload): array
    {
        $manifest = [];
        $data = is_array($upload->data) ? $upload->data : [];

        foreach ($data as $cat => $items) {
            if (!is_array($items)) continue;
            foreach ($items as $item) {
                if (!is_array($item)) continue;
                $serviceId = $item['service_id'] ?? $item['channel_id'] ?? $item['id'] ?? null;
                if ($serviceId) {
                    $manifest[$serviceId] = url('/storage/logos/'.$serviceId.'.png');
                }
            }
        }

        return $manifest;
    }

    protected function locateUploadTxtFile($upload): ?string
    {
        $filename = $upload->filename ?? null;
        if ($filename) {
            $candidates = [
                storage_path('app/'.$filename),
                storage_path('app/logs/'.$filename),
                storage_path('app/uploads/'.$filename),
                public_path('uploads/'.$filename),
                public_path('storage/'.$filename),
                public_path('storage/logs/'.$filename),
                public_path('storage/uploads/'.$filename),
            ];
            foreach ($candidates as $p) {
                if ($p && file_exists($p)) return $p;
            }

            foreach (['local','public'] as $disk) {
                if (Storage::disk($disk)->exists($filename)) {
                    try { return Storage::disk($disk)->path($filename); } catch (\Throwable $_) { }
                }
                $candidate = 'logs/'.$filename;
                if (Storage::disk($disk)->exists($candidate)) {
                    try { return Storage::disk($disk)->path($candidate); } catch (\Throwable $_) {}
                }
            }
        }

        $raw = $upload->raw_text ?? $upload->content ?? null;
        if (empty($raw) && is_array($upload->data)) {
            $raw = $upload->data['raw'] ?? $upload->data['txt'] ?? null;
        }
        if ($raw) {
            $tmp = tempnam(sys_get_temp_dir(), 'audit_txt_');
            file_put_contents($tmp, $raw);
            return $tmp;
        }

        return null;
    }

    protected function extractServiceIdsFromTxtUsingPython(string $localPath): array
    {
        $result = [];
        if (empty($localPath) || !file_exists($localPath) || empty($this->auditApiUrl)) {
            return $result;
        }

        try {
            $baseUrl = rtrim($this->auditApiUrl, '/');

            if (str_contains($baseUrl, '/api/audit-report')) {
                $url = preg_replace('#/pdf$#', '', $baseUrl) . '/extract-service-ids';
            } else {
                $url = $baseUrl . '/api/audit-report/extract-service-ids';
            }

            $client = Http::timeout(60);
            if ($this->auditApiToken) {
                $client = $client->withHeaders(['Authorization' => 'Bearer '.$this->auditApiToken]);
            }

            $response = $client->attach('txt_file', fopen($localPath, 'r'), basename($localPath))->post($url);
            if (! $response->successful()) {
                Log::warning('extractServiceIdsFromTxtUsingPython: non-success response', ['status' => $response->status(), 'body' => $response->body()]);
                return [];
            }

            $json = $response->json();
            if (is_array($json)) {
                if (!empty($json['service_ids']) && is_array($json['service_ids'])) {
                    $result = array_map('strval', $json['service_ids']);
                } elseif (!empty($json['ids']) && is_array($json['ids'])) {
                    $result = array_map('strval', $json['ids']);
                }
            }
        } catch (\Exception $e) {
            Log::error('extractServiceIdsFromTxtUsingPython exception: '.$e->getMessage());
        }

        return array_values(array_unique($result));
    }

    public function generatePdfForSelectedUpload()
    {
        $user = Auth::user();
        if (!$user || !$this->selectedUploadId) {
            $this->dispatch('notify', type: 'error', message: 'No hay archivo seleccionado');
            return;
        }

        if (empty($this->auditApiUrl)) {
            $this->dispatch('notify', type: 'error', message: 'No se ha configurado AUDIT_API_URL');
            return;
        }

        $upload = LogAnalytic::where('id', $this->selectedUploadId)
            ->where('user_id', $user->id)
            ->first();

        if (! $upload) {
            $this->dispatch('notify', type: 'error', message: 'Upload no encontrado');
            return;
        }

        $localPath = $this->locateUploadTxtFile($upload);
        $canAttachTxt = !empty($localPath) && file_exists($localPath);

        $serviceIds = [];
        if ($canAttachTxt) {
            $serviceIds = $this->extractServiceIdsFromTxtUsingPython($localPath);
        }

        if (empty($serviceIds)) {
            $data = is_array($upload->data) ? $upload->data : [];
            foreach ($data as $cat => $items) {
                if (!is_array($items)) continue;
                foreach ($items as $item) {
                    if (!is_array($item)) continue;
                    $sid = $item['service_id'] ?? $item['channel_id'] ?? $item['id'] ?? null;
                    if ($sid) $serviceIds[] = (string)$sid;
                }
            }
        }
        $serviceIds = array_values(array_unique($serviceIds));

        $manifest = [];
        foreach ($serviceIds as $sid) {
            $candidates = [
                'logos/'.$sid.'.png',
                'logos/'.Str::slug($sid).'.png',
                'logos/'.Str::lower($sid).'.png',
            ];
            foreach ($candidates as $candidate) {
                try {
                    if (Storage::disk('public')->exists($candidate)) {
                        $manifest[$sid] = Storage::disk('public')->url($candidate);
                        break;
                    }
                } catch (\Throwable $_) {
                }
            }
        }

        if (empty($manifest)) {
            $this->dispatch('notify', type: 'error', message: 'No se encontraron imagenes de canales para este reporte.');
            return;
        }

        $imageManifestJson = !empty($manifest) ? json_encode($manifest) : null;
        $reportDataJson = json_encode(is_array($upload->data) ? $upload->data : []);

        $tempCreated = $canAttachTxt && (strpos((string)$localPath, sys_get_temp_dir()) === 0);

        try {
            $baseUrl = rtrim($this->auditApiUrl, '/');
            $url = str_contains($baseUrl, '/api/audit-report/pdf')
                ? $baseUrl
                : $baseUrl.'/api/audit-report/pdf';

            $client = Http::timeout(120);
            if ($this->auditApiToken) {
                $client = $client->withHeaders(['Authorization' => 'Bearer '.$this->auditApiToken]);
            }

            $payload = [
                'image_url_template' => url('/storage/logos/{service_id}.png'),
                'image_manifest_json' => $imageManifestJson,
                'report_data_json' => $reportDataJson,
                'report_id' => (string) $upload->id,
                'report_filename' => (string) ($upload->filename ?? ('upload-'.$upload->id.'.txt')),
                'report_date' => (string) ($upload->report_date ?? ''),
                'output_filename' => 'reporte-auditoria-'.$upload->id.'.pdf',
            ];

            if ($canAttachTxt) {
                $response = $client->attach(
                    'txt_file',
                    fopen($localPath, 'r'),
                    basename($localPath) ?: 'report.txt'
                )->post($url, $payload);
            } else {
                $response = $client->post($url, $payload);
            }

            if (! $response->successful()) {
                $this->dispatch('notify', type: 'error', message: 'Error Python generando PDF: '.$response->status());
                return;
            }

            $contentType = strtolower((string) ($response->header('content-type') ?? ''));
            $outName = 'reports/reporte-auditoria-'.$upload->id.'-'.now()->format('Ymd_His').'.pdf';

            if (str_contains($contentType, 'application/pdf')) {
                Storage::disk('public')->put($outName, $response->body());
                $publicUrl = asset('storage/'.$outName);
                $this->dispatch('report-generated', url: $publicUrl, message: 'PDF generado correctamente por Python');
                return;
            }

            $json = $response->json();
            if (is_array($json) && !empty($json['pdf_base64'])) {
                $decoded = base64_decode((string) $json['pdf_base64'], true);
                if ($decoded !== false) {
                    Storage::disk('public')->put($outName, $decoded);
                    $publicUrl = asset('storage/'.$outName);
                    $this->dispatch('report-generated', url: $publicUrl, message: 'PDF generado correctamente por Python');
                    return;
                }
            }

            if (is_array($json) && !empty($json['pdf_url'])) {
                $this->dispatch('report-generated', url: (string) $json['pdf_url'], message: 'PDF generado correctamente por Python');
                return;
            }

            $this->dispatch('notify', type: 'error', message: 'Python respondio sin PDF valido.');
        } catch (\Exception $e) {
            Log::error('PDF generation failed: '.$e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Excepcion generando PDF: '.$e->getMessage());
        } finally {
            if ($tempCreated && file_exists($localPath)) {
                @unlink($localPath);
            }
        }
    }
}
