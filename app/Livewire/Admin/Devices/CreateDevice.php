<?php

namespace App\Livewire\Admin\Devices;

use App\Enums\DeviceDRM;
use App\Enums\DeviceProtocol;
use App\Models\Device;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateDevice extends Component
{
    use WithFileUploads;

    public $name;

    public $image_url;

    public $status = '';

    public $protocol = '';

    public $drm = '';

    public $area = 'OTT';

    public $store_url;

    public function boot()
    {
        $this->withValidator(function ($validator) {
            if ($validator->fails()) {
                $errorMessages = '<ul style="list-style-type: disc; padding-left: 20px; margin-left: 0; padding-right: 0;">';

                foreach ($validator->errors()->all() as $error) {
                    $errorMessages .= "<li style='list-style-position: inside;'>$error</li>";
                }

                $errorMessages .= '</ul>';

                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => __('Error'),
                    'html' => '<b>'.__('Your registration for a new device contains the following errors:').'</b><br><br>'.$errorMessages,
                ]);
            }
        });
    }

    public function store()
    {
        $this->authorize('create', Device::class);

        $this->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:1,0',
            'area' => 'required|string|in:OTT,DTH,DTH/OTT',
            'protocol' => ['nullable', 'string', Rule::in(array_map(fn ($c) => $c->value, DeviceProtocol::cases()))],
            'drm' => ['nullable', 'string', Rule::in(array_map(fn ($c) => $c->value, DeviceDRM::cases()))],
            'store_url' => 'nullable|url|max:2048',
            'image_url' => 'nullable|image|max:2048',
        ], [], [
            'name' => __('device name'),
            'image_url' => __('device image'),
            'area' => __('device area'),
            'store_url' => __('device store url'),
            'protocol' => __('device protocol'),
        ]);

        $imagePath = null;
        if ($this->image_url) {
            $imageName = time().'_'.$this->image_url->getClientOriginalName();
            $this->image_url->storeAs('devices', $imageName, 'public');
            $imagePath = 'devices/'.$imageName;
        }

        $device = Device::create([
            'name' => $this->name,
            'status' => (bool) $this->status,
            'area' => $this->area ?: 'OTT',
            'image_url' => $imagePath,
            'protocol' => $this->protocol ?: null,
            'drm' => $this->drm ?: null,
            'store_url' => $this->store_url ?: null,
        ]);

        session()->flash('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => __('New device created successfully.'),
        ]);

        return redirect()->route('devices.show', $device);
    }

    public function render()
    {
        return view('livewire.admin.devices.create-device');
    }
}
