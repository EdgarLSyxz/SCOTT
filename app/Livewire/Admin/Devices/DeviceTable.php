<?php

namespace App\Livewire\Admin\Devices;

use App\Models\Device;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class DeviceTable extends Component
{
    use AuthorizesRequests, WithPagination;

    public $name = '';

    public $status = true;

    public $protocolFilter = null;

    public $statusFilter = null;

    public $queryString = ['protocolFilter', 'statusFilter'];

    public function toggleProtocolFilter()
    {
        $options = ['HLS', 'DASH'];

        $currentIndex = array_search($this->protocolFilter, $options, true);

        if ($currentIndex === false) {
            $this->protocolFilter = $options[0];
        } else {
            $next = $currentIndex + 1;
            $this->protocolFilter = $next >= count($options) ? null : $options[$next];
        }

        $this->resetPage();
    }

    public function toggleStatusFilter()
    {
        if ($this->statusFilter === null) {
            $this->statusFilter = true;
        } elseif ($this->statusFilter === true) {
            $this->statusFilter = false;
        } else {
            $this->statusFilter = null;
        }

        $this->resetPage();
    }

    public function render()
    {
        $this->authorize('viewAny', Device::class);

        $auth = auth()->user();

        $query = Device::query()->where('name', '!=', 'Android (Mobile & TV)');

        if (! ($auth && $auth->id === 1)) {
            $query->where('area', $auth->area ?? 'OTT');
        }

        if (! is_null($this->protocolFilter)) {
            $query->where('protocol', $this->protocolFilter);
        }

        if (! is_null($this->statusFilter)) {
            $query->where('status', $this->statusFilter ? 1 : 0);
        }

        if (is_null($this->protocolFilter) && is_null($this->statusFilter)) {
            $query->whereIn('protocol', ['HLS', 'DASH'])
                ->orderByRaw("CASE WHEN protocol = 'HLS' THEN 0 WHEN protocol = 'DASH' THEN 1 ELSE 2 END")
                ->orderBy('name', 'asc');
        } else {
            if (! is_null($this->protocolFilter)) {
                $query->orderBy('name', 'asc');
            } else {
                $query->orderBy('id', 'desc');
            }
        }

        $devices = $query->get();

        return view('livewire.admin.devices.device-table', [
            'devices' => $devices,
        ]);
    }
}
