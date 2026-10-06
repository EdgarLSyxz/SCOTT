<?php

namespace App\Livewire\App\SolarInterferences;

use App\Models\SolarInterference;
use App\Models\SolarInterferenceUpload;
use App\Services\SolarChannelResolver;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Component;

class SolarInterferencesModal extends Component
{
    public bool $open = false;

    public bool $loading = false;

    public ?string $activeDocumentName = null;

    public int $pastDays = 7;

    public int $futureDays = 30;

    public int $loadedAt = 0;

    public array $todayEvents = [];

    public array $groupedEvents = [];

    public array $orderedDates = [];

    public int $totalEvents = 0;

    public int $totalChannels = 0;

    public bool $todayHasData = false;

    public ?string $windowStart = null;

    public ?string $windowEnd = null;

    public int $totalDays = 0;

    public int $elapsedDays = 0;

    public int $remainingDays = 0;

    public int $progressPercent = 0;

    public string $progressStatus = 'upcoming';

    public function mount(): void
    {
        $this->activeDocumentName = $this->resolveActiveDocumentName();
    }

    #[On('open-solar-modal')]
    public function openModal(): void
    {
        $this->activeDocumentName = $this->resolveActiveDocumentName();
        $this->open = true;

        $this->loadModalData();
    }

    #[On('close-solar-modal')]
    public function closeModal(): void
    {
        $this->open = false;
    }

    public function refreshModal(): void
    {
        $this->loadModalData();
    }

    public function loadActiveData(): void
    {
        $this->activeDocumentName = $this->resolveActiveDocumentName();
    }

    public function loadModalData(): void
    {
        if (! $this->activeDocumentName) {
            $this->resetModalData();
            return;
        }

        $this->loading = true;

        try {
            $today = Carbon::today();
            $from = $today->copy()->subDays($this->pastDays)->toDateString();
            $to = $today->copy()->addDays($this->futureDays)->toDateString();

            $records = SolarInterference::query()
                ->where('document_name', $this->activeDocumentName)
                ->whereBetween('event_date', [$from, $to])
                ->orderBy('event_date')
                ->orderBy('start_time')
                ->get();

            $events = $this->buildEvents($records);
            $resolvedMap = app(SolarChannelResolver::class)->resolveMany(
                $events->pluck('channel')->filter()->unique()->all()
            );

            $events = $events
                ->map(function (array $event) use ($resolvedMap) {
                    $key = $event['channel']
                        ? mb_strtolower(trim(preg_replace('/\s+/u', ' ', $event['channel'])))
                        : null;
                    $event['resolved'] = $key !== null ? ($resolvedMap[$key] ?? null) : null;
                    return $event;
                })
                ->filter(fn (array $event) => $event['resolved'] !== null)
                ->values();

            $grouped = $events
                ->groupBy('date_key')
                ->sortKeys()
                ->map(fn (Collection $dayEvents) => $dayEvents
                    ->map(fn (array $e) => [
                        'channel_name' => $e['channel'],
                        'channel' => $e['resolved'],
                        'label' => $e['label'],
                        'start_time' => $e['start_time'],
                        'end_time' => $e['end_time'],
                        'duration_seconds' => $e['duration_seconds'],
                        'section' => $e['section'],
                    ])
                    ->values()
                    ->all())
                ->all();

            $todayKey = $today->format('Y-m-d');
            $yesterdayKey = $today->copy()->subDay()->format('Y-m-d');
            $todayEvents = $grouped[$todayKey] ?? [];

            $ordered = array_values(array_filter(
                array_keys($grouped),
                fn ($date) => $date !== $todayKey
            ));

            usort($ordered, function (string $a, string $b) use ($yesterdayKey) {
                if ($a === $yesterdayKey) {
                    return -1;
                }
                if ($b === $yesterdayKey) {
                    return 1;
                }
                return strcmp($b, $a);
            });

            $this->todayEvents = $todayEvents;
            $this->groupedEvents = $grouped;
            $this->orderedDates = $ordered;
            $this->todayHasData = ! empty($todayEvents);
            $this->totalEvents = collect($grouped)->flatten(1)->count();
            $this->totalChannels = collect($grouped)
                ->flatten(1)
                ->pluck('channel_name')
                ->filter()
                ->unique()
                ->count();
            $this->loadedAt = time();

            $this->computeProgressWindow();

            if ($this->todayHasData) {
                $this->dispatch('solar-focus-today');
            }
        } finally {
            $this->loading = false;
        }
    }

