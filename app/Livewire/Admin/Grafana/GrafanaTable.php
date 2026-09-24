<?php

namespace App\Livewire\Admin\Grafana;

use App\Models\GrafanaPanel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class GrafanaTable extends Component
{
    use AuthorizesRequests;

    public function render()
    {
        $this->authorize('viewAny', GrafanaPanel::class);

        $user = auth()->user();

        $query = GrafanaPanel::query();

        if ($user) {
            if ($user->id === 1) {
            } else {
                if (in_array($user->area, ['OTT', 'DTH'])) {
                    if ($user->area === 'DTH') {
                        $query->where(function ($q) {
                            $q->where('area', 'DTH')
                                ->orWhereIn('id', [1, 3]);
                        });
                    } else {
                        $query->where('area', $user->area);
                    }
                } else {
                    if (! empty($user->area)) {
                        $query->where('area', $user->area);
                    } else {
                        $query->whereRaw('0 = 1');
                    }
                }
            }
        } else {
            $query->whereRaw('0 = 1');
        }

        $panels = $query->get();

        $panels = $panels->sortBy(function ($panel) {
            if ($panel->id == 2) {
                return 3;
            }
            if ($panel->id == 3) {
                return 2;
            }

            return $panel->id;
        })->values();

        return view('livewire.admin.grafana.grafana-table', [
            'panels' => $panels,
        ]);
    }
}
