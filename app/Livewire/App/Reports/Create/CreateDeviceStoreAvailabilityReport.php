<?php

namespace App\Livewire\App\Reports\Create;

use App\Models\Device;
use App\Models\DeviceStoreAvailability;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class CreateDeviceStoreAvailabilityReport extends Component
{
    private const SAMSUNG_STORE_WEB_NOTE = 'No existe un acceso a la aplicación StarTV Stream en la VERSIÓN WEB de la tienda de aplicaciones de Samsung para TVs.';

    public $reportData;

    public function mount()
    {
        $devices = Device::where('area', Report::AREA_OTT)
            ->where('name', '<>', 'Android (Mobile & TV)')
            ->orderBy('name')
            ->get();

        $this->reportData = [
            'devices' => $devices->map(function ($device) {
                return $this->initializeDevice($device);
            })->toArray(),
        ];
    }

    protected function initializeDevice(?Device $device = null): array
    {
        $isSamsungDevice = $this->isSamsungDevice($device?->name);

        return [
            'device_id' => $device?->id ?? '',
            'is_active' => false,
            'is_available_in_store' => false,
            'notes' => $isSamsungDevice ? self::SAMSUNG_STORE_WEB_NOTE : '',
        ];
    }

    public function addDevice()
    {
        $this->reportData['devices'][] = $this->initializeDevice();
    }

    public function removeDevice($index)
    {
        unset($this->reportData['devices'][$index]);
        $this->reportData['devices'] = array_values($this->reportData['devices']);
    }

    public function saveReport()
    {
        try {
            $this->validateReportData();

            if (empty($this->reportData['devices'])) {
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => __('Error'),
                    'text' => __('You must select at least one device to create a report.'),
                ]);
                return;
            }

            $deviceIds = array_column($this->reportData['devices'], 'device_id');
            $deviceCounts = array_count_values($deviceIds);
            $repeatedDevices = [];
            foreach ($deviceCounts as $deviceId => $count) {
                if ($deviceId && $count > 1) {
                    $repeatedDevices[] = Device::find($deviceId)?->name ?? $deviceId;
                }
            }

            if (!empty($repeatedDevices)) {
                $errorMessages = '<ul style="text-align: center;">';
                foreach ($repeatedDevices as $deviceName) {
                    $errorMessages .= '<li>• ' . __('The device ":device" cannot be selected more than once.', ['device' => $deviceName]) . '</li>';
                }
                $errorMessages .= '</ul>';

                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => __('Error'),
                    'html' => '<b>' . __('Your report log contains errors:') . '</b><br><br>' . $errorMessages,
                ]);
                return;
            }

            $report = Report::create([
                'title' => null,
                'type' => 'Functions',
                'category' => 'StarTV Stream Availability',
                'duration' => null,
                'reported_by' => Auth::id(),
                'area' => Auth::user()->area ?? Report::AREA_OTT,
                'reviewed_by' => null,
                'status' => 'Reported',
            ]);

            foreach ($this->reportData['devices'] as $deviceRow) {
                $device = Device::find($deviceRow['device_id']);
                $isSamsung = $this->isSamsungDevice($device?->name);
                $notes = $deviceRow['notes'] ?? null;

                if ($isSamsung) {
                    $notes = self::SAMSUNG_STORE_WEB_NOTE;
                }

                $isAvailable = $isSamsung ? false : (bool) ($deviceRow['is_available_in_store'] ?? false);

                DeviceStoreAvailability::create([
                    'report_id' => $report->id,
                    'device_id' => $deviceRow['device_id'],
                    'user_id' => Auth::id(),
                    'is_active' => (bool) ($deviceRow['is_active'] ?? false),
                    'is_available_in_store' => $isAvailable,
                    'notes' => $notes,
                ]);
            }

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => __('Well done!'),
                'text' => __('Store availability report created successfully.'),
            ]);

            $this->dispatch('reportCreated');
        } catch (ValidationException $e) {
            $errorMessages = '<ul style="text-align: center;">';
            foreach ($e->errors() as $messages) {
                foreach ($messages as $message) {
                    $errorMessages .= "<li>• {$message}</li>";
                }
            }
            $errorMessages .= '</ul>';

            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => __('Error'),
                'html' => '<b>' . __('Your report log contains errors:') . '</b><br><br>' . $errorMessages,
            ]);
        }
    }

    protected function validateReportData()
    {
        $this->validate([
            'reportData.devices' => 'required|array|min:1',
        ], [], [
            'reportData.devices' => __('devices'),
        ]);

        foreach ($this->reportData['devices'] as $index => $device) {
            $this->validate([
                "reportData.devices.{$index}.device_id" => 'required|exists:devices,id',
                "reportData.devices.{$index}.is_active" => 'nullable|boolean',
                "reportData.devices.{$index}.is_available_in_store" => 'nullable|boolean',
                "reportData.devices.{$index}.notes" => 'nullable|string|max:1000',
            ], [], [
                "reportData.devices.{$index}.device_id" => __('device'),
                "reportData.devices.{$index}.is_active" => __('device active status'),
                "reportData.devices.{$index}.is_available_in_store" => __('store availability status'),
                "reportData.devices.{$index}.notes" => __('notes'),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.app.reports.create.create-device-store-availability-report', [
            'devices' => Device::where('area', Report::AREA_OTT)
                ->where('name', '<>', 'Android (Mobile & TV)')
                ->orderBy('name')
                ->get()
                ->map(fn($device) => [
                    'id' => $device->id,
                    'name' => $device->name,
                    'image' => $device->image,
                    'store_url' => $device->store_url,
                    'status' => (bool) $device->status,
                    'protocol' => $device->protocol,
                    'drm' => $device->drm,
                    'is_samsung' => $this->isSamsungDevice($device->name),
                ]),
        ]);
    }

    private function isSamsungDevice(?string $deviceName): bool
    {
        if (! $deviceName) {
            return false;
        }

        return str_contains(strtolower($deviceName), 'samsung');
    }
}
