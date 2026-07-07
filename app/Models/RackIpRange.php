<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RackIpRange extends Model
{
    protected $table = 'rack_ip_ranges';

    protected $fillable = [
        'rack_id',
        'cidr_range',
        'mask',
        'vlan',
        'description',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'vlan' => 'integer',
        'is_active' => 'boolean',
    ];

    public function rack(): BelongsTo
    {
        return $this->belongsTo(Rack::class, 'rack_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
