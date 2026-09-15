<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use App\Models\User;

class UserTable extends Component
{
    public $areaFilter = 'all';
    public $statusFilter = 'active';

    protected $queryString = [
        'areaFilter' => ['except' => 'all'],
        'statusFilter' => ['except' => 'active'],
    ];

    protected function isMaster($user)
    {
        return $user && ($user->id === 1 || $user->hasRole('master'));
    }

    public function toggleAreaFilter()
    {
        $auth = auth()->user();

        if (! $this->isMaster($auth)) {
            return;
        }

        $options = ['all', 'DTH', 'OTT'];

        $currentIndex = array_search($this->areaFilter, $options, true);

        $this->areaFilter = $options[($currentIndex === false ? 0 : ($currentIndex + 1) % count($options))];
    }

    public function toggleStatusFilter()
    {
        $auth = auth()->user();

        if (! $this->isMaster($auth)) {
            return;
        }

        $options = ['all', 'active', 'inactive'];

        $currentIndex = array_search($this->statusFilter, $options, true);

        $this->statusFilter = $options[($currentIndex === false ? 0 : ($currentIndex + 1) % count($options))];
    }

    public function render()
    {
        $user = auth()->user();

        $query = User::query();

        if ($this->isMaster($user)) {
            if (in_array($this->areaFilter, ['OTT', 'DTH'])) {
                $filter = $this->areaFilter;
                $query->where(function ($q) use ($filter) {
                    $q->where('default_area', $filter)
                      ->orWhere('area', $filter);
                });
            }

            if ($this->areaFilter === 'all') {
                $query->orderByRaw("(CASE WHEN COALESCE(area, default_area) = 'OTT' THEN 1 WHEN COALESCE(area, default_area) = 'DTH' THEN 2 ELSE 3 END) ASC");
            }

            if (in_array($this->statusFilter, ['active', 'inactive'])) {
                $query->where('status', $this->statusFilter === 'active');
            }
        } elseif ($user) {
            $query->where('id', $user->id);
        } else {
            $query->whereRaw('1 = 0');
        }

        $users = $query->orderBy('status', 'desc')->get();

        return view('livewire.admin.users.user-table', compact('users'));
    }
}
