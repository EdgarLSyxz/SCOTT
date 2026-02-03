<?php

namespace App\Livewire\Admin\Devices\Packages;

use App\Models\Package;
use Illuminate\Support\Facades\Auth;
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
    public $allCustomerIds = [];
    public $filteredCustomerIds = [];
    public $modalSearchTerm = '';

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

        $models = Package::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'user_id', 'filename', 'data', 'created_at']);

        $this->uploads = $models->map(function ($m) {
            $attrs = $m->getAttributes();
            return [
                'id' => $m->id,
                'user_id' => $m->user_id,
                'filename' => $m->filename,
                'data' => $m->data,
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

        $upload = Package::where('id', $this->selectedUploadId)
            ->where('user_id', $user->id)
            ->first();

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
                    $packages[] = [
                        'id' => $item['id'] ?? $item['service_id'] ?? (string)$idx,
                        'name' => $item['name'] ?? $item['title'] ?? '',
                        'customers' => $count,
                        'customers_list' => is_array($ids) ? $ids : [],
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
            $this->loadUploads();
            $this->selectedPackage = null;
            $this->packages = [];
            $this->totalPackages = 0;
            $this->totalCustomers = 0;
            $this->dispatch('package-upload-deleted', [
                'message' => __('Upload removed successfully'),
            ]);
        }
    }

    public function openModal($packageId)
    {
        $pkg = collect($this->packages)->firstWhere('id', $packageId);
        if ($pkg) {
            $this->selectedPackage = $pkg;
            $this->allCustomerIds = $pkg['customers_list'] ?? [];
            $this->filteredCustomerIds = $this->allCustomerIds;
            $this->modalSearchTerm = '';
            $this->modalOpen = true;
        }
    }

    public function closeModal()
    {
        $this->modalOpen = false;
        $this->selectedPackage = null;
        $this->allCustomerIds = [];
        $this->filteredCustomerIds = [];
        $this->modalSearchTerm = '';
    }

    public function filterModalSearch($term)
    {
        $this->modalSearchTerm = $term;
        $term = strtolower($term);
        $this->filteredCustomerIds = collect($this->allCustomerIds)
            ->filter(fn($id) => strpos(strtolower($id), $term) !== false)
            ->values()
            ->toArray();
    }

    public function updatedModalSearchTerm($value)
    {
        $this->filterModalSearch($value ?? '');
    }

    public function getFilteredPackages()
    {
        $term = strtolower($this->searchTerm);
        return collect($this->packages)
            ->filter(function ($pkg) use ($term) {
                return strpos(strtolower($pkg['name']), $term) !== false ||
                       strpos(strtolower((string)$pkg['id']), $term) !== false ||
                       collect($pkg['customers_list'])->contains(fn($id) => strpos(strtolower($id), $term) !== false);
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
