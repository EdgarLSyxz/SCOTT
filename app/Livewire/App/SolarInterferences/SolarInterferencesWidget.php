<?php

namespace App\Livewire\App\SolarInterferences;

use App\Models\SolarInterference;
use App\Models\SolarInterferenceUpload;
use App\Services\SolarChannelResolver;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class SolarInterferencesWidget extends Component
{
    public ?string $activeDocumentName = null;

    public bool $todayHasData = false;

    public int $todayChannelsCount = 0;

    public function mount(): void
    {
        $this->refreshBadge();
    }

    public function loadActiveData(): void
    {
        $this->refreshBadge();
    }

    protected function refreshBadge(): void
    {
        $this->activeDocumentName = Cache::remember(
            'solar:active_document_name',
            now()->addMinutes(5),
            function () {
                return SolarInterferenceUpload::active()
                    ->orderByDesc('last_event_date')
                    ->first()
                    ?->document_name;
            }
        );

        if (! $this->activeDocumentName) {
            $this->todayHasData = false;
            $this->todayChannelsCount = 0;
            return;
        }

        $today = Carbon::today()->toDateString();

        $channels = SolarInterference::query()
            ->where('document_name', $this->activeDocumentName)
            ->whereDate('event_date', $today)
            ->pluck('channels');

        $unique = collect();

        foreach ($channels as $json) {
            if (is_array($json)) {
                foreach ($json as $name) {
                    if ($name) {
                        $unique->push($name);
                    }
                }
            }
        }

        $unique = $unique->unique();

        if ($unique->isEmpty()) {
            $this->todayHasData = false;
            $this->todayChannelsCount = 0;
            return;
        }

        $resolved = app(SolarChannelResolver::class)->resolveMany($unique->all());

        $count = $unique
            ->filter(function (string $name) use ($resolved) {
                $key = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $name)));
                return ! empty($resolved[$key]);
            })
            ->count();

        $this->todayHasData = $count > 0;
        $this->todayChannelsCount = $count;
    }

    public function render()
    {
        return view('livewire.app.solar-interferences.solar-interferences-widget');
    }
}