<?php

namespace App\Livewire\App\DTH;

use App\Models\DthTransponderRecord;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TransponderTracker extends Component
{
    public string $upLinkSite = 'Zacatecas';
    public string $transponders = 'KU01,KU03, KU05,KU07, KU09 Y KU11';

    protected array $allowedSites = ['Zacatecas', 'Iztapalapa', 'Distribuido'];

    public function mount(): void
    {
        $this->authorizeArea();
    }

    public function saveRecord(): void
    {
        $this->authorizeArea();

        $this->validate([
            'upLinkSite' => 'required|in:Zacatecas,Iztapalapa,Distribuido',
            'transponders' => 'required|string|max:255',
        ]);

        DthTransponderRecord::create([
            'up_link_site' => $this->upLinkSite,
            'transponders' => trim($this->transponders),
            'description' => $this->buildDescription(),
            'recorded_at' => now(),
            'user_id' => Auth::id(),
        ]);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Registro guardado',
            'text' => 'Se guardo el estado actual de transponders.',
        ]);
    }

    public function getCurrentDescriptionProperty(): string
    {
        return $this->buildDescription();
    }

    public function render()
    {
        $this->authorizeArea();

        return view('livewire.app.DTH.transponder-tracker', [
            'sites' => $this->allowedSites,
            'latestRecord' => DthTransponderRecord::latest('recorded_at')->first(),
            'history' => DthTransponderRecord::query()
                ->latest('recorded_at')
                ->with('user:id,name')
                ->limit(30)
                ->get(),
        ]);
    }

    private function buildDescription(): string
    {
        return sprintf('%s desde %s', trim($this->transponders), $this->upLinkSite);
    }

    private function authorizeArea(): void
    {
        $user = Auth::user();
        if (! $user || $user->area !== 'DTH') {
            abort(403);
        }
    }
}