    protected function computeProgressWindow(): void
    {
        $upload = SolarInterferenceUpload::query()
            ->where('document_name', $this->activeDocumentName)
            ->first();

        if (! $upload || ! $upload->first_event_date || ! $upload->last_event_date) {
            $this->windowStart = null;
            $this->windowEnd = null;
            $this->totalDays = 0;
            $this->elapsedDays = 0;
            $this->remainingDays = 0;
            $this->progressPercent = 0;
            $this->progressStatus = 'upcoming';
            return;
        }

        $start = Carbon::parse($upload->first_event_date)->startOfDay();
        $end = Carbon::parse($upload->last_event_date)->startOfDay();
        $today = Carbon::today();

        $totalDays = $start->diffInDays($end) + 1;

        if ($today->lt($start)) {
            $elapsed = 0;
            $status = 'upcoming';
        } elseif ($today->gt($end)) {
            $elapsed = $totalDays;
            $status = 'finished';
        } else {
            $elapsed = $start->diffInDays($today) + 1;
            $status = 'active';
        }

        $percent = $totalDays > 0
            ? (int) round(($elapsed / $totalDays) * 100)
            : 0;

        $this->windowStart = $start->toDateString();
        $this->windowEnd = $end->toDateString();
        $this->totalDays = $totalDays;
        $this->elapsedDays = $elapsed;
        $this->remainingDays = max(0, $totalDays - $elapsed);
        $this->progressPercent = max(0, min(100, $percent));
        $this->progressStatus = $status;
    }

    protected function resetModalData(): void
    {
        $this->todayEvents = [];
        $this->groupedEvents = [];
        $this->todayHasData = false;
        $this->totalEvents = 0;
        $this->totalChannels = 0;
        $this->windowStart = null;
        $this->windowEnd = null;
        $this->totalDays = 0;
        $this->elapsedDays = 0;
        $this->remainingDays = 0;
        $this->progressPercent = 0;
        $this->progressStatus = 'upcoming';
        $this->loadedAt = time();
    }

    protected function buildEvents(Collection $records): Collection
    {
        $events = collect();

        foreach ($records as $record) {
            $channelList = $record->channel_list;

            if (empty($channelList)) {
                $events->push($this->buildEventEntry($record, null, $record->region_name));
                continue;
            }

            foreach ($channelList as $channel) {
                $events->push($this->buildEventEntry($record, $channel, $record->region_name));
            }
        }

        return $events;
    }

    protected function buildEventEntry(SolarInterference $record, ?string $channel, string $label): array
    {
        return [
            'channel' => $channel,
            'label' => $label,
            'date_key' => $record->event_date->format('Y-m-d'),
            'start_time' => $record->start_time,
            'end_time' => $record->end_time,
            'duration_seconds' => $record->duration_seconds,
            'section' => $record->section,
        ];
    }

    protected function resolveActiveDocumentName(): ?string
    {
        return Cache::remember(
            'solar:active_document_name',
            now()->addMinutes(5),
            function () {
                return SolarInterferenceUpload::active()
                    ->orderByDesc('last_event_date')
                    ->first()
                    ?->document_name;
            }
        );
    }

    public function render()
    {
        return view('livewire.app.solar-interferences.solar-interferences-modal', [
            'today' => Carbon::today()->format('Y-m-d'),
        ]);
    }
}