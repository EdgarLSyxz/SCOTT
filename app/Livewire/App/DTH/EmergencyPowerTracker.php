<?php

namespace App\Livewire\App\DTH;

use App\Models\DTHPowerSourceEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class EmergencyPowerTracker extends Component
{
    public string $powerSource = 'CFE';

    public string $notes = '';

    private const FUEL_ALERT_HOURS = 8;

    public function mount(): void
    {
        $this->authorizeArea();

        if (! Schema::hasTable('dth_power_source_events')) {
            return;
        }

        $latest = DTHPowerSourceEvent::latest('changed_at')->first();
        if ($latest) {
            $this->powerSource = $latest->power_source === 'Planta Electrica'
                ? 'Power Plant'
                : $latest->power_source;
        }
    }

    public function updatePowerSource(): void
    {
        $this->authorizeArea();

        if (! Schema::hasTable('dth_power_source_events')) {
            return;
        }

        $this->validate([
            'powerSource' => 'required|in:CFE,Power Plant',
            'notes' => 'required|string|max:500',
        ]);

        $latest = DTHPowerSourceEvent::latest('changed_at')->first();
        if ($latest && $latest->power_source === $this->powerSource) {
            $this->dispatch('swal', [
                'icon' => 'info',
                'title' => __('No changes detected'),
                'text' => __('The power source is already set to the selected value.'),
            ]);

            return;
        }

        DTHPowerSourceEvent::create([
            'power_source' => $this->powerSource,
            'notes' => trim($this->notes),
            'changed_at' => now(),
            'user_id' => Auth::id(),
        ]);

        $this->notes = '';

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => __('Power source updated'),
            'text' => __('The power source change was recorded successfully.'),
        ]);
    }

    public function getElapsedHumanProperty(): string
    {
        if (! Schema::hasTable('dth_power_source_events')) {
            return __('No records yet');
        }

        $latest = DTHPowerSourceEvent::latest('changed_at')->first();
        if (! $latest) {
            return __('No records yet');
        }

        $seconds = $latest->changed_at->diffInSeconds(now());

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }

    public function getShowFuelAlertProperty(): bool
    {
        if (! Schema::hasTable('dth_power_source_events')) {
            return false;
        }

        $latest = DTHPowerSourceEvent::latest('changed_at')->first();
        if (! $latest) {
            return false;
        }

        return $latest->changed_at->diffInSeconds(now()) >= (self::FUEL_ALERT_HOURS * 3600);
    }

    public function render()
    {
        $this->authorizeArea();

        if (! Schema::hasTable('dth_power_source_events')) {
            return view('livewire.app.DTH.emergency-power-tracker', [
                'latestEvent' => null,
                'history' => collect(),
            ]);
        }

        $latest = DTHPowerSourceEvent::latest('changed_at')->with('user:id,name')->first();

        return view('livewire.app.DTH.emergency-power-tracker', [
            'latestEvent' => $latest,
            'history' => DTHPowerSourceEvent::query()
                ->latest('changed_at')
                ->with('user:id,name')
                ->limit(30)
                ->get(),
        ]);
    }

    private function authorizeArea(): void
    {
        $user = Auth::user();
        if (! $user || $user->area !== 'DTH') {
            abort(403);
        }
    }
}
