<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RackEquipmentHistory extends Model
{
    protected $table = 'rack_equipment_history';

    public $timestamps = false;

    protected $fillable = [
        'rack_id',
        'position',
        'rack_equipment_id',
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
        'change_type',
        'changes',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'position' => 'integer',
        'installation_date' => 'date',
        'changed_at' => 'datetime',
        'changes' => 'array',
    ];

    public const TYPE_CREATED = 'created';

    public const TYPE_UPDATED = 'updated';

    public const TYPE_DELETED = 'deleted';

    public function rack(): BelongsTo
    {
        return $this->belongsTo(Rack::class, 'rack_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(RackEquipment::class, 'rack_equipment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getChangeTypeLabelAttribute(): string
    {
        return match ($this->change_type) {
            self::TYPE_CREATED => __('Created'),
            self::TYPE_UPDATED => __('Updated'),
            self::TYPE_DELETED => __('Deleted'),
            default => ucfirst((string) $this->change_type),
        };
    }

    public function getChangeTypeColorAttribute(): string
    {
        return match ($this->change_type) {
            self::TYPE_CREATED => 'green',
            self::TYPE_UPDATED => 'blue',
            self::TYPE_DELETED => 'red',
            default => 'gray',
        };
    }
}
