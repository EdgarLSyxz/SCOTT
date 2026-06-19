<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RackEquipment extends Model
{
    protected $table = 'rack_equipment';

    protected $fillable = [
        'rack_id',
        'position',
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
        'updated_by',
    ];

    protected $casts = [
        'position' => 'integer',
        'installation_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function rack(): BelongsTo
    {
        return $this->belongsTo(Rack::class, 'rack_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isEmpty(): bool
    {
        return empty($this->equipment_name);
    }

    public function getDisplayLabelAttribute(): string
    {
        $parts = array_filter([
            $this->equipment_name,
            $this->equipment_role ? '(' . $this->equipment_role . ')' : null,
        ]);
        return $parts ? implode(' ', $parts) : '—';
    }

    public function getDisplaySubtitleAttribute(): string
    {
        $parts = array_filter([
            $this->equipment_model,
            $this->ip_address,
        ]);
        return $parts ? implode(' · ', $parts) : '';
    }
}
