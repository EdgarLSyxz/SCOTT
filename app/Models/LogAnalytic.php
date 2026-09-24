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

    public function getReportDateAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value instanceof \Carbon\Carbon ? $value : \Carbon\Carbon::instance($value);
        }

        $dateStr = (string) $value;

        try {
            return \Carbon\Carbon::parse($dateStr);
        } catch (\Exception $_) {
            $formats = [
                'd/m/Y H:i:s', 'd/m/Y H:i', 'd/m/Y',
                'd-m-Y H:i:s', 'd-m-Y H:i', 'd-m-Y',
                'd.m.Y H:i:s', 'd.m.Y H:i', 'd.m.Y',
                'Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d',
                'm/d/Y H:i:s', 'm/d/Y', 'Y/m/d H:i:s', 'Y/m/d',
            ];

            foreach ($formats as $fmt) {
                try {
                    $dt = \Carbon\Carbon::createFromFormat($fmt, $dateStr);
                    if ($dt !== false) {
                        return $dt;
                    }
                } catch (\Exception $_) {
                }
            }
        }

        return $dateStr;
    }
}
