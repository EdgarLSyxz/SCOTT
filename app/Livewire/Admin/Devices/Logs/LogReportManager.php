<?php

namespace App\Livewire\Admin\Devices\Logs;

use App\Models\LogAnalytic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
    }

    protected function formatCategoryName($key)
    {
        return ucfirst(str_replace('_', ' ', $key));
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

    public function render()
    {
        return view('livewire.admin.devices.logs.log-report-manager');
    }
}
