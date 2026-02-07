<?php

namespace App\Livewire\Admin\Devices\Downloads;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Download;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DownloadHistoryTable extends Component
{
    use WithPagination;

    protected $listeners = [
        'downloads-updated' => 'onDownloadsUpdated',
    ];

    public $startDate;
    public $endDate;
    public $showDetailsModal = false;
    public $detailsYear;
    public $detailsMonth;
    public $detailsGrouped = [];

    public function resetFilters()
    {
        $this->reset(['startDate', 'endDate']);
        $this->resetPage();
        $this->dispatch('clear-datepicker-range');
    }

    protected function aggregatesQuery()
    {
        $query = Download::query()
            ->select('downloads.year', 'downloads.month', DB::raw('SUM(downloads.count) as total_count'), DB::raw('MAX(downloads.created_at) as created_at'))
            ->join('devices', 'downloads.device_id', '=', 'devices.id');

        if ($this->startDate && $this->endDate) {
            $start = \Carbon\Carbon::createFromFormat('Y-m-d', $this->startDate)->startOfDay();
            $end = \Carbon\Carbon::createFromFormat('Y-m-d', $this->endDate)->endOfDay();
            $query->whereBetween('downloads.created_at', [$start, $end]);
        }

        $auth = Auth::user();

        if ($auth && $auth->id !== 1) {
            if ($viewerArea = $auth->area) {
                $query->where('devices.area', $viewerArea);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $query->groupBy('downloads.year', 'downloads.month');

        return $query;
    }

    public function showMonthDetails($year, $month)
    {
        $this->detailsYear = (int) $year;
        $this->detailsMonth = (int) $month;

        $query = Download::with('device')
            ->where('year', $this->detailsYear)
            ->where('month', $this->detailsMonth);

        $auth = Auth::user();

        if ($auth && $auth->id !== 1) {
            if ($viewerArea = $auth->area) {
                $query->whereHas('device', fn($d) => $d->where('area', $viewerArea));
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $downloads = $query->orderBy('created_at')->get();

        $grouped = $downloads->groupBy(function ($d) {
            return sprintf('%04d-%02d-%02d', $d->year, $d->month, $d->day);
        });

        $grouped = $grouped->sortKeys();

        $this->detailsGrouped = $grouped->map(function ($items) {
            $byDevice = $items->groupBy('device_id');

            $arr = $byDevice->map(function ($group, $deviceId) {
                $first = $group->first();
                return [
                    'device_id' => $deviceId,
                    'id' => $first->id,
                    'device_name' => optional($first->device)->name,
                    'protocol' => optional($first->device)->protocol ?? '',
                    'image' => optional($first->device)->thumbnail ?? optional($first->device)->image ?? optional($first->device)->icon ?? null,
                    'count' => $group->sum('count'),
                    'created_at' => $first->created_at->format('Y-m-d H:i:s'),
                ];
            })->values()->toArray();
            $arr = array_values(array_filter($arr, function ($item) {
                return isset($item['count']) && (int) $item['count'] > 0;
            }));

            usort($arr, function ($a, $b) {
                $order = ['HLS' => 0, 'DASH' => 1];
                $pa = strtoupper($a['protocol'] ?? '');
                $pb = strtoupper($b['protocol'] ?? '');
                $ra = $order[$pa] ?? 2;
                $rb = $order[$pb] ?? 2;
                if ($ra !== $rb) {
                    return $ra < $rb ? -1 : 1;
                }
                if ($ra === 2 && $pa !== $pb) {
                    $cmp = strcmp($pa, $pb);
                    if ($cmp !== 0) return $cmp;
                }
                return strcmp($a['device_name'] ?? '', $b['device_name'] ?? '');
            });

            return $arr;
        })->toArray();

        $this->showDetailsModal = true;
    }

    public function render()
    {
        $query = $this->aggregatesQuery();
        $query->orderBy('year', 'desc')->orderBy('month', 'desc');
        $aggregates = $query->paginate(10);

        return view('livewire.admin.devices.downloads.download-history-table', compact('aggregates'));
    }

    public function onDownloadsUpdated($payload = null)
    {
        $this->resetPage();
    }
}
