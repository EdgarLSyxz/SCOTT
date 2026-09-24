<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolarInterference extends Model
{
    use HasFactory;

    public const SECTION_STATE = 'state';

    public const SECTION_SATELLITE = 'satellite';

    public const SECTION_TELEPORT = 'teleport';

    protected $table = 'solar_interferences';

    protected $fillable = [
        'document_name',
        'section',
        'region_name',
        'event_date',
        'start_time',
        'end_time',
        'duration_seconds',
        'channels',
        'affected_channels_count',
        'uploaded_by',
    ];

    protected $casts = [
        'event_date' => 'date',
        'channels' => 'array',
        'affected_channels_count' => 'integer',
        'duration_seconds' => 'integer',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopeForDocument($query, string $documentName)
    {
        return $query->where('document_name', $documentName);
    }

    public function getSectionLabelAttribute(): string
    {
        return match ($this->section) {
            self::SECTION_STATE => __('State'),
            self::SECTION_SATELLITE => __('Satellite'),
            self::SECTION_TELEPORT => __('Telepuerto'),
            default => ucfirst((string) $this->section),
        };
    }

    public function getChannelListAttribute(): array
    {
        return is_array($this->channels) ? array_values(array_filter($this->channels)) : [];
    }
}
