<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Channel extends Model
{
    protected $fillable = [
        'image_url',
        'number',
        'area',
        'origin',
        'name',
        'url',
        'category',
        'audio_spanish_enabled',
        'audio_english_enabled',
        'subtitles_enabled',
        'profiles',
        'status',
    ];

    protected $casts = [
        'profiles' => 'array',
        'audio_spanish_enabled' => 'boolean',
        'audio_english_enabled' => 'boolean',
        'subtitles_enabled' => 'boolean',
    ];

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn () => Storage::url($this->image_url),
        );
    }

    public function reportDetails()
    {
        return $this->hasMany(ReportDetail::class);
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }
}
