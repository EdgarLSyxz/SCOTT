<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transponder extends Model
{
    use HasFactory;

    public const SITE_ZACATECAS = 'Zacatecas';
    public const SITE_TOLUCA = 'Toluca';

    public const ALLOWED_SITES = [
        self::SITE_ZACATECAS,
        self::SITE_TOLUCA,
    ];

    protected $fillable = [
        'code',
        'description',
        'active_site',
        'is_on',
        'sort_order',
    ];

    protected $casts = [
        'is_on' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(TransponderStateEvent::class)->latest('created_at');
    }

    public function oppositeSite(): string
    {
        return $this->active_site === self::SITE_TOLUCA
            ? self::SITE_ZACATECAS
            : self::SITE_TOLUCA;
    }

    public function timePerSite(): array
    {
        $now = now();
        $events = $this->events()->orderBy('created_at')->get();

        $totals = [
            self::SITE_ZACATECAS => 0,
            self::SITE_TOLUCA => 0,
        ];

        if ($events->isEmpty()) {
            $totals[$this->active_site] = $this->created_at
                ? $this->created_at->diffInSeconds($now)
                : 0;
            return $totals;
        }

        $previousSite = $this->active_site;
        $previousTime = $events->first()->created_at;

        foreach ($events as $event) {
            $segment = $previousTime->diffInSeconds($event->created_at);
            if (isset($totals[$previousSite])) {
                $totals[$previousSite] += $segment;
            }
            $previousSite = $event->to_site;
            $previousTime = $event->created_at;
        }

        $tailSegment = $previousTime->diffInSeconds($now);
        if (isset($totals[$previousSite])) {
            $totals[$previousSite] += $tailSegment;
        }

        return $totals;
    }
}
