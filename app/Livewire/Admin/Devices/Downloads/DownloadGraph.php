<?php

namespace App\Livewire\Admin\Devices\Downloads;

use App\Models\Device;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DownloadGraph extends Component
{
    public $selectedYear;

    public $monthlyData = [];

    public $kpis = [];

    public $monthlyDeviceData = [];

    public $devices;

    public $selectedDevice = null;

    public function render()
    {
        return view('livewire.admin.devices.downloads.download-graph');
    }

    public function mount($year = null)
    {
        $this->selectedYear = $year === null
            ? 'all'
            : (strtolower((string) $year) === 'all' ? 'all' : (int) $year);
        try {
            $this->devices = DB::table('devices')
                ->select('id', 'name')
                ->whereNotIn('name', ['Web Client', 'Android Mobile', 'Android TV'])
                ->orderBy('name')
                ->get();
        } catch (\Exception $e) {
            $this->devices = collect();
        }

        $this->loadData();
        $this->loadDeviceData();
    }

    public function loadData()
    {
        $isAllYears = $this->isAllYearsSelected();

        try {
            $rows = DB::table('downloads')
                ->when(! $isAllYears, function ($q) {
                    $q->where('year', (int) $this->selectedYear);
                })
                ->when($this->selectedDevice && $this->selectedDevice !== '', function ($q) {
                    $q->where('device_id', $this->selectedDevice);
                })
                ->selectRaw('month, SUM(`count`) as total')
                ->groupBy('month')
                ->get()
                ->keyBy(function ($item) {
                    return (int) $item->month;
                });
        } catch (\Exception $e) {
            $rows = collect();
        }

        $data = [];
        for ($m = 1; $m <= 12; $m++) {
            $data[] = isset($rows[$m]) ? (int) $rows[$m]->total : 0;
        }

        $this->monthlyData = $data;

        $total = array_sum($data);
        $average = $total ? (int) round($total / 12) : 0;
        $max = $data ? max($data) : 0;
        $topMonth = '—';
        if ($max > 0) {
            $idx = array_search($max, $data);
            $topMonth = date('F', mktime(0, 0, 0, $idx + 1, 1));
        }

        $this->kpis = [
            'total' => $total,
            'average' => $average,
            'top' => ['month' => $topMonth, 'value' => $max],
        ];

        try {
            $this->loadDeviceData();
        } catch (\Exception $e) {
        }

        if (! empty($this->monthlyDeviceData) && is_iterable($this->monthlyDeviceData)) {
            $top = collect($this->monthlyDeviceData)->sortByDesc('total')->first();
            if ($top) {
                $deviceName = $top->name ?? ($top->device_id ?? '—');
                $deviceTotal = isset($top->total) ? (int) $top->total : 0;
                $topDeviceImage = null;
                try {
                    if (! empty($top->device_id)) {
                        $dev = Device::find($top->device_id);
                        if ($dev) {
                            $topDeviceImage = $dev->image ?? null;
                        }
                    }
                } catch (\Exception $e) {
                    $topDeviceImage = null;
                }

                $this->kpis['top_device'] = [
                    'id' => $top->device_id ?? null,
                    'name' => $deviceName ?? '—',
                    'total' => $deviceTotal,
                    'image' => $topDeviceImage,
                ];
            }
        }

        $pie = [0, 0];
        $pieLabels = ['HLS', 'DASH'];
        try {
            $pieRows = DB::table('downloads')
                ->when(! $isAllYears, function ($q) {
                    $q->where('year', (int) $this->selectedYear);
                })
                ->join('devices', 'downloads.device_id', '=', 'devices.id')
                ->whereIn('devices.protocol', ['HLS', 'DASH'])
                ->select('devices.protocol', DB::raw('SUM(downloads.`count`) as total_downloads'))
                ->groupBy('devices.protocol')
                ->get()
                ->keyBy('protocol');

            $pie[0] = isset($pieRows['HLS']) ? (int) $pieRows['HLS']->total_downloads : 0;
            $pie[1] = isset($pieRows['DASH']) ? (int) $pieRows['DASH']->total_downloads : 0;
        } catch (\Exception $e) {
            $pie = [0, 0];
        }

        try {
            $hlsTotal = isset($pieRows['HLS']) ? (int) $pieRows['HLS']->total_downloads : 0;
            $dashTotal = isset($pieRows['DASH']) ? (int) $pieRows['DASH']->total_downloads : 0;
            $totalProtocolDownloads = $hlsTotal + $dashTotal;

            if ($totalProtocolDownloads > 0) {
                $hlsPercent = round(($hlsTotal / $totalProtocolDownloads) * 100, 1);
                $dashPercent = round(($dashTotal / $totalProtocolDownloads) * 100, 1);
            } else {
                $hlsPercent = 0;
                $dashPercent = 0;
            }

            $totalDevicesWithDownloads = DB::table('downloads')
                ->when(! $isAllYears, function ($q) {
                    $q->where('year', (int) $this->selectedYear);
                })
                ->select('downloads.device_id', DB::raw('SUM(downloads.count) as total'))
                ->groupBy('downloads.device_id')
                ->havingRaw('SUM(downloads.count) > 0')
                ->get()
                ->count();
        } catch (\Exception $e) {
            $totalDevicesWithDownloads = 0;
            $hlsPercent = 0;
            $dashPercent = 0;
        }

        $this->kpis = array_merge($this->kpis, [
            'device_protocol_percent' => [
                'HLS' => $hlsPercent ?? 0,
                'DASH' => $dashPercent ?? 0,
                'totalDevices' => $totalDevicesWithDownloads ?? 0,
            ],
        ]);

        $payload = [
            'data' => $this->monthlyData,
            'year' => $isAllYears ? 'all' : (int) $this->selectedYear,
            'kpis' => $this->kpis,
            'pie' => $pie,
            'pieLabels' => $pieLabels,
            'device_name' => null,
            'device_id' => $this->selectedDevice,
            'deviceProtocolPercent' => [
                'HLS' => $hlsPercent ?? 0,
                'DASH' => $dashPercent ?? 0,
                'totalDevices' => $totalDevicesWithDownloads ?? 0,
            ],
            'download_rows' => [],
            'devices' => [],
            'period_labels' => [],
        ];

        if ($this->selectedDevice) {
            try {
                $name = DB::table('devices')->where('id', $this->selectedDevice)->value('name');
                $payload['device_name'] = $name ?: null;
            } catch (\Exception $e) {
                $payload['device_name'] = null;
            }
        }

        try {
            $monthLabels = [];
            for ($m = 1; $m <= 12; $m++) {
                $monthLabels[] = $isAllYears
                    ? date('M', mktime(0, 0, 0, $m, 1))
                    : date('M Y', mktime(0, 0, 0, $m, 1, (int) $this->selectedYear));
            }

            $rows = DB::table('downloads')
                ->selectRaw('downloads.device_id, downloads.month as month, SUM(downloads.count) as total, devices.name')
                ->join('devices', 'downloads.device_id', '=', 'devices.id')
                ->when(! $isAllYears, function ($q) {
                    $q->where('downloads.year', (int) $this->selectedYear);
                })
                ->when($this->selectedDevice && $this->selectedDevice !== '', function ($q) {
                    $q->where('downloads.device_id', $this->selectedDevice);
                })
                ->groupBy('downloads.device_id', 'downloads.month', 'devices.name')
                ->orderBy('devices.name')
                ->get();

            $devices = [];
            foreach ($rows as $r) {
                $did = $r->device_id;
                if (! isset($devices[$did])) {
                    $devices[$did] = [
                        'id' => $did,
                        'name' => $r->name,
                        'image' => $r->image ?? null,
                        'months' => array_fill(0, 12, 0),
                    ];
                }
                $idx = intval($r->month) - 1;
                if ($idx >= 0 && $idx < 12) {
                    $devices[$did]['months'][$idx] = (int) $r->total;
                }
            }

            $devicesList = [];
            foreach ($devices as $dev) {
                $counts = $dev['months'];
                $total = array_sum($counts);
                $avg = count($counts) ? round($total / count($counts), 2) : 0;
                $topIndex = array_search(max($counts), $counts);
                $topLabel = $topIndex !== false
                    ? ($isAllYears
                        ? date('M', mktime(0, 0, 0, $topIndex + 1, 1))
                        : date('M Y', mktime(0, 0, 0, $topIndex + 1, 1, (int) $this->selectedYear)))
                    : null;
                $topValue = $counts[$topIndex] ?? 0;

                $max = max($counts) ?: 1;
                $w = 260;
                $h = 48;
                $pad = 6;
                $pts = [];
                $n = count($counts);
                for ($i = 0; $i < $n; $i++) {
                    $x = $pad + ($i * ($w - $pad * 2) / max(1, $n - 1));
                    $y = $h - $pad - (($counts[$i] / $max) * ($h - $pad * 2));
                    $pts[] = round($x, 1).','.round($y, 1);
                }
                $points = implode(' ', $pts);
                $svg = '<svg width="'.$w.'" height="'.$h.'" xmlns="http://www.w3.org/2000/svg"><polyline fill="none" stroke="#0f6fec" stroke-width="2" points="'.$points.'"/></svg>';

                $devicesList[] = [
                    'id' => $dev['id'],
                    'name' => $dev['name'],
                    'image' => $dev['image'] ?? null,
                    'counts' => $counts,
                    'total' => $total,
                    'average' => $avg,
                    'top_month_label' => $topLabel,
                    'top_month_value' => $topValue,
                    'sparkline' => $svg,
                ];
            }

            $downloadRows = DB::table('downloads')
                ->select([
                    'downloads.id',
                    'downloads.device_id',
                    'devices.name as device_name',
                    'devices.protocol',
                    'devices.area as device_area',
                    'downloads.year',
                    'downloads.month',
                    'downloads.count',
                    'downloads.created_at',
                ])
                ->join('devices', 'downloads.device_id', '=', 'devices.id')
                ->when(! $isAllYears, function ($q) {
                    $q->where('downloads.year', (int) $this->selectedYear);
                })
                ->when($this->selectedDevice && $this->selectedDevice !== '', function ($q) {
                    $q->where('downloads.device_id', $this->selectedDevice);
                })
                ->orderBy('downloads.created_at', 'desc')
                ->get()
                ->map(function ($r) {
                    return [
                        'id' => $r->id,
                        'device_id' => $r->device_id,
                        'device_name' => $r->device_name,
                        'protocol' => $r->protocol,
                        'device_area' => $r->device_area ?? '',
                        'year' => $r->year,
                        'month' => $r->month,
                        'count' => $r->count,
                        'created_at' => isset($r->created_at) ? date('Y-m-d H:i:s', strtotime($r->created_at)) : null,
                    ];
                })->toArray();

            $payload['download_rows'] = $downloadRows;
            $payload['devices'] = $devicesList;
            $payload['period_labels'] = $monthLabels;

            if ($isAllYears) {
                $payload['years'] = collect($downloadRows)
                    ->pluck('year')
                    ->filter(fn ($year) => is_numeric($year) && (int) $year > 0)
                    ->map(fn ($year) => (int) $year)
                    ->unique()
                    ->sortDesc()
                    ->values()
                    ->toArray();
            }

            $groupedByDevice = [];
            foreach ($devicesList as $dev) {
                $monthRows = [];
                foreach ($payload['period_labels'] as $idx => $label) {
                    $monthRows[] = [
                        'label' => $label,
                        'count' => $dev['counts'][$idx] ?? 0,
                    ];
                }
                $groupedByDevice[$dev['id']] = [
                    'id' => $dev['id'],
                    'name' => $dev['name'],
                    'months' => $monthRows,
                    'total' => $dev['total'] ?? array_sum($dev['counts']),
                ];
            }

            $groupedByMonth = [];
            foreach ($payload['period_labels'] as $idx => $label) {
                $devicesForMonth = [];
                $monthTotal = 0;
                foreach ($devicesList as $dev) {
                    $c = $dev['counts'][$idx] ?? 0;
                    $devicesForMonth[] = [
                        'device_id' => $dev['id'],
                        'name' => $dev['name'],
                        'count' => $c,
                    ];
                    $monthTotal += $c;
                }
                $groupedByMonth[] = [
                    'label' => $label,
                    'devices' => $devicesForMonth,
                    'total' => $monthTotal,
                ];
            }

            $payload['grouped_by_device'] = $groupedByDevice;
            $payload['grouped_by_month'] = $groupedByMonth;
        } catch (\Exception $e) {
        }

        try {
            $this->dispatch('downloads-updated', $payload);
        } catch (\Exception $e) {
        }
    }

    public function loadDeviceData()
    {
        $isAllYears = $this->isAllYearsSelected();

        try {
            $this->monthlyDeviceData = DB::table('downloads')
                ->when(! $isAllYears, function ($q) {
                    $q->where('year', (int) $this->selectedYear);
                })
                ->join('devices', 'downloads.device_id', '=', 'devices.id')
                ->select('devices.id as device_id', 'devices.name', DB::raw('SUM(`count`) as total'))
                ->groupBy('devices.id', 'devices.name')
                ->get();
        } catch (\Exception $e) {
            $this->monthlyDeviceData = collect();
        }
    }

    public function updatedSelectedDevice($value)
    {
        $this->selectedDevice = $value === '' ? null : $value;
        $this->loadData();
        $this->loadDeviceData();
    }

    public function updatedSelectedYear($value)
    {
        $this->selectedYear = strtolower((string) $value) === 'all' ? 'all' : (int) $value;
        $this->loadData();
        $this->loadDeviceData();
    }

    private function isAllYearsSelected(): bool
    {
        return strtolower((string) $this->selectedYear) === 'all';
    }
}
