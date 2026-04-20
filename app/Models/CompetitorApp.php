<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetitorApp extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'store_url',
        'is_primary',
        'is_active',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function snapshots(): HasMany
    {
        return $this->hasMany(CompetitorAppSnapshot::class);
    }
}
