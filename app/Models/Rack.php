<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rack extends Model
{
    protected $fillable = [
        'name',
        'location',
        'total_units',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'total_units' => 'integer',
        'is_active' => 'boolean',
    ];

    public function equipment(): HasMany
    {
        return $this->hasMany(RackEquipment::class, 'rack_id')->orderBy('position');
    }

    public function activeEquipment(): HasMany
    {
        return $this->hasMany(RackEquipment::class, 'rack_id')
            ->where('is_active', true)
            ->orderBy('position');
    }

    public function history(): HasMany
    {
        return $this->hasMany(RackEquipmentHistory::class, 'rack_id')->orderByDesc('changed_at');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getOccupiedPositionsCountAttribute(): int
    {
        return (int) $this->activeEquipment()
            ->whereNotNull('equipment_name')
            ->where('equipment_name', '!=', '')
            ->sum('size_u');
    }

    public function positionsMap(): array
    {
        $equipment = $this->equipment()->get();

        $map = [];
        foreach ($equipment as $item) {
            $end = (int) $item->position + (int) $item->size_u - 1;
            for ($i = (int) $item->position; $i <= $end; $i++) {
                $map[$i] = $item;
            }
        }

        $positions = [];
        for ($i = 1; $i <= $this->total_units; $i++) {
            $positions[] = $map[$i] ?? null;
        }

        return $positions;
    }
}
