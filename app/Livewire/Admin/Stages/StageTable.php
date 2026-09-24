<?php

namespace App\Livewire\Admin\Stages;

use App\Models\Stage;
use Livewire\Component;
use Livewire\WithPagination;

class StageTable extends Component
{
    use WithPagination;

    public $areaFilter = 'all';

    protected $queryString = [
        'areaFilter' => ['except' => 'all'],
    ];

    public function toggleAreaFilter()
    {
        $auth = auth()->user();

        if (! $auth || $auth->id !== 1) {
            return;
        }

        $options = ['all', 'DTH', 'OTT'];

        $currentIndex = array_search($this->areaFilter, $options, true);

        $this->areaFilter = $options[($currentIndex === false ? 0 : ($currentIndex + 1) % count($options))];
    }

    public function render()
    {
        $query = Stage::query();

        $auth = auth()->user();

        if ($auth && $auth->id === 1) {
            if (in_array($this->areaFilter, ['OTT', 'DTH'])) {
                $query->where('area', $this->areaFilter);
            }
        } else {
            if ($auth && isset($auth->area) && in_array($auth->area, ['OTT', 'DTH'])) {
                $query->where('area', $auth->area);
            }
        }

        $stages = $query->orderBy('status', 'desc')->paginate(10);

        return view('livewire.admin.stages.stage-table', compact('stages'));
    }
}
