<?php

namespace App\Livewire\App\SolarInterferences;

use App\Models\SolarInterference;
use App\Models\SolarInterferenceUpload;
use App\Services\SolarChannelResolver;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class SolarInterferencesWidget extends Component
{
    public bool $open = false;

    public ?string $activeDocumentName = null;

    public function mount()
    {
        $this->loadActiveData();
    }

    public function loadActiveData(): void
    {
        $active = SolarInterferenceUpload::active()
            ->orderByDesc('last_event_date')
            ->first();

        $this->activeDocumentName = $active?->document_name;
    }

    public function openModal(): void
    {
        $this->open = true;
    }

    public function closeModal(): void
    {
        $this->open = false;
    }

    public function getRecordsProperty(): Collection
    {
        if (! $this->activeDocumentName) {
            return collect();
        }

        return SolarInterference::query()
            ->where('document_name', $this->activeDocumentName)
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get();
    }

    public function getEventsProperty(): Collection
    {
        $events = collect();

        foreach ($this->records as $record) {
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
            'record' => $record,
            'channel' => $channel,
            'label' => $label,
            'date' => $record->event_date,
            'date_key' => $record->event_date->format('Y-m-d'),
            'start_time' => $record->start_time,
            'end_time' => $record->end_time,
            'duration_seconds' => $record->duration_seconds,
            'section' => $record->section,
            'section_label' => $record->section_label,
        ];
    }

    public function getGroupedEventsProperty(): Collection
    {
        return $this->events
            ->groupBy('date_key')
            ->sortKeys();
    }

    public function resolveChannel(string $channelName): ?array
    {
        return app(SolarChannelResolver::class)->resolve($channelName);
    }

    public function render()
    {
        $today = Carbon::today()->format('Y-m-d');
        $groupedEvents = $this->groupedEvents;
        $todayEvents = $groupedEvents->get($today, collect());

        $orderedDates = $groupedEvents
            ->keys()
            ->sortBy(function ($date) use ($today) {
                return $date === $today ? '0' : $date;
            })
            ->values();

        $todayChannels = $todayEvents
            ->pluck('channel')
            ->filter()
            ->unique()
            ->filter(fn($ch) => $this->resolveChannel($ch))
            ->values();

        return view('livewire.app.solar-interferences.solar-interferences-widget', [
            'groupedEvents' => $groupedEvents,
            'orderedDates' => $orderedDates,
            'today' => $today,
            'todayEvents' => $todayEvents,
            'todayHasData' => $todayEvents->isNotEmpty(),
            'todayChannels' => $todayChannels,
            'totalEvents' => $this->events->count(),
            'totalChannels' => $this->events->pluck('channel')->filter()->unique()->count(),
        ]);
    }
}
