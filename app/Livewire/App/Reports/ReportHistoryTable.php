<?php

namespace App\Livewire\App\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Report;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportsExport;

class ReportHistoryTable extends Component
{
    use WithPagination;

    public $search = '';
    public $orderField = 'created_at';
    public $orderDirection = 'desc';
    public $areaFilter = 'all';
    public $statusFilter = null;
    public $selectedUser = null;
    public $typeFilter = null;
    public $reportTypes = ['Momentary', 'Hourly', 'Functions'];
    public $statusOptions = ['Revision', 'Resolved', 'Reported'];
    public $userOptions = [];
    public $startDate;
    public $endDate;
    public $currentTypeIndex = -1;
    public $currentStatusIndex = -1;
    public $currentUserIndex = -1;
    protected $queryString = ['search', 'orderField', 'orderDirection', 'areaFilter' => ['except' => 'all']];

    public function mount()
    {
        $this->userOptions = User::whereHas('reports')->pluck('name')->toArray();

        $this->selectedUser = null;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function setOrder($field)
    {
        if ($this->orderField === $field) {
            $this->orderDirection = $this->orderDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->orderField = $field;
            $this->orderDirection = 'asc';
        }
    }

    public function setOrderByReporter()
    {
        $usersWithReports = Report::select('reported_by')
            ->distinct()
            ->pluck('reported_by')
            ->toArray();

        if (empty($usersWithReports))
            return;

        if ($this->orderField !== 'reported_by') {
            $this->orderField = 'reported_by';
            $this->currentUserIndex = 0;
        } else {
            $this->currentUserIndex = ($this->currentUserIndex + 1) % count($usersWithReports);
        }

        $this->selectedUser = $usersWithReports[$this->currentUserIndex];
    }

    public function toggleStatusFilter()
    {
        $total = count($this->statusOptions);
        $this->currentStatusIndex = ($this->currentStatusIndex + 1) % ($total + 1);

        $this->statusFilter = $this->currentStatusIndex === $total
            ? null
            : $this->statusOptions[$this->currentStatusIndex];
    }

    public function toggleTypeFilter()
    {
        $total = count($this->reportTypes);
        $this->currentTypeIndex = ($this->currentTypeIndex + 1) % ($total + 1);

        $this->typeFilter = $this->currentTypeIndex === $total
            ? null
            : $this->reportTypes[$this->currentTypeIndex];
    }

    public function resetFilters()
    {
        $this->reset([
            'search',
            'orderField',
            'orderDirection',
            'statusFilter',
            'selectedUser',
            'typeFilter',
            'startDate',
            'endDate'
        ]);

        $this->currentTypeIndex = -1;
        $this->currentStatusIndex = -1;
        $this->currentUserIndex = -1;

        $this->resetPage();

        $this->dispatch('clear-datepicker-range');
    }

    public function toggleUserFilter()
    {
        if (empty($this->userOptions)) {
            return;
        }

        $total = count($this->userOptions);
        $this->currentUserIndex = ($this->currentUserIndex + 1) % ($total + 1);

        $this->selectedUser = $this->currentUserIndex === $total
            ? null
            : $this->userOptions[$this->currentUserIndex];
    }

    public function toggleAreaFilter()
    {
        $auth = auth()->user();

        if (! $auth || $auth->id !== 1) {
            return;
        }

        $options = ['all', 'DTH', 'OTT'];

        $currentIndex = array_search($this->areaFilter, $options, true);

        $this->areaFilter = $options[($currentIndex === false ? 0 : ($currentIndex + 1) % count($options))];
        $this->resetPage();
    }

    protected function filteredQuery()
    {
        $query = Report::query()
            ->where(function ($q) {
                $q->where('category', 'like', "%{$this->search}%")
                    ->orWhereHas('reportedBy', fn($query) =>
                    $query->where('name', 'like', "%{$this->search}%"))
                    ->orWhereHas('reportDetails.channel', function ($q) {
                        $q->where('name', 'like', "%{$this->search}%")
                            ->orWhere('number', 'like', "%{$this->search}%")
                            ->orWhereRaw("CONCAT(number, ' ', name) LIKE ?", ["%{$this->search}%"]);
                    })
                    ->orWhereHas('deviceStoreAvailabilities.device', function ($q) {
                        $q->where('name', 'like', "%{$this->search}%");
                    });
            })
            ->with(['reportedBy', 'stages', 'reportDetails.channel', 'reportDetails.reportContentLosses', 'deviceStoreAvailabilities.device']);

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        if ($this->selectedUser) {
            $query->whereHas('reportedBy', fn($q) =>
            $q->where('name', $this->selectedUser));
        }

        if ($this->startDate && $this->endDate) {
            $start = \Carbon\Carbon::createFromFormat('Y-m-d', $this->startDate)->startOfDay();
            $end = \Carbon\Carbon::createFromFormat('Y-m-d', $this->endDate)->endOfDay();
            $query->whereBetween('created_at', [$start, $end]);
        }

        $auth = auth()->user();

        if ($auth && $auth->id === 1) {
            if ($this->areaFilter && in_array($this->areaFilter, ['DTH', 'OTT'], true)) {
                $query->where('area', $this->areaFilter);
            }
        } else {
            if ($auth && ($viewerArea = ($auth->area))) {
                $query->where('area', $viewerArea);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $query;
    }

    public function exportToExcel()
    {
        $reports = $this->filteredQuery()
            ->orderBy($this->orderField, $this->orderDirection)
            ->get();

        $date = now()->format('Y-m-d_H-i-s');
        $filename = "Report_{$date}.xlsx";

        return Excel::download(new ReportsExport($reports), $filename);
    }

    public function render()
    {
        $reports = $this->filteredQuery()
            ->orderBy($this->orderField, $this->orderDirection)
            ->paginate(10);

        return view('livewire.app.reports.report-history-table', compact('reports'));
    }
}
