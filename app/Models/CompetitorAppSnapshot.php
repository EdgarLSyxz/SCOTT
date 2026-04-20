<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitorAppSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'competitor_app_id',
        'snapshot_date',
        'rating',
        'downloads_label',
        'reviews_label',
        'release_date',
        'rank_position',
        'rank_delta',
        'movement',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'release_date' => 'date',
        'rating' => 'decimal:2',
        'rank_position' => 'integer',
        'rank_delta' => 'integer',
    ];

    public function app(): BelongsTo
    {
        return $this->belongsTo(CompetitorApp::class, 'competitor_app_id');
    }
}
