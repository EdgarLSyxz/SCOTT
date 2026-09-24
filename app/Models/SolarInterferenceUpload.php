<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolarInterferenceUpload extends Model
{
    use HasFactory;

    protected $table = 'solar_interference_uploads';

    protected $fillable = [
        'document_name',
        'records_count',
        'states_count',
        'satellites_count',
        'teleports_count',
        'first_event_date',
        'last_event_date',
        'is_active',
        'uploaded_by',
    ];

    protected $casts = [
        'records_count' => 'integer',
        'states_count' => 'integer',
        'satellites_count' => 'integer',
        'teleports_count' => 'integer',
        'first_event_date' => 'date',
        'last_event_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function interferences()
    {
        return $this->hasMany(SolarInterference::class, 'document_name', 'document_name');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getRouteKeyName(): string
    {
        return 'document_name';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('document_name', $value)->firstOrFail();
    }
}
