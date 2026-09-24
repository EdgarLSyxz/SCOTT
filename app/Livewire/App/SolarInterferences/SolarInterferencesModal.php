<?php

namespace App\Livewire\App\SolarInterferences;

use App\Models\SolarInterference;
use App\Models\SolarInterferenceUpload;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class SolarInterferencesModal extends Component
{
    public bool $open = false;

    public ?string $activeDocumentName = null;

    protected $listeners = [
        'open-solar-modal' => 'openModal',
        'close-solar-modal' => 'closeModal',
    ];

    public function openModal(): void
    {
        $this->loadActiveData();
        $this->open = true;
    }

    public function closeModal(): void
    {
        $this->open = false;
    }

    public function loadActiveData(): void
    {
        $active = SolarInterferenceUpload::active()
            ->orderByDesc('last_event_date')
            ->first();

        $this->activeDocumentName = $active?->document_name;
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

    public function render()
    {
        $today = Carbon::today()->format('Y-m-d');
        $groupedEvents = $this->groupedEvents;
        $todayEvents = $groupedEvents->get($today, collect());

        return view('livewire.app.solar-interferences.solar-interferences-modal', [
            'groupedEvents' => $groupedEvents,
            'today' => $today,
            'todayEvents' => $todayEvents,
            'todayHasData' => $todayEvents->isNotEmpty(),
            'totalEvents' => $this->events->count(),
            'totalChannels' => $this->events->pluck('channel')->filter()->unique()->count(),
        ]);
    }
}
