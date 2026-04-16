<?php

namespace App\Livewire\Admin\Devices\Downloads;

use App\Models\UserGrowth;
use Livewire\Component;

class UserGrowthTracker extends Component
{
    public string $recordedAt  = '';
    public string $customers   = '';
    public string $devices     = '';

    public bool $showForm      = false;
    public ?int $editingId     = null;

    protected function rules(): array
    {
        return [
            'recordedAt' => 'required|date',
            'customers'  => 'required|integer|min:0',
            'devices'    => 'required|integer|min:0',
        ];
    }

    protected function messages(): array
    {
        return [
            'recordedAt.required' => __('The date is required.'),
            'recordedAt.date'     => __('The date must be a valid date.'),
            'customers.required'  => __('Customers count is required.'),
            'customers.integer'   => __('Customers must be a whole number.'),
            'customers.min'       => __('Customers cannot be negative.'),
            'devices.required'    => __('Devices count is required.'),
            'devices.integer'     => __('Devices must be a whole number.'),
            'devices.min'         => __('Devices cannot be negative.'),
        ];
    }

    public function openForm(?int $id = null): void
    {
        $this->resetValidation();
        $this->editingId = $id;

        if ($id) {
            $record           = UserGrowth::findOrFail($id);
            $this->recordedAt = $record->recorded_at->format('Y-m-d');
            $this->customers  = (string) $record->customers;
            $this->devices    = (string) $record->devices;
        } else {
            $this->recordedAt = date('Y-m-d');
            $this->customers  = '';
            $this->devices    = '';
        }

        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->reset(['showForm', 'editingId', 'recordedAt', 'customers', 'devices']);
        $this->resetValidation();
    }

    public function save(): void
    {
        $data = $this->validate();

        UserGrowth::updateOrCreate(
            ['id' => $this->editingId],
            [
                'recorded_at' => $data['recordedAt'],
                'customers'   => (int) $data['customers'],
                'devices'     => (int) $data['devices'],
            ]
        );

        $this->cancelForm();

        $this->dispatch('user-growth-saved');
    }

    public function delete(int $id): void
    {
        UserGrowth::findOrFail($id)->delete();
        $this->dispatch('user-growth-saved');
    }

    public function render()
    {
        $records = UserGrowth::orderBy('recorded_at')->get();

        return view('livewire.admin.devices.downloads.user-growth-tracker', [
            'records'      => $records,
            'chartLabels'  => $records->map(fn ($r) => $r->recorded_at->format('d-M'))->values(),
            'chartCustomers' => $records->pluck('customers')->values(),
            'chartDevices'   => $records->pluck('devices')->values(),
        ]);
    }
}
