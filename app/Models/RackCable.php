<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RackCable extends Model
{
    protected $table = 'rack_cables';

    protected $fillable = [
        'rack_id',
        'source_position',
        'source_equipment_id',
        'source_port',
        'destination_label',
        'destination_ip',
        'vlan',
        'cable_type',
        'color',
        'notes',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'source_position' => 'integer',
        'vlan' => 'integer',
        'is_active' => 'boolean',
    ];

    public const CABLE_TYPES = [
        'cat6' => 'Cat6 / Cat6a',
        'fiber-sm' => 'Fiber SM (Single-mode)',
        'fiber-mm' => 'Fiber MM (Multi-mode)',
        'dac' => 'DAC (Direct Attach Copper)',
        'console' => 'Console / Serial',
        'power' => 'Power',
        'coaxial' => 'Coaxial',
        'other' => 'Other',
    ];

    public const COLORS = [
        'green' => 'Green',
        'red' => 'Red',
        'blue' => 'Blue',
        'yellow' => 'Yellow',
        'orange' => 'Orange',
        'gray' => 'Gray',
    ];

    public function rack(): BelongsTo
    {
        return $this->belongsTo(Rack::class, 'rack_id');
    }

    public function sourceEquipment(): BelongsTo
    {
        return $this->belongsTo(RackEquipment::class, 'source_equipment_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getOriginLabelAttribute(): string
    {
        $parts = ["U{$this->source_position}"];
        if (!empty($this->source_port)) {
            $parts[] = $this->source_port;
        }
        return implode(' · ', $parts);
    }

    public function getDestinationShortAttribute(): string
    {
        return $this->destination_label;
    }
}
