<?php

namespace App\Livewire\App\DTH;

use App\Models\DthPowerSourceEvent;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EmergencyPowerTracker extends Component
{
    public string $powerSource = 'CFE';
    public ?string $notes = null;

    public function mount(): void
    {
        $this->authorizeArea();

        $latest = DthPowerSourceEvent::latest('changed_at')->first();
        if ($latest) {
            $this->powerSource = $latest->power_source;
        }
    }

    public function updatePowerSource(): void
    {
        $this->authorizeArea();

        $this->validate([
            'powerSource' => 'required|in:CFE,Planta Electrica',
            'notes' => 'nullable|string|max:500',
        ]);

        $latest = DthPowerSourceEvent::latest('changed_at')->first();
        if ($latest && $latest->power_source === $this->powerSource) {
            $this->dispatch('swal', [
                'icon' => 'info',
                'title' => 'Sin cambios',
                'text' => 'La fuente de energia ya estaba en el estado seleccionado.',
            ]);

            return;
        }

        DthPowerSourceEvent::create([
            'power_source' => $this->powerSource,
            'notes' => $this->notes ? trim($this->notes) : null,
            'changed_at' => now(),
            'user_id' => Auth::id(),
        ]);

        $this->notes = null;

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Estado actualizado',
            'text' => 'Se registro el cambio de energia correctamente.',
        ]);
    }

    public function getElapsedHumanProperty(): string
    {
        $latest = DthPowerSourceEvent::latest('changed_at')->first();
        if (! $latest) {
            return 'Sin registros';
        }

        $seconds = $latest->changed_at->diffInSeconds(now());

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }

    public function getShowFuelAlertProperty(): bool
    {
        $latest = DthPowerSourceEvent::latest('changed_at')->first();
        if (! $latest) {
            return false;
        }

        return $latest->changed_at->diffInSeconds(now()) >= (8 * 3600);
    }

    public function render()
    {
        $this->authorizeArea();

        $latest = DthPowerSourceEvent::latest('changed_at')->with('user:id,name')->first();

        return view('livewire.app.DTH.emergency-power-tracker', [
            'latestEvent' => $latest,
            'history' => DthPowerSourceEvent::query()
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
