<?php

namespace App\Livewire\Admin\Devices\Downloads;

use Livewire\Component;
use App\Models\Device;
use App\Models\Download;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MonthlyDownloadsReport extends Component
{
    public $year;
    public $month;
    public $day;
    public $years = [];
    public $months = [];
    public $days = [];
    public $devices;
    public $counts = [];
    public $successMessage = null;

    protected $rules = [
        'year' => 'required|integer|min:1900',
        'month' => 'required|integer|min:1|max:12',
        'day' => 'required|integer|min:1|max:31',
        'counts' => 'array',
        'counts.*' => 'integer|min:0',
    ];

    public function mount()
    {
        $allowedIds = [1, 2, 3, 5, 7, 8];

        if (! (Auth::id() && in_array((int) Auth::id(), $allowedIds, true))) {
            abort(403);
        }

        $this->year = (int) date('Y');
        $this->month = (int) date('n');
        $this->day = (int) date('j');
        $exclude = ['Web Client', 'Android Mobile', 'Android TV'];
        $this->devices = Device::whereNotIn('name', $exclude)->orderBy('name')->get();

        foreach ($this->devices as $device) {
            $this->counts[$device->id] = 0;
        }

        $current = (int) date('Y');
        $currentMonth = (int) date('n');

        $minYear = Download::min('year');
        $maxYear = Download::max('year');

        if (! $minYear) {
            $minYear = $current - 5;
        }

        if (! $maxYear) {
            $maxYear = $current;
        }

        $maxYear = max($maxYear, $current);

        $minYear = min($minYear, $current - 3);

        for ($y = $maxYear; $y >= $minYear; $y--) {
            $this->years[] = $y;
        }

        if ($this->year >= $current) {
            $maxMonth = $currentMonth;
        } else {
            $maxMonth = 12;
        }

        $this->months = [];
        for ($m = 1; $m <= $maxMonth; $m++) {
            $this->months[] = $m;
        }

        $this->buildDays();
    }

    public function getTotalProperty()
    {
        return array_sum(array_map('intval', $this->counts));
    }

    public function getAverageProperty()
    {
        $n = count($this->counts) ?: 1;
        return (int) round($this->total / $n);
    }

    public function updatedYear()
    {
        $this->year = (int) $this->year;
        $this->buildMonths();
        $this->buildDays();
        $this->loadCounts();
    }

    public function updatedMonth()
    {
        $this->buildDays();
        $this->loadCounts();
    }

    public function updatedDay()
    {
        $this->loadCounts();
    }

    protected function buildMonths()
    {
        $current = (int) date('Y');
        $currentMonth = (int) date('n');

        $selectedYear = (int) $this->year;

        if ($selectedYear >= $current) {
            $maxMonth = $currentMonth;
        } else {
            $maxMonth = 12;
        }

        $this->months = [];
        for ($m = 1; $m <= $maxMonth; $m++) {
            $this->months[] = $m;
        }

        if (! in_array((int) $this->month, $this->months)) {
            $this->month = (int) (end($this->months) ?: $currentMonth);
        }
    }

    protected function buildDays()
    {
        $current = (int) date('Y');
        $currentMonth = (int) date('n');
        $currentDay = (int) date('j');

        $selectedYear = (int) $this->year;
        $selectedMonth = (int) $this->month;

        if ($selectedYear >= $current && $selectedMonth >= $currentMonth) {
            $maxDay = $currentDay;
        } else {
            $maxDay = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);
        }

        $this->days = [];
        for ($d = 1; $d <= $maxDay; $d++) {
            $this->days[] = $d;
        }

        if (! in_array((int) $this->day, $this->days)) {
            $this->day = (int) (end($this->days) ?: $currentDay);
        }
    }

    protected function loadCounts()
    {
        foreach ($this->devices as $device) {
            $this->counts[$device->id] = 0;
        }
    }

    public function save()
    {
        foreach ($this->devices as $device) {
            if (! isset($this->counts[$device->id]) || $this->counts[$device->id] === '') {
                $this->counts[$device->id] = 0;
            }
            $this->counts[$device->id] = (int) $this->counts[$device->id];
        }

        $this->validate();

        DB::transaction(function () {
            foreach ($this->devices as $device) {
                $value = isset($this->counts[$device->id]) ? (int) $this->counts[$device->id] : 0;

                if ($value > 0) {
                    Download::addToMonth($device->id, $this->year, $this->month, $this->day, $value);
                }
            }
        });

        foreach ($this->devices as $device) {
            $this->counts[$device->id] = 0;
        }

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => __('Monthly downloads saved successfully.'),
        ]);

        $this->dispatch('downloads-updated', [
            'year' => $this->year,
            'month' => $this->month,
        ]);

        $this->year = (int) date('Y');
        $this->month = (int) date('n');
        $this->day = (int) date('j');
        $this->buildMonths();
        $this->buildDays();
        $this->loadCounts();

        try { $this->dispatch('close-monthly-report-modal'); } catch (\Exception $e) {}
    }

    public function render()
    {
        $reportDate = \Carbon\Carbon::createFromDate($this->year, $this->month, $this->day)->toDateString();
        $competitorRanking = CompetitorAppRanking::getCompetitorRankingForDate($reportDate);

        return view('livewire.admin.devices.downloads.monthly-downloads-report', [
            'competitorRanking' => $competitorRanking,
            'reportDate' => $reportDate,
        ]);
    }
}
