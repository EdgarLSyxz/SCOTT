<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceStoreAvailability extends Model
{
    protected $fillable = [
        'report_id',
        'device_id',
        'user_id',
        'is_active',
        'is_available_in_store',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_available_in_store' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
