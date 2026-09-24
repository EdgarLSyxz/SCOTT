<?php

namespace App\Livewire\Admin\Channels;

use App\Enums\ChannelCategory;
use App\Enums\ChannelOrigin;
use App\Models\Channel;
use Livewire\Component;
use Livewire\WithPagination;

class ChannelTable extends Component
{
    use WithPagination;

    public $search = '';

    public $showInactive = false;

    public $showActive = false;

    public $showDualAudio = false;

    public $originFilter = null;

    public $categoryFilter = null;

    public $areaFilter = 'all';

    protected $queryString = ['search', 'showInactive', 'showActive', 'showDualAudio', 'originFilter', 'categoryFilter', 'areaFilter' => ['except' => 'all']];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingShowInactive()
    {
        if ($this->showInactive) {
            $this->showActive = false;
        }

        $this->resetPage();
    }

    public function updatingShowActive()
    {
        if ($this->showActive) {
            $this->showInactive = false;
        }

        $this->resetPage();
    }

    public function updatingShowDualAudio()
    {
        $this->resetPage();
    }

    public function toggleOriginFilter()
    {
        $origins = collect(ChannelOrigin::cases())->map(fn ($case) => $case->value)->all();

        if ($this->originFilter === null) {
            $this->originFilter = $origins[0];
        } else {
            $currentIndex = array_search($this->originFilter, $origins);
            $this->originFilter = $origins[$currentIndex + 1] ?? null;
        }

        $this->resetPage();
    }

    public function toggleCategoryFilter()
    {
        $categories = collect(ChannelCategory::cases())->map(fn ($case) => $case->value)->all();

        if ($this->categoryFilter === null) {
            $this->categoryFilter = $categories[0];
        } else {
            $currentIndex = array_search($this->categoryFilter, $categories);
            $this->categoryFilter = $categories[$currentIndex + 1] ?? null;
        }

        $this->resetPage();
    }

    public function toggleAreaFilter()
    {
        $areas = ['all', 'DTH', 'OTT'];

        $currentIndex = array_search($this->areaFilter, $areas, true);
        $nextIndex = ($currentIndex === false) ? 1 : ($currentIndex + 1) % count($areas);
        $this->areaFilter = $areas[$nextIndex];

        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->resetPage();
        $this->search = '';
        $this->showInactive = false;
        $this->showActive = false;
        $this->showDualAudio = false;
        $this->originFilter = null;
        $this->categoryFilter = null;
    }

    public function render()
    {
        $query = Channel::query();

        if ($this->search) {
            $searchTerms = explode(' ', $this->search);

            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->where(function ($subQuery) use ($term) {
                        $subQuery->where('name', 'like', '%'.$term.'%')
                            ->orWhere('number', 'like', '%'.$term.'%');
                    });
                }
            });
        }

        if ($this->showInactive) {
            $query->where('status', '0');
        }

        if ($this->showActive) {
            $query->where('status', '1');
        }

        if ($this->originFilter) {
            $query->where('origin', $this->originFilter);
        }

        if ($this->categoryFilter) {
            $query->where('category', $this->categoryFilter);
        }

        if ($this->areaFilter && $this->areaFilter !== 'all') {
            if (auth()->user()?->id === 1) {
                if ($this->areaFilter === 'DTH') {
                    $query->where(function ($q) {
                        $q->where('area', 'DTH')->orWhere('area', 'DTH/OTT');
                    });
                } else {
                    $query->where(function ($q) {
                        $q->where('area', 'OTT')->orWhere('area', 'DTH/OTT');
                    });
                }
            } else {
                if ($this->areaFilter === 'DTH') {
                    $query->where(function ($q) {
                        $q->where('area', 'DTH')->orWhere('area', 'DTH/OTT');
                    });
                } else {
                    $query->where(function ($q) {
                        $q->where('area', 'OTT')->orWhere('area', 'DTH/OTT');
                    });
                }
            }
        }

        $channels = $query->orderByDesc('status')->orderBy('number')->paginate(10);

        return view('livewire.admin.channels.channel-table', [
            'channels' => $channels,
            'showDualAudio' => $this->showDualAudio,
        ]);
    }
}
