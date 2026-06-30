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
        'size_u',
    ];

    private const SNAPSHOT_FIELDS = [
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
        'size_u',
    ];

    public function created(RackEquipment $equipment): void
    {
        RackEquipmentHistory::create($this->snapshot($equipment, [
            'rack_equipment_id' => $equipment->id,
            'change_type' => RackEquipmentHistory::TYPE_CREATED,
            'changes' => null,
        ]));
    }

    public function updated(RackEquipment $equipment): void
    {
        $changes = $this->buildDiff($equipment);

        if (empty($changes)) {
            return;
        }

        RackEquipmentHistory::create($this->snapshot($equipment, [
            'rack_equipment_id' => $equipment->id,
            'change_type' => RackEquipmentHistory::TYPE_UPDATED,
            'changes' => $changes,
        ]));
    }

    public function deleted(RackEquipment $equipment): void
    {
        RackEquipmentHistory::create($this->snapshot($equipment, [
            'rack_equipment_id' => null,
            'change_type' => RackEquipmentHistory::TYPE_DELETED,
            'changes' => null,
        ]));
    }

    private function snapshot(RackEquipment $equipment, array $overrides): array
    {
        $base = [
            'rack_id' => $equipment->rack_id,
            'position' => $equipment->position,
            'changed_by' => Auth::id(),
            'changed_at' => now(),
        ];

        foreach (self::SNAPSHOT_FIELDS as $field) {
            $base[$field] = $equipment->{$field};
        }

        return array_merge($base, $overrides);
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
