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
        return $this->activeEquipment()
            ->whereNotNull('equipment_name')
            ->where('equipment_name', '!=', '')
            ->count();
    }
}
