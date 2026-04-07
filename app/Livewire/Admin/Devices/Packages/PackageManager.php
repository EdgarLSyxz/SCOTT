<?php

namespace App\Livewire\Admin\Devices\Packages;

use App\Models\Package;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\Attributes\On;

class PackageManager extends Component
{
    public $uploads = [];
    public $selectedUploadId = null;
    public $packages = [];
    public $totalPackages = 0;
    public $totalCustomers = 0;
    public $searchTerm = '';
    public $selectedPackage = null;
    public $modalOpen = false;
    public $allCustomerIdsCount = 0;
    public $filteredCustomerIds = [];
    public $currentPackageId = null;
    public $currentUploadId = null;
    public $customerPage = 1;
    public $customerPageSize = 200;
    public $modalHasMore = false;
    public $modalSearchTerm = '';
    protected $allowedIds = [1, 2, 3, 5, 7, 8];

    public function mount()
    {
        $this->loadUploads();
    }

    #[On('refresh-uploads')]
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

        $query = Package::query();
        if (! in_array($user->id, $this->allowedIds)) {
            $query->where('user_id', $user->id);
        }

        $models = $query->orderBy('created_at', 'desc')
            ->get(['id', 'user_id', 'filename', 'data', 'created_at']);

        $this->uploads = $models->map(function ($m) {
            $attrs = $m->getAttributes();
            return [
                'id' => $m->id,
                'user_id' => $m->user_id,
                'filename' => $m->filename,
                'created_at' => $m->created_at ? $m->created_at->format('Y-m-d H:i:s') : ($attrs['created_at'] ?? null),
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

        $query = Package::where('id', $this->selectedUploadId);
        if (! in_array($user->id, $this->allowedIds)) {
            $query->where('user_id', $user->id);
        }
        $upload = $query->first();

        if ($upload) {
            $this->normalizePackages($upload->data);
        }
    }

    public function normalizePackages($data)
    {
        $raw = is_array($data) ? $data : [];
        $packages = [];

        if (is_array($raw)) {
            foreach ($raw as $idx => $item) {
                if (is_array($item)) {
                    $ids = $item['customers'] ?? $item['customers_list'] ?? $item['customer_ids'] ?? [];
                    $count = $item['customers_count'] ?? (is_array($ids) ? count($ids) : ($item['customers'] ?? 0));
                    $preview = [];
                    if (is_array($ids)) {
                        $preview = array_slice($ids, 0, 5);
                    }
                    $packages[] = [
                        'id' => $item['id'] ?? $item['service_id'] ?? (string)$idx,
                        'data_key' => (string)$idx,
                        'name' => $item['name'] ?? $item['title'] ?? '',
                        'customers' => $count,
                        'customers_preview' => $preview,
                    ];
                }
            }
        } elseif (is_object($raw)) {
            foreach ((array)$raw as $key => $item) {
                if (is_array($item)) {
                    $ids = $item['customers'] ?? $item['customers_list'] ?? $item['customer_ids'] ?? [];
                    $count = $item['customers_count'] ?? (is_array($ids) ? count($ids) : ($item['customers'] ?? 0));
                    $packages[] = [
                        'id' => $item['id'] ?? $item['service_id'] ?? (string)$key,
                        'data_key' => (string)$key,
                        'name' => $item['name'] ?? $item['title'] ?? '',
                        'customers' => $count,
                        'customers_list' => is_array($ids) ? $ids : [],
                    ];
                }
            }
        }

        $this->packages = collect($packages)
            ->sortBy(function ($p) {
                return is_numeric($p['id']) ? (int) $p['id'] : $p['id'];
            })
            ->values()
            ->toArray();

        $this->totalPackages = count($packages);
        $this->totalCustomers = array_reduce($packages, function ($carry, $p) {
            $count = 0;
            if (isset($p['customers']) && is_numeric($p['customers'])) {
                $count = (int)$p['customers'];
            } elseif (!empty($p['customers_list']) && is_array($p['customers_list'])) {
                $count = count($p['customers_list']);
            }
            return $carry + $count;
        }, 0);
    }

    public function deleteUpload()
    {
        $user = Auth::user();
        if (!$user || !$this->selectedUploadId) {
            return;
        }

        $upload = Package::where('id', $this->selectedUploadId)
            ->where('user_id', $user->id)
            ->first();

        if ($upload) {
            $upload->delete();
            $this->selectedPackage = null;
            $this->loadUploads();
            $this->dispatch('package-upload-deleted', [
                'message' => __('Upload removed successfully'),
            ]);
        }
    }

    public function openModal($dataKey, $packageId = null)
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $uploadId = $this->selectedUploadId;
        if (! $uploadId) {
            return;
        }

        $query = Package::where('id', $uploadId);
        if (! in_array($user->id, $this->allowedIds)) {
            $query->where('user_id', $user->id);
        }
        $upload = $query->first();

        if (! $upload) {
            return;
        }

        $data = $upload->data;
        $item = null;
        if (is_array($data) && array_key_exists((string)$dataKey, $data)) {
            $item = $data[(string)$dataKey];
        }

        if (! $item) {
            $item = $this->findPackageItem($data, $packageId ?? $dataKey);
        }
        if (! $item) {
            return;
        }

        $ids = $item['customers'] ?? $item['customers_list'] ?? $item['customer_ids'] ?? [];
        if (! is_array($ids)) {
            $ids = [];
        }

        $cacheKey = 'pkg_customers_' . $uploadId . '_' . ($packageId ?? $dataKey) . '_' . $user->id;
        Cache::put($cacheKey, $ids, now()->addMinutes(10));

        $this->currentPackageId = $packageId ?? $dataKey;
        $this->currentUploadId = $uploadId;
        $this->selectedPackage = [
            'id' => $item['id'] ?? $item['service_id'] ?? ($packageId ?? $dataKey),
            'name' => $item['name'] ?? $item['title'] ?? '',
            'customers' => is_array($ids) ? count($ids) : 0,
        ];
        $this->allCustomerIdsCount = count($ids);
        $this->customerPage = 1;
        $this->modalSearchTerm = '';
        $this->modalHasMore = $this->allCustomerIdsCount > $this->customerPageSize;
        $this->filteredCustomerIds = array_slice($ids, 0, $this->customerPageSize);
        $this->modalOpen = true;
    }

    public function closeModal()
    {
        if ($this->currentPackageId && $this->currentUploadId) {
            $user = Auth::user();
            $cacheKey = 'pkg_customers_' . $this->currentUploadId . '_' . $this->currentPackageId . '_' . ($user?->id ?? '');
            Cache::forget($cacheKey);
        }

        $this->modalOpen = false;
        $this->selectedPackage = null;
        $this->allCustomerIdsCount = 0;
        $this->filteredCustomerIds = [];
        $this->modalSearchTerm = '';
        $this->currentPackageId = null;
        $this->currentUploadId = null;
        $this->customerPage = 1;
        $this->modalHasMore = false;
    }

    public function filterModalSearch($term)
    {
        $this->modalSearchTerm = $term;
        $term = strtolower($term);

        if (! $this->currentPackageId || ! $this->currentUploadId) {
            $this->filteredCustomerIds = [];
            return;
        }

        $user = Auth::user();
        $cacheKey = 'pkg_customers_' . $this->currentUploadId . '_' . $this->currentPackageId . '_' . $user->id;
        $ids = Cache::get($cacheKey, []);

        if ($term === '') {
            $this->customerPage = 1;
            $this->modalHasMore = count($ids) > $this->customerPageSize;
            $this->filteredCustomerIds = array_slice($ids, 0, $this->customerPageSize);
            return;
        }

        $filtered = array_values(array_filter($ids, function ($id) use ($term) {
            return strpos(strtolower((string)$id), $term) !== false;
        }));

        $this->filteredCustomerIds = $filtered;
        $this->modalHasMore = false;
    }

    public function updatedModalSearchTerm($value)
    {
        $this->filterModalSearch($value ?? '');
    }

    public function loadMoreCustomers()
    {
        if (! $this->currentPackageId || ! $this->currentUploadId) return;
        $user = Auth::user();
        $cacheKey = 'pkg_customers_' . $this->currentUploadId . '_' . $this->currentPackageId . '_' . $user->id;
        $ids = Cache::get($cacheKey, []);
        if (empty($ids)) return;

        $this->customerPage++;
        $offset = ($this->customerPage - 1) * $this->customerPageSize;
        $next = array_slice($ids, $offset, $this->customerPageSize);
        if (! empty($next)) {
            $this->filteredCustomerIds = array_merge($this->filteredCustomerIds, $next);
        }
        $this->modalHasMore = count($ids) > ($this->customerPage * $this->customerPageSize);
    }

    protected function findPackageItem($data, $packageId)
    {
        $search = function ($node) use (&$search, $packageId) {
            if (is_array($node)) {
                foreach ($node as $idx => $item) {
                    if (is_array($item) || is_object($item)) {
                        $arr = is_array($item) ? $item : (array)$item;
                        $id = $arr['id'] ?? $arr['service_id'] ?? null;
                        if ((string)$id === (string)$packageId) return $arr;
                        if ((string)$idx === (string)$packageId) return is_array($item) ? $item : (array)$item;
                        $res = $search($item);
                        if ($res !== null) return $res;
                    }
                }
            }
            return null;
        };

        return $search($data);
    }

    public function getFilteredPackages()
    {
        $term = strtolower($this->searchTerm);
        return collect($this->packages)
            ->filter(function ($pkg) use ($term) {
                $nameMatch = strpos(strtolower($pkg['name'] ?? ''), $term) !== false;
                $idMatch = strpos(strtolower((string)($pkg['id'] ?? '')), $term) !== false;
                $customers = $pkg['customers_list'] ?? $pkg['customers_preview'] ?? [];
                $customerMatch = false;
                if (! empty($customers) && is_array($customers)) {
                    foreach ($customers as $cid) {
                        if (strpos(strtolower((string)$cid), $term) !== false) {
                            $customerMatch = true;
                            break;
                        }
                    }
                }

                return $nameMatch || $idMatch || $customerMatch;
            })
            ->sortBy(function ($p) {
                return is_numeric($p['id']) ? (int) $p['id'] : $p['id'];
            })
            ->values()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.admin.devices.packages.package-manager');
    }
}
