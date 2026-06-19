<?php

namespace App\Observers;

use App\Models\RackEquipment;
use App\Models\RackEquipmentHistory;
use Illuminate\Support\Facades\Auth;

class RackEquipmentObserver
{
    private const TRACKED_FIELDS = [
        'equipment_name',
        'equipment_model',
        'equipment_role',
        'ip_address',
        'serial_number',
        'mac_address',
        'vendor',
        'installation_date',
        'notes',
        'color',
        'is_active',
    ];

    public function created(RackEquipment $equipment): void
    {
        RackEquipmentHistory::create([
            'rack_id' => $equipment->rack_id,
            'position' => $equipment->position,
            'rack_equipment_id' => $equipment->id,
            'equipment_name' => $equipment->equipment_name,
            'equipment_model' => $equipment->equipment_model,
            'equipment_role' => $equipment->equipment_role,
            'ip_address' => $equipment->ip_address,
            'serial_number' => $equipment->serial_number,
            'mac_address' => $equipment->mac_address,
            'vendor' => $equipment->vendor,
            'installation_date' => $equipment->installation_date,
            'notes' => $equipment->notes,
            'color' => $equipment->color,
            'change_type' => RackEquipmentHistory::TYPE_CREATED,
            'changes' => null,
            'changed_by' => Auth::id(),
            'changed_at' => now(),
        ]);
    }

    public function updated(RackEquipment $equipment): void
    {
        $changes = $this->buildDiff($equipment);

        if (empty($changes)) {
            return;
        }

        RackEquipmentHistory::create([
            'rack_id' => $equipment->rack_id,
            'position' => $equipment->position,
            'rack_equipment_id' => $equipment->id,
            'equipment_name' => $equipment->equipment_name,
            'equipment_model' => $equipment->equipment_model,
            'equipment_role' => $equipment->equipment_role,
            'ip_address' => $equipment->ip_address,
            'serial_number' => $equipment->serial_number,
            'mac_address' => $equipment->mac_address,
            'vendor' => $equipment->vendor,
            'installation_date' => $equipment->installation_date,
            'notes' => $equipment->notes,
            'color' => $equipment->color,
            'change_type' => RackEquipmentHistory::TYPE_UPDATED,
            'changes' => $changes,
            'changed_by' => Auth::id(),
            'changed_at' => now(),
        ]);
    }

    public function deleted(RackEquipment $equipment): void
    {
        RackEquipmentHistory::create([
            'rack_id' => $equipment->rack_id,
            'position' => $equipment->position,
            'rack_equipment_id' => null,
            'equipment_name' => $equipment->equipment_name,
            'equipment_model' => $equipment->equipment_model,
            'equipment_role' => $equipment->equipment_role,
            'ip_address' => $equipment->ip_address,
            'serial_number' => $equipment->serial_number,
            'mac_address' => $equipment->mac_address,
            'vendor' => $equipment->vendor,
            'installation_date' => $equipment->installation_date,
            'notes' => $equipment->notes,
            'color' => $equipment->color,
            'change_type' => RackEquipmentHistory::TYPE_DELETED,
            'changes' => null,
            'changed_by' => Auth::id(),
            'changed_at' => now(),
        ]);
    }

    private function buildDiff(RackEquipment $equipment): array
    {
        $diff = [];
        $original = $equipment->getOriginal();

        foreach (self::TRACKED_FIELDS as $field) {
            $newValue = $equipment->{$field};
            $oldValue = $original[$field] ?? null;

            if ($field === 'installation_date') {
                if ($newValue instanceof \DateTimeInterface) {
                    $newValue = $newValue->format('Y-m-d');
                }
                if ($oldValue instanceof \DateTimeInterface) {
                    $oldValue = $oldValue->format('Y-m-d');
                }
            }

            $newStr = $newValue === null ? null : (string) $newValue;
            $oldStr = $oldValue === null ? null : (string) $oldValue;

            if ($newStr !== $oldStr) {
                $diff[$field] = [
                    'old' => $oldStr,
                    'new' => $newStr,
                ];
            }
        }

        return $diff;
    }
}
