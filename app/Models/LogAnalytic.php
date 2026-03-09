<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAnalytic extends Model
{
    protected $table = 'log_analytics';

    protected $fillable = [
        'user_id',
        'filename',
        'report_date',
        'data',
    ];

    protected $casts = [
        'report_date' => 'date',
        'data' => 'json',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCategory($categoryName)
    {
        return $this->data[$categoryName] ?? [];
    }

    public function allCategories()
    {
        return array_keys($this->data ?? []);
    }
}
