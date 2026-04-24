<?php

namespace App\Livewire\App\DTH;

use App\Models\DthTransponderRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class TransponderTracker extends Component
{
    public string $upLinkSite = 'Zacatecas';
    public string $transponders = 'KU01, KU03, KU05, KU07, KU09, KU11';

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
            'title' => __('Record saved'),
            'text' => __('The current transponder status was saved successfully.'),
        ]);
    }

    public function getCurrentDescriptionProperty(): string
    {
        return $this->buildDescription();
    }

    public function render()
    {
        $this->authorizeArea();

        if (! Schema::hasTable('dth_transponder_records')) {
            return view('livewire.app.DTH.transponder-tracker', [
                'sites' => $this->allowedSites,
                'latestRecord' => null,
                'history' => collect(),
            ]);
        }

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
        if ($this->upLinkSite === 'Distribuido') {
            return sprintf('%s %s', trim($this->transponders), __('Distributed between Zacatecas and Iztapalapa'));
        }

        return sprintf('%s %s %s', trim($this->transponders), __('from'), $this->upLinkSite);
    }

    private function authorizeArea(): void
    {
        $user = Auth::user();
        if (! $user || $user->area !== 'DTH') {
            abort(403);
        }
    }
}
