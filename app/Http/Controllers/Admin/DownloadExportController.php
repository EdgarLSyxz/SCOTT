<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\DownloadsExcelMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DownloadExportController extends Controller
{
    private function isAllYearsRequest(Request $request): bool
    {
        $yearInput = strtolower((string) ($request->input('year', $request->query('year', ''))));

        return $request->boolean('all_years') || in_array($yearInput, ['all', 'multi'], true);
    }

    private function parseYearsFromRows(array $downloadRows): array
    {
        $years = [];
        foreach ($downloadRows as $row) {
            $year = (int) ($row['year'] ?? 0);
            if ($year > 0) {
                $years[$year] = true;
            }
        }

        $years = array_map('intval', array_keys($years));
        rsort($years);

        return array_values($years);
    }

    private function buildYearRangeLabel(array $years): string
    {
        if (empty($years)) {
            return (string) date('Y');
        }

        $years = array_map('intval', $years);
        rsort($years);

        if (count($years) === 1) {
            return (string) $years[0];
        }

        return max($years) . ' - ' . min($years);
    }

    private function buildProtocolSummary(array $downloadRows): array
    {
        $protocols = ['HLS', 'DASH'];
        $deviceSets = [
            'HLS' => [],
            'DASH' => [],
        ];
        $downloadTotals = [
            'HLS' => 0,
            'DASH' => 0,
        ];

        foreach ($downloadRows as $row) {
            $protocol = strtoupper(trim((string)($row['protocol'] ?? '')));
            if (!in_array($protocol, $protocols, true)) {
                continue;
            }

            $count = (int)($row['count'] ?? 0);
            if ($count <= 0) {
                continue;
            }

            $downloadTotals[$protocol] += $count;

            $deviceId = $row['device_id'] ?? null;
            if ($deviceId !== null && $deviceId !== '') {
                $deviceSets[$protocol][(string)$deviceId] = true;
            }
        }

        $allDevices = array_unique(array_merge(array_keys($deviceSets['HLS']), array_keys($deviceSets['DASH'])));
        $totalDevices = count($allDevices);
        $totalDownloads = $downloadTotals['HLS'] + $downloadTotals['DASH'];

        $summary = [
            'device_total' => $totalDevices,
            'download_total' => $totalDownloads,
        ];

        foreach ($protocols as $protocol) {
            $deviceCount = count($deviceSets[$protocol]);
            $percent = $totalDownloads ? round(($downloadTotals[$protocol] / $totalDownloads) * 100, 1) : 0;
            $summary[$protocol] = [
                'download_percent' => $percent,
                'device_count' => $deviceCount,
                'download_total' => $downloadTotals[$protocol],
            ];
        }

        return $summary;
    }

    private function buildProtocolSummaryByYear(array $downloadRows, array $years = []): array
    {
        $rowsByYear = [];

        foreach ($downloadRows as $row) {
            $year = (int) ($row['year'] ?? 0);
            if ($year <= 0) {
                continue;
            }

            if (!isset($rowsByYear[$year])) {
                $rowsByYear[$year] = [];
            }

            $rowsByYear[$year][] = $row;
        }

        if (empty($years)) {
            $years = array_map('intval', array_keys($rowsByYear));
        }

        rsort($years);

        $out = [];
        foreach ($years as $year) {
            $year = (int) $year;
            $out[$year] = $this->buildProtocolSummary($rowsByYear[$year] ?? []);
        }

        return $out;
    }

    private function buildProtocolDeviceSummary(array $downloadRows): array
    {
        $protocols = ['HLS', 'DASH'];
        $deviceSets = [
            'HLS' => [],
            'DASH' => [],
        ];

        foreach ($downloadRows as $row) {
            $protocol = strtoupper(trim((string) ($row['protocol'] ?? '')));
            if (!in_array($protocol, $protocols, true)) {
                continue;
            }

            $deviceId = $row['device_id'] ?? null;
            if ($deviceId === null || $deviceId === '') {
                continue;
            }

            $deviceSets[$protocol][(string) $deviceId] = true;
        }

        $allDevices = array_unique(array_merge(array_keys($deviceSets['HLS']), array_keys($deviceSets['DASH'])));
        $totalDevices = count($allDevices);

        $summary = [
            'device_total' => $totalDevices,
        ];

        foreach ($protocols as $protocol) {
            $count = count($deviceSets[$protocol]);
            $summary[$protocol] = [
                'device_total' => $count,
                'device_percent' => $totalDevices ? round(($count / $totalDevices) * 100, 1) : 0,
            ];
        }

        return $summary;
    }

    public function historyCSV(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');

        $query = DB::table('downloads')
            ->select([
                'downloads.id',
                'downloads.device_id',
                'downloads.year',
                'downloads.month',
                'downloads.count',
                'downloads.created_at',
                'devices.name as device_name',
                'devices.protocol',
                'devices.area as device_area',
            ])
            ->join('devices', 'downloads.device_id', '=', 'devices.id')
            ->orderBy('downloads.created_at', 'desc');

        if ($start && $end) {
            try {
                $s = Carbon::createFromFormat('Y-m-d', $start)->startOfDay();
                $e = Carbon::createFromFormat('Y-m-d', $end)->endOfDay();
                $query->whereBetween('downloads.created_at', [$s, $e]);
            } catch (\Throwable $e) {
            }
        }

        $auth = Auth::user();
        if ($auth && $auth->id !== 1) {
            if ($viewerArea = $auth->area) {
                $query->where('devices.area', $viewerArea);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $filename = __('Download History') . ' - ' . now()->format(format: 'dmY His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($query, $start, $end) {
            $handle = fopen('php://output', 'w');
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            $headerRow = ['device_id', 'device_name', 'protocol', 'month', 'year', 'count', 'device_area', 'created_at'];
            fputcsv($handle, $headerRow);

            $query->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $r) {
                    $values = [
                        $r->device_id ?? '—',
                        $r->device_name ?? '—',
                        $r->protocol ?? '—',
                        $r->month ?? '—',
                        $r->year ?? '—',
                        $r->count ?? '—',
                        $r->device_area ?? '—',
                        isset($r->created_at)
                            ? Carbon::parse($r->created_at)->format('Y-m-d H:i:s') . ' UTC-6'
                            : '—',
                    ];
                    fputcsv($handle, $values);
                }
            });

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }

    public function getMonthsByYear(Request $request)
    {
        $year = $request->query('year', date('Y'));
        $deviceId = $request->query('device_id');

        try {
            $auth = Auth::user();
            $areaFilter = null;
            if ($auth && $auth->id !== 1) {
                if ($viewerArea = $auth->area) {
                    $areaFilter = $viewerArea;
                }
            }

            $query = DB::table('downloads')
                ->select('month')
                ->where('year', $year)
                ->when($deviceId, function ($q, $deviceId) {
                    return $q->where('downloads.device_id', $deviceId);
                })
                ->distinct()
                ->orderBy('month');

            if ($areaFilter) {
                $query->join('devices', 'downloads.device_id', '=', 'devices.id')
                    ->where('devices.area', $areaFilter);
            }

            $monthsData = $query->pluck('month')->toArray();

            $monthLabels = [];
            $monthNames = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

            foreach ($monthsData as $m) {
                $monthNumber = intval($m);
                $monthName = $monthNames[$monthNumber - 1] ?? 'Mes ' . $monthNumber;
                $monthLabels[] = [
                    'value' => $monthNumber,
                    'label' => $monthName,
                ];
            }

            return response()->json([
                'months' => $monthLabels,
                'year' => $year,
            ]);
        } catch (\Throwable $e) {
            \Log::error('getMonthsByYear exception', [
                'error' => $e->getMessage(),
                'year' => $year,
            ]);
            return response()->json(['months' => [], 'year' => $year], 200);
        }
    }

    public function historyData(Request $request)
    {
        $allYearsMode = $this->isAllYearsRequest($request);
        $yearInput = $request->query('year', $request->input('year', date('Y')));
        $year = $allYearsMode ? 'all' : $yearInput;
        $deviceId = $request->query('device_id', $request->input('device_id'));
        $month = $request->query('month', $request->input('month'));

        $data = [
            'year' => $year,
            'years' => [],
            'is_multi_year' => $allYearsMode,
            'year_range_label' => null,
            'device_id' => $deviceId,
            'month' => $month,
            'devices' => [],
            'period_labels' => [],
            'download_rows' => [],
            'summary' => [
                'total' => 0,
                'average' => 0,
                'top_month_label' => null,
                'top_month_value' => 0,
            ],
        ];

        try {
            $auth = Auth::user();
            $areaFilter = null;
            if ($auth && $auth->id !== 1) {
                if ($viewerArea = $auth->area) {
                    $areaFilter = $viewerArea;
                } else {
                    \Log::info('historyData: User not admin, no area assigned, returning empty', ['user_id' => $auth->id]);
                    return response()->json($data);
                }
            }

            $years = [];
            $months = [];

            if ($allYearsMode) {
                $yearsQuery = DB::table('downloads')
                    ->select('downloads.year')
                    ->distinct();

                if ($areaFilter) {
                    $yearsQuery->join('devices', 'downloads.device_id', '=', 'devices.id')
                        ->where('devices.area', $areaFilter);
                }

                if ($deviceId) {
                    $yearsQuery->where('downloads.device_id', $deviceId);
                }

                if ($month) {
                    $yearsQuery->where('downloads.month', intval($month));
                }

                $years = $yearsQuery
                    ->orderByDesc('downloads.year')
                    ->pluck('downloads.year')
                    ->map(fn($y) => (int) $y)
                    ->filter(fn($y) => $y > 0)
                    ->values()
                    ->toArray();

                if (empty($years)) {
                    $data['years'] = [];
                    $data['year_range_label'] = null;
                    return response()->json($data);
                }

                $monthsQuery = DB::table('downloads')
                    ->select('downloads.year', 'downloads.month')
                    ->distinct()
                    ->whereIn('downloads.year', $years)
                    ->orderBy('downloads.year')
                    ->orderBy('downloads.month');

                if ($areaFilter) {
                    $monthsQuery->join('devices', 'downloads.device_id', '=', 'devices.id')
                        ->where('devices.area', $areaFilter);
                }

                if ($deviceId) {
                    $monthsQuery->where('downloads.device_id', $deviceId);
                }

                if ($month) {
                    $monthsQuery->where('downloads.month', intval($month));
                }

                $months = $monthsQuery->get()->map(function ($r) {
                    return sprintf('%04d-%02d', (int) $r->year, (int) $r->month);
                })->values()->toArray();
            } else {
                if ($month) {
                    $monthNum = intval($month);
                    $s = Carbon::create($year, $monthNum, 1)->startOfDay();
                    $e = Carbon::create($year, $monthNum, 1)->endOfMonth()->endOfDay();
                } else {
                    $s = Carbon::create($year, 1, 1)->startOfDay();
                    $e = Carbon::create($year, 12, 31)->endOfDay();
                }

                $period = CarbonPeriod::create($s->copy()->startOfMonth(), '1 month', $e->copy()->startOfMonth());
                foreach ($period as $m) {
                    $months[] = $m->format('Y-m');
                }

                $years = [(int) $year];
            }

            $data['years'] = $years;
            $data['year_range_label'] = $this->buildYearRangeLabel($years);

            \Log::info('historyData: Starting query', [
                'year' => $year,
                'years' => $years,
                'month' => $month,
                'device_id' => $deviceId,
                'area_filter' => $areaFilter,
                'user_id' => $auth?->id,
                'is_admin' => $auth?->id === 1,
                'months' => $months,
            ]);

            $rowsQuery = DB::table('downloads')
                ->selectRaw('downloads.device_id, downloads.month as month, downloads.year as year, SUM(downloads.count) as total, devices.name')
                ->join('devices', 'downloads.device_id', '=', 'devices.id');

            if ($allYearsMode) {
                $rowsQuery->whereIn('downloads.year', $years);
            } else {
                $rowsQuery->where('downloads.year', $year);
            }

            if ($month) {
                $rowsQuery->where('downloads.month', intval($month));
            }

            if ($areaFilter) {
                $rowsQuery->where('devices.area', $areaFilter);
            }

            if ($deviceId) {
                $rowsQuery->where('downloads.device_id', $deviceId);
            }

            $rows = $rowsQuery->groupBy('downloads.device_id', 'downloads.month', 'downloads.year', 'devices.name')
                ->orderBy('devices.name')
                ->get();

            \Log::info('historyData: Rows query result', [
                'count' => $rows->count(),
                'sample' => $rows->take(3)->toArray(),
            ]);

            $downloadsQuery = DB::table('downloads')
                ->select([
                    'downloads.id',
                    'downloads.device_id',
                    'devices.name as device_name',
                    'devices.protocol',
                    'devices.area as device_area',
                    'downloads.year',
                    'downloads.month',
                    'downloads.day',
                    'downloads.count',
                    'downloads.created_at',
                ])
                ->join('devices', 'downloads.device_id', '=', 'devices.id')
                ->orderBy('downloads.created_at', 'desc');

            if ($allYearsMode) {
                $downloadsQuery->whereIn('downloads.year', $years);
            } else {
                $downloadsQuery->where('downloads.year', $year);
            }

            if ($month) {
                $downloadsQuery->where('downloads.month', intval($month));
            }

            if ($areaFilter) {
                $downloadsQuery->where('devices.area', $areaFilter);
            }

            if ($deviceId) {
                $downloadsQuery->where('downloads.device_id', $deviceId);
            }

            $downloadRowsCollection = $downloadsQuery->get();

            \Log::info('historyData: Download rows query result', [
                'count' => $downloadRowsCollection->count(),
                'sample' => $downloadRowsCollection->take(3)->toArray(),
            ]);

            $downloadRows = $downloadRowsCollection->map(function ($r) {
                return [
                    'id' => $r->id,
                    'device_id' => $r->device_id,
                    'device_name' => $r->device_name,
                    'protocol' => $r->protocol,
                    'device_area' => $r->device_area ?? '',
                    'year' => $r->year,
                    'month' => $r->month,
                    'day' => $r->day,
                    'count' => $r->count,
                    'created_at' => isset($r->created_at) ? Carbon::parse($r->created_at)->format('Y-m-d H:i:s') : null,
                ];
            })->toArray();

            $data['download_rows'] = $downloadRows;

            $monthIndexMap = [];
            foreach ($months as $idx => $monthKey) {
                $monthIndexMap[$monthKey] = $idx;
            }

            $devices = [];
            foreach ($rows as $r) {
                $did = $r->device_id;
                if (!isset($devices[$did])) {
                    $devices[$did] = [
                        'id' => $did,
                        'name' => $r->name,
                        'image' => $r->image ?? null,
                        'months' => array_fill(0, count($months), 0),
                    ];
                }
                $monthKey = sprintf('%04d-%02d', (int) $r->year, (int) $r->month);
                $index = $monthIndexMap[$monthKey] ?? null;
                if ($index !== null) {
                    $devices[$did]['months'][$index] = (int)$r->total;
                }
            }

            $devicesList = [];
            foreach ($devices as $dev) {
                $counts = $dev['months'];
                $total = array_sum($counts);
                $avg = count($counts) ? round($total / count($counts), 2) : 0;
                $topIndex = array_search(max($counts), $counts);
                $topLabel = null;
                if (isset($months[$topIndex])) {
                    $topLabelRaw = Carbon::createFromFormat('Y-m', $months[$topIndex])->locale('es')->isoFormat('MMM YYYY');
                    $topLabelRaw = preg_replace('/\.$/u', '', $topLabelRaw);
                    $topLabel = mb_convert_case($topLabelRaw, MB_CASE_TITLE, 'UTF-8');
                }
                $topValue = $counts[$topIndex] ?? 0;

                $devicesList[] = [
                    'id' => $dev['id'],
                    'name' => $dev['name'],
                    'image' => $dev['image'] ?? null,
                    'counts' => $counts,
                    'total' => $total,
                    'average' => $avg,
                    'top_month_label' => $topLabel,
                    'top_month_value' => $topValue,
                ];
            }

            foreach ($devicesList as &$dev) {
                $counts = $dev['counts'];
                $max = max($counts) ?: 1;
                $w = 260; $h = 48; $pad = 6;
                $pts = [];
                $n = count($counts);
                for ($i = 0; $i < $n; $i++) {
                    $x = $pad + ($i * ($w - $pad * 2) / max(1, $n - 1));
                    $y = $h - $pad - (($counts[$i] / $max) * ($h - $pad * 2));
                    $pts[] = round($x,1) . ',' . round($y,1);
                }
                $points = implode(' ', $pts);
                $dev['sparkline'] = '<svg width="' . $w . '" height="' . $h . '" xmlns="http://www.w3.org/2000/svg">'
                    . '<polyline fill="none" stroke="#0f6fec" stroke-width="2" points="' . $points . '"/>'
                    . '</svg>';
            }
            unset($dev);

            $overallTotal = array_sum(array_map(fn($d) => $d['total'], $devicesList));
            $monthsCount = count($months) ?: 1;
            $overallAvg = round($overallTotal / $monthsCount, 2);

            $monthlyTotals = array_fill(0, $monthsCount, 0);
            foreach ($devicesList as $d) {
                foreach ($d['counts'] as $i => $c) {
                    $monthlyTotals[$i] += $c;
                }
            }
            $overallTopIndex = array_search(max($monthlyTotals), $monthlyTotals);
            $overallTopLabel = isset($months[$overallTopIndex]) ? Carbon::createFromFormat('Y-m', $months[$overallTopIndex])->format('M Y') : null;
            $overallTopValue = $monthlyTotals[$overallTopIndex] ?? 0;

            $data['devices'] = $devicesList;
            if (empty($deviceId)) {
                $hasWeb = false;
                foreach ($data['devices'] as $d) {
                    if (mb_strtolower($d['name'] ?? '') === mb_strtolower('Web Client')) { $hasWeb = true; break; }
                }
                if (! $hasWeb) {
                    $data['devices'][] = [
                        'id' => null,
                        'name' => 'Web Client',
                        'image' => null,
                        'counts' => array_fill(0, count($months), 0),
                        'total' => 0,
                        'average' => 0,
                        'top_month_label' => null,
                        'top_month_value' => 0,
                        'sparkline' => '',
                        'protocol' => 'WEB',
                        'no_aplica' => true,
                    ];
                }
            }

            $data['period_labels'] = array_map(function ($m) {
                $lbl = Carbon::createFromFormat('Y-m', $m)->locale('es')->isoFormat('MMM YYYY');
                $lbl = preg_replace('/\.$/u', '', $lbl);
                return mb_convert_case($lbl, MB_CASE_TITLE, 'UTF-8');
            }, $months);
            $data['summary'] = [
                'total' => $overallTotal,
                'average' => $overallAvg,
                'top_month_label' => $overallTopLabel,
                'top_month_value' => $overallTopValue,
            ];

            $groupedByDevice = [];
            foreach ($data['devices'] as $dev) {
                $monthRows = [];
                foreach ($data['period_labels'] as $idx => $label) {
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
            foreach ($data['period_labels'] as $idx => $label) {
                $devicesForMonth = [];
                $monthTotal = 0;
                foreach ($data['devices'] as $dev) {
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

            $data['grouped_by_device'] = $groupedByDevice;
            $data['grouped_by_month'] = $groupedByMonth;

            \Log::info('historyData: Final response prepared', [
                'devices_count' => count($data['devices']),
                'download_rows_count' => count($data['download_rows']),
                'summary_total' => $data['summary']['total'],
                'is_multi_year' => $data['is_multi_year'],
            ]);
        } catch (\Throwable $e) {
            \Log::error('historyData exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $data['devices'] = [];
            $data['period_labels'] = [];
        }

        return response()->json($data);
    }

    public function historyPDF(Request $request)
    {
        $charts = $request->input('charts', []);
        $chartsByYearInput = $request->input('charts_by_year', []);
        $allYearsMode = $this->isAllYearsRequest($request);
        $year = $allYearsMode ? 'all' : $request->input('year', date('Y'));
        $deviceId = $request->input('device_id');

        $monthly = $charts['monthly'] ?? null;
        $pie = $charts['pie'] ?? null;

        $data = [
            'monthlyImage' => $monthly,
            'pieImage' => $pie,
            'charts_by_year' => is_array($chartsByYearInput) ? $chartsByYearInput : [],
            'chart_global_bar' => $request->input('chart_global_bar'),
            'protocol_summary_by_year' => [],
            'protocol_device_summary_global' => null,
            'year' => $year,
            'years' => [],
            'is_multi_year' => $allYearsMode,
            'year_range_label' => null,
            'device_id' => $deviceId,
            'devices' => [],
            'period_labels' => [],
            'download_rows' => [],
            'summary' => [
                'total' => 0,
                'average' => 0,
                'top_month_label' => null,
                'top_month_value' => 0,
            ],
        ];

        $prefetched = $request->input('data');
        if (!$prefetched && $allYearsMode) {
            try {
                $prefetchedResponse = $this->historyData($request);
                $prefetched = $prefetchedResponse->getContent();
            } catch (\Throwable $_e) {
            }
        }

        if ($prefetched) {
            try {
                $pd = is_string($prefetched) ? json_decode($prefetched, true) : $prefetched;
                if (is_array($pd)) {
                    $data['download_rows'] = $pd['download_rows'] ?? [];
                    $data['devices'] = $pd['devices'] ?? [];
                    $data['period_labels'] = $pd['period_labels'] ?? [];
                    $data['summary'] = $pd['summary'] ?? $data['summary'];
                    $data['is_multi_year'] = !empty($pd['is_multi_year']) || $allYearsMode;
                    $data['years'] = $pd['years'] ?? $this->parseYearsFromRows($data['download_rows'] ?? []);
                    $data['year_range_label'] = $pd['year_range_label'] ?? $this->buildYearRangeLabel($data['years']);

                    if ($data['is_multi_year']) {
                        $data['year'] = 'all';
                    }

                    \Log::info('PDF prefetched data received', [
                        'year' => $year,
                        'device_id' => $deviceId,
                        'download_rows_count' => count($data['download_rows']),
                        'devices_count' => count($data['devices']),
                        'has_summary' => !empty($data['summary']),
                        'has_period_labels' => !empty($data['period_labels']),
                    ]);
                    if (empty($deviceId)) {
                        $hasWeb = false;
                        foreach ($data['devices'] as $d) {
                            if (mb_strtolower($d['name'] ?? '') === mb_strtolower('Web Client')) { $hasWeb = true; break; }
                        }
                        if (! $hasWeb) {
                            $monthsCount = count($data['period_labels'] ?? []);
                            $data['devices'][] = [
                                'id' => null,
                                'name' => 'Web Client',
                                'protocol' => 'WEB',
                                'image' => null,
                                'counts' => array_fill(0, $monthsCount, 0),
                                'total' => 0,
                                'average' => 0,
                                'top_month_label' => null,
                                'top_month_value' => 0,
                                'sparkline' => '',
                                'no_aplica' => true,
                            ];
                        }
                    } else {
                        $data['devices'] = array_values(array_filter($data['devices'] ?? [], function ($d) {
                            return mb_strtolower($d['name'] ?? '') !== mb_strtolower('Web Client');
                        }));
                        \Log::debug('historyPDF: Removed Web Client from prefetched devices because deviceId was provided', ['device_id' => $deviceId]);
                    }

                    $data['protocol_summary'] = $this->buildProtocolSummary($data['download_rows'] ?? []);
                    $data['protocol_summary_by_year'] = $this->buildProtocolSummaryByYear(
                        $data['download_rows'] ?? [],
                        $data['years'] ?? []
                    );
                    $data['protocol_device_summary_global'] = $this->buildProtocolDeviceSummary($data['download_rows'] ?? []);
                }
            } catch (\Throwable $_e) {
                \Log::error('PDF prefetch JSON decode error', ['error' => $_e->getMessage()]);
            }
        } else {
            try {
                $monthsWithRecords = DB::table('downloads')
                    ->select('month')
                    ->where('downloads.year', $year)
                    ->distinct()
                    ->pluck('month')
                    ->toArray();

                $monthsWithRecords = array_map('intval', $monthsWithRecords);
                sort($monthsWithRecords);

                $months = [];
                foreach ($monthsWithRecords as $month) {
                    $months[] = Carbon::create($year, $month, 1)->format('Y-m');
                }

                $rows = DB::table('downloads')
                    ->selectRaw('downloads.device_id, downloads.month as month, downloads.year as year, SUM(downloads.count) as total, devices.name, devices.protocol')
                    ->join('devices', 'downloads.device_id', '=', 'devices.id')
                    ->where('downloads.year', $year)
                    ->whereIn('downloads.month', $monthsWithRecords)
                    ->groupBy('downloads.device_id', 'downloads.month', 'downloads.year', 'devices.name', 'devices.protocol')
                    ->orderBy('devices.name')
                    ->get();

                $downloadsQuery = DB::table('downloads')
                    ->select([
                        'downloads.id',
                        'downloads.device_id',
                        'devices.name as device_name',
                        'devices.protocol',
                        'devices.area as device_area',
                        'downloads.year',
                        'downloads.month',
                        'downloads.day',
                        'downloads.count',
                        'downloads.created_at',
                    ])
                    ->join('devices', 'downloads.device_id', '=', 'devices.id')
                    ->where('downloads.year', $year)
                    ->orderBy('downloads.created_at', 'desc');

                $auth = Auth::user();
                if ($auth && $auth->id !== 1) {
                    if ($viewerArea = $auth->area) {
                        $downloadsQuery->where('devices.area', $viewerArea);
                    } else {
                        $downloadsQuery->whereRaw('1 = 0');
                    }
                }

                if ($deviceId) {
                    $downloadsQuery->where('downloads.device_id', $deviceId);
                }

                $downloadRows = $downloadsQuery->get()->map(function ($r) {
                    return [
                        'id' => $r->id,
                        'device_id' => $r->device_id,
                        'device_name' => $r->device_name,
                        'protocol' => $r->protocol,
                        'device_area' => $r->device_area ?? '',
                        'year' => $r->year,
                        'month' => $r->month,
                        'day' => $r->day,
                        'count' => $r->count,
                        'created_at' => isset($r->created_at) ? Carbon::parse($r->created_at)->format('Y-m-d H:i:s') : null,
                    ];
                })->toArray();

                $data['download_rows'] = $downloadRows;
                $data['protocol_summary'] = $this->buildProtocolSummary($downloadRows);
                $data['protocol_summary_by_year'] = $this->buildProtocolSummaryByYear($downloadRows, [(int) $year]);
                $data['protocol_device_summary_global'] = $this->buildProtocolDeviceSummary($downloadRows);

                $devices = [];
                foreach ($rows as $r) {
                    $did = $r->device_id;
                    if (!isset($devices[$did])) {
                        $devices[$did] = [
                            'id' => $did,
                            'name' => $r->name,
                            'protocol' => $r->protocol ?? '',
                            'image' => $r->image ?? null,
                            'months' => array_fill(0, count($months), 0),
                        ];
                    }
                    $monthNum = intval($r->month);
                    $index = $monthNum - 1;
                    if ($index >= 0 && $index < count($months)) {
                        $devices[$did]['months'][$index] = (int)$r->total;
                    }
                }

                $devicesList = [];
                foreach ($devices as $dev) {
                    $counts = $dev['months'];
                    $max = max($counts) ?: 1;
                    $w = 260; $h = 48; $pad = 6;
                    $pts = [];
                    $n = count($counts);
                    for ($i = 0; $i < $n; $i++) {
                        $x = $pad + ($i * ($w - $pad * 2) / max(1, $n - 1));
                        $y = $h - $pad - (($counts[$i] / $max) * ($h - $pad * 2));
                        $pts[] = round($x,1) . ',' . round($y,1);
                    }
                    $points = implode(' ', $pts);
                    $svg = '<svg width="' . $w . '" height="' . $h . '" xmlns="http://www.w3.org/2000/svg">'
                        . '<polyline fill="none" stroke="#0f6fec" stroke-width="2" points="' . $points . '"/>'
                        . '</svg>';

                    $total = array_sum($counts);
                    $avg = $n ? round($total / $n, 2) : 0;
                    $topIndex = array_search(max($counts), $counts);
                    $topLabel = null;
                    if (isset($months[$topIndex])) {
                        $topLabelRaw = Carbon::createFromFormat('Y-m', $months[$topIndex])->locale('es')->isoFormat('MMM YYYY');
                        $topLabelRaw = preg_replace('/\.$/u', '', $topLabelRaw);
                        $topLabel = mb_convert_case($topLabelRaw, MB_CASE_TITLE, 'UTF-8');
                    }
                    $topValue = $counts[$topIndex] ?? 0;

                    $devicesList[] = [
                        'id' => $dev['id'],
                        'name' => $dev['name'],
                        'protocol' => $dev['protocol'] ?? '',
                        'image' => $dev['image'] ?? null,
                        'counts' => $counts,
                        'total' => $total,
                        'average' => $avg,
                        'top_month_label' => $topLabel,
                        'top_month_value' => $topValue,
                        'sparkline' => $svg,
                    ];
                }

                $grouped = [];
                foreach ($devicesList as $d) {
                    $p = trim(strtoupper((string)($d['protocol'] ?? '')));
                    if ($p === '') $p = 'UNKNOWN';
                    $grouped[$p][] = $d;
                }

                $protocols = array_keys($grouped);
                natcasesort($protocols);
                $protocols = array_values($protocols);
                $ordered = [];
                if (in_array('HLS', $protocols, true)) { $ordered[] = 'HLS'; }
                if (in_array('DASH', $protocols, true)) { $ordered[] = 'DASH'; }
                foreach ($protocols as $p) {
                    if ($p === 'HLS' || $p === 'DASH') continue;
                    $ordered[] = $p;
                }

                $orderedDevices = [];
                foreach ($ordered as $p) {
                    foreach ($grouped[$p] as $dev) {
                        $orderedDevices[] = $dev;
                    }
                }

                if (empty($deviceId)) {
                    $webClientIndex = null;
                    foreach ($orderedDevices as $i => $d) {
                        if (mb_strtolower($d['name'] ?? '') === mb_strtolower('Web Client')) {
                            $webClientIndex = $i; break;
                        }
                    }
                    if ($webClientIndex !== null) {
                        $web = $orderedDevices[$webClientIndex];
                        unset($orderedDevices[$webClientIndex]);
                        $orderedDevices = array_values($orderedDevices);
                        $web['no_aplica'] = true;
                        $web['counts'] = [];
                        $web['total'] = 0;
                        $orderedDevices[] = $web;
                    } else {
                        $orderedDevices[] = [
                            'id' => null,
                            'name' => 'Web Client',
                            'protocol' => 'WEB',
                            'image' => null,
                            'counts' => [],
                            'total' => 0,
                            'average' => 0,
                            'top_month_label' => null,
                            'top_month_value' => 0,
                            'sparkline' => '',
                            'no_aplica' => true,
                        ];
                    }
                }

                $devicesList = $orderedDevices;

                $overallTotal = array_sum(array_map(fn($d) => $d['total'], $devicesList));
                $monthsCount = count($months) ?: 1;
                $overallAvg = round($overallTotal / $monthsCount, 2);

                $monthlyTotals = array_fill(0, $monthsCount, 0);
                foreach ($devicesList as $d) {
                    foreach ($d['counts'] as $i => $c) {
                        $monthlyTotals[$i] += $c;
                    }
                }
                $overallTopIndex = array_search(max($monthlyTotals), $monthlyTotals);
                $overallTopLabel = isset($months[$overallTopIndex]) ? Carbon::createFromFormat('Y-m', $months[$overallTopIndex])->format('M Y') : null;
                $overallTopValue = $monthlyTotals[$overallTopIndex] ?? 0;

                $data['devices'] = $devicesList;
                $data['period_labels'] = array_map(function ($m) {
                    $lbl = Carbon::createFromFormat('Y-m', $m)->locale('es')->isoFormat('MMM YYYY');
                    $lbl = preg_replace('/\.$/u', '', $lbl);
                    return mb_convert_case($lbl, MB_CASE_TITLE, 'UTF-8');
                }, $months);
                $data['summary'] = [
                    'total' => $overallTotal,
                    'average' => $overallAvg,
                    'top_month_label' => $overallTopLabel,
                    'top_month_value' => $overallTopValue,
                ];
                $data['is_multi_year'] = false;
                $data['years'] = [(int) $year];
                $data['year_range_label'] = $this->buildYearRangeLabel($data['years']);

                try {
                    \Log::debug('PDF export data', [
                        'year' => $year,
                        'device_id' => $deviceId,
                        'download_rows_count' => count($downloadRows),
                        'devices_count' => count($devicesList),
                        'months' => $months,
                    ]);

                    \Log::debug('PDF export sample rows', [
                        'download_rows_sample' => array_slice($downloadRows, 0, 8),
                        'devices_sample' => array_slice($devicesList, 0, 8),
                    ]);
                } catch (\Throwable $_logEx) {
                }
            } catch (\Throwable $e) {
                $data['devices'] = [];
                $data['period_labels'] = [];
            }
        }

        try {
            $logoPath = public_path('img/startv-stream-logo.png');
            if ($logoPath && file_exists($logoPath)) {
                $type = pathinfo($logoPath, PATHINFO_EXTENSION) ?: 'png';
                $contents = @file_get_contents($logoPath);
                if ($contents !== false) {
                    $data['logo'] = 'data:image/' . $type . ';base64,' . base64_encode($contents);
                } else {
                    $data['logo'] = null;
                }
            } else {
                $data['logo'] = null;
            }
        } catch (\Throwable $_e) {
            $data['logo'] = null;
        }

        \Log::info('historyPDF: Data before view render', [
            'download_rows_count' => count($data['download_rows'] ?? []),
            'download_rows_sample_first' => !empty($data['download_rows']) ? $data['download_rows'][0] : null,
            'devices_count' => count($data['devices'] ?? []),
            'has_download_rows' => !empty($data['download_rows']),
        ]);

        $html = view('admin.devices.monthly-downloads.download-history', $data)->render();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();

        $pdf = $dompdf->output();
        $filename = __('Download History') . ' - ' . now()->format(format: 'dmY His') . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function historyEmail(Request $request)
    {
        $auth = Auth::user();
        if (! $auth) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $prefetched = $request->input('data');
        if ($prefetched) {
            $pd = is_string($prefetched) ? json_decode($prefetched, true) : $prefetched;
        } else {
            $resp = $this->historyData($request);
            $pd = json_decode($resp->getContent(), true);
        }

        if (!is_array($pd)) {
            return response()->json(['message' => 'Invalid data'], 422);
        }

        $allYearsMode = $this->isAllYearsRequest($request) || !empty($pd['is_multi_year']);
        if ($allYearsMode) {
            $pd['is_multi_year'] = true;
            $pd['years'] = $pd['years'] ?? $this->parseYearsFromRows($pd['download_rows'] ?? []);
            $pd['year_range_label'] = $pd['year_range_label'] ?? $this->buildYearRangeLabel($pd['years']);
        }

        $deviceIdParam = $request->input('device_id') ?? $pd['device_id'] ?? null;
        if (empty($deviceIdParam)) {
            $hasWeb = false;
            foreach ($pd['devices'] ?? [] as $d) {
                if (mb_strtolower($d['name'] ?? '') === mb_strtolower('Web Client')) { $hasWeb = true; break; }
            }
            if (! $hasWeb) {
                $monthsCount = count($pd['period_labels'] ?? []);
                $pd['devices'][] = [
                    'id' => null,
                    'name' => 'Web Client',
                    'protocol' => 'WEB',
                    'image' => null,
                    'counts' => array_fill(0, $monthsCount, 0),
                    'total' => 0,
                    'average' => 0,
                    'top_month_label' => null,
                    'top_month_value' => 0,
                    'sparkline' => '',
                    'no_aplica' => true,
                ];
            }
        } else {
            $pd['devices'] = array_values(array_filter($pd['devices'] ?? [], function ($d) {
                return mb_strtolower($d['name'] ?? '') !== mb_strtolower('Web Client');
            }));
            \Log::debug('historyEmail: Removed Web Client from prefetched devices because device_id param is provided', ['device_id' => $deviceIdParam]);
        }

        $monthly = $request->input('charts.monthly');
        $pie = $request->input('charts.pie');

        $monthValue = $pd['month'] ?? $request->input('month');
        if ($monthValue !== null && $monthValue !== '' && trim((string)$monthValue) !== '') {
            $monthInt = intval($monthValue);
            if ($monthInt >= 1 && $monthInt <= 12) {
                $monthValue = $monthInt;
            } else {
                $monthValue = null;
            }
        } else {
            $monthValue = null;
        }

        if ($allYearsMode) {
            $monthValue = null;
        }

        $pdfData = [
            'monthlyImage' => $monthly,
            'pieImage' => $pie,
            'charts_by_year' => is_array($request->input('charts_by_year', [])) ? $request->input('charts_by_year', []) : [],
            'chart_global_bar' => $request->input('chart_global_bar'),
            'year' => $allYearsMode ? 'all' : ($pd['year'] ?? $request->input('year') ?? date('Y')),
            'years' => $pd['years'] ?? [],
            'is_multi_year' => $allYearsMode,
            'year_range_label' => $pd['year_range_label'] ?? null,
            'month' => $monthValue,
            'device_id' => $deviceIdParam,
            'devices' => $pd['devices'] ?? [],
            'period_labels' => $pd['period_labels'] ?? [],
            'download_rows' => $pd['download_rows'] ?? [],
            'summary' => $pd['summary'] ?? [
                'total' => 0,
                'average' => 0,
                'top_month_label' => null,
                'top_month_value' => 0,
            ],
            'protocol_device_summary_global' => null,
        ];

        if (!empty($pdfData['month'])) {
            $deviceIdFilter = $pdfData['device_id'] ?? null;

            if (!empty($deviceIdFilter)) {
                $daysWithData = [];
                $totalDownloads = 0;

                foreach (($pdfData['download_rows'] ?? []) as $row) {
                    $day = (int)($row['day'] ?? 0);
                    $count = (int)($row['count'] ?? 0);

                    if ($day > 0) {
                        $daysWithData[$day] = true;
                    }
                    $totalDownloads += $count;
                }

                $daysCount = count($daysWithData);
                $averagePerDay = $daysCount > 0 ? round($totalDownloads / $daysCount, 2) : 0;

                $dayTotals = [];
                foreach (($pdfData['download_rows'] ?? []) as $row) {
                    $day = (int)($row['day'] ?? 0);
                    $count = (int)($row['count'] ?? 0);
                    if ($day > 0) {
                        if (!isset($dayTotals[$day])) {
                            $dayTotals[$day] = 0;
                        }
                        $dayTotals[$day] += $count;
                    }
                }

                $topDay = 0;
                $topDayValue = 0;
                if (!empty($dayTotals)) {
                    arsort($dayTotals);
                    $topDay = (int)array_key_first($dayTotals);
                    $topDayValue = (int)reset($dayTotals);
                }

                $pdfData['summary']['total'] = $totalDownloads;
                $pdfData['summary']['average'] = $averagePerDay;
                $pdfData['summary']['top_month_label'] = $topDay > 0 ? 'Día ' . $topDay : '—';
                $pdfData['summary']['top_month_value'] = $topDayValue;
                $pdfData['summary']['is_monthly_email'] = true;
                $pdfData['summary']['is_device_month_mode'] = true;

            } else {
                $deviceTotals = [];
                foreach (($pdfData['download_rows'] ?? []) as $row) {
                    $deviceName = trim((string)($row['device_name'] ?? ''));
                    if ($deviceName === '') {
                        continue;
                    }
                    $count = (int)($row['count'] ?? 0);
                    if (!isset($deviceTotals[$deviceName])) {
                        $deviceTotals[$deviceName] = 0;
                    }
                    $deviceTotals[$deviceName] += $count;
                }

                $totalDownloads = array_sum($deviceTotals);
                $devicesWithData = count($deviceTotals);
                $averagePerDevice = $devicesWithData > 0 ? round($totalDownloads / $devicesWithData, 2) : 0;

                $topDeviceName = '—';
                $topDeviceValue = 0;
                if (!empty($deviceTotals)) {
                    arsort($deviceTotals);
                    $topDeviceName = (string)array_key_first($deviceTotals);
                    $topDeviceValue = (int)reset($deviceTotals);
                }

                $pdfData['summary']['total'] = $totalDownloads;
                $pdfData['summary']['average'] = $averagePerDevice;
                $pdfData['summary']['top_month_label'] = $topDeviceName;
                $pdfData['summary']['top_month_value'] = $topDeviceValue;
                $pdfData['summary']['is_monthly_email'] = true;
                $pdfData['summary']['is_device_month_mode'] = false;
            }
        }

        $pdfData['protocol_summary'] = $this->buildProtocolSummary($pdfData['download_rows'] ?? []);
        $pdfData['protocol_summary_by_year'] = $this->buildProtocolSummaryByYear(
            $pdfData['download_rows'] ?? [],
            $pdfData['years'] ?? []
        );
        $pdfData['protocol_device_summary_global'] = $this->buildProtocolDeviceSummary($pdfData['download_rows'] ?? []);

        \Log::info('historyEmail: PDF data before render', [
            'download_rows_count' => count($pdfData['download_rows'] ?? []),
            'download_rows_sample_first' => !empty($pdfData['download_rows']) ? $pdfData['download_rows'][0] : null,
            'devices_count' => count($pdfData['devices'] ?? []),
            'has_download_rows' => !empty($pdfData['download_rows']),
            'has_monthly_image' => !empty($pdfData['monthlyImage']),
            'has_pie_image' => !empty($pdfData['pieImage']),
        ]);

        try {
            $logoPath = public_path('img/startv-stream-logo.png');
            if ($logoPath && file_exists($logoPath)) {
                $type = pathinfo($logoPath, PATHINFO_EXTENSION) ?: 'png';
                $contents = @file_get_contents($logoPath);
                $pdfData['logo'] = $contents !== false
                    ? 'data:image/' . $type . ';base64,' . base64_encode($contents)
                    : null;
            } else {
                $pdfData['logo'] = null;
            }
        } catch (\Throwable $_e) {
            $pdfData['logo'] = null;
        }

        $pdfHtml = view('admin.devices.monthly-downloads.download-history', $pdfData)->render();
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($pdfHtml);
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();
        $pdfBytes = $dompdf->output();
        $pdfFilename = __('Download History') . ' - ' . now()->format(format: 'dmY His') . '.pdf';

        try {
            $spreadsheet = new Spreadsheet();
            $year = $pd['year'] ?? $request->input('year') ?? date('Y');

            $month = $pdfData['month'] ?? null;

            $deviceId = $request->input('device_id') ?? $pd['device_id'] ?? null;

            $monthNames = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

            $auth = Auth::user();
            $areaFilter = null;
            if ($auth && $auth->id !== 1) {
                if ($viewerArea = $auth->area) {
                    $areaFilter = $viewerArea;
                }
            }

            if ($allYearsMode) {
                $downloadRowsForExcel = collect($pdfData['download_rows'] ?? []);
                $yearsForExcel = $pdfData['years'] ?? $this->parseYearsFromRows($downloadRowsForExcel->toArray());

                if (empty($yearsForExcel)) {
                    $yearsForExcel = [(int) date('Y')];
                }

                $sheetCounter = 0;
                foreach ($yearsForExcel as $yr) {
                    $sheet = $sheetCounter === 0 ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
                    $sheet->setTitle(substr((string) $yr, 0, 31));

                    $sheet->setCellValue('A1', 'Fecha');
                    $sheet->setCellValue('B1', 'Mes');
                    $sheet->setCellValue('C1', 'Día');
                    $sheet->setCellValue('D1', 'Dispositivo');
                    $sheet->setCellValue('E1', 'Protocolo');
                    $sheet->setCellValue('F1', 'Descargas');
                    $sheet->getStyle('A1:F1')->getFont()->setBold(true);
                    $sheet->getColumnDimension('A')->setWidth(16);
                    $sheet->getColumnDimension('B')->setWidth(14);
                    $sheet->getColumnDimension('C')->setWidth(10);
                    $sheet->getColumnDimension('D')->setWidth(42);
                    $sheet->getColumnDimension('E')->setWidth(14);
                    $sheet->getColumnDimension('F')->setWidth(14);

                    $yearRows = $downloadRowsForExcel->filter(function ($r) use ($yr, $deviceId) {
                        if ((int) ($r['year'] ?? 0) !== (int) $yr) {
                            return false;
                        }

                        if (!empty($deviceId) && (string) ($r['device_id'] ?? '') !== (string) $deviceId) {
                            return false;
                        }

                        return true;
                    });

                    $groupedRows = [];
                    foreach ($yearRows as $r) {
                        $monthN = (int) ($r['month'] ?? 0);
                        $dayN = (int) ($r['day'] ?? 0);
                        $deviceName = (string) ($r['device_name'] ?? '—');
                        $protocol = (string) ($r['protocol'] ?? '—');
                        $count = (int) ($r['count'] ?? 0);
                        $dateStr = sprintf('%04d-%02d-%02d', (int) $yr, max(1, $monthN), max(1, $dayN));
                        $key = $dateStr . '|' . $deviceName;

                        if (!isset($groupedRows[$key])) {
                            $groupedRows[$key] = [
                                'date' => $dateStr,
                                'month' => $monthN,
                                'day' => $dayN,
                                'device_name' => $deviceName,
                                'protocol' => $protocol,
                                'count' => 0,
                            ];
                        }

                        $groupedRows[$key]['count'] += $count;
                    }

                    $groupedRows = array_values($groupedRows);
                    usort($groupedRows, function ($a, $b) {
                        $dateCmp = strcmp((string) ($a['date'] ?? ''), (string) ($b['date'] ?? ''));
                        if ($dateCmp !== 0) {
                            return $dateCmp;
                        }

                        return strcmp((string) ($a['device_name'] ?? ''), (string) ($b['device_name'] ?? ''));
                    });

                    $row = 2;
                    foreach ($groupedRows as $line) {
                        $sheet->setCellValue("A{$row}", $line['date']);
                        $sheet->setCellValue("B{$row}", $line['month'] > 0 ? ($monthNames[$line['month'] - 1] ?? $line['month']) : '—');
                        $sheet->setCellValue("C{$row}", $line['day'] > 0 ? $line['day'] : '—');
                        $sheet->setCellValue("D{$row}", $line['device_name']);
                        $sheet->setCellValue("E{$row}", $line['protocol'] ?: '—');
                        $sheet->setCellValue("F{$row}", $line['count']);
                        $row++;
                    }

                    if ($row === 2) {
                        $sheet->setCellValue('A2', 'Sin datos para este año');
                    }

                    $sheetCounter++;
                }

                $spreadsheet->setActiveSheetIndex(0);
            } else {

                $monthsWithData = DB::table('downloads')
                    ->select('month')
                    ->where('downloads.year', $year)
                    ->distinct()
                    ->pluck('month')
                    ->toArray();

            if ($month) {
                $monthsWithData = array_filter($monthsWithData, function($m) use ($month) {
                    return intval($m) === intval($month);
                });
            }

            if ($areaFilter) {
                $monthsWithData = DB::table('downloads')
                    ->join('devices', 'downloads.device_id', '=', 'devices.id')
                    ->select('downloads.month')
                    ->where('downloads.year', $year)
                    ->where('devices.area', $areaFilter)
                    ->distinct()
                    ->pluck('downloads.month')
                    ->toArray();

                if ($month) {
                    $monthsWithData = array_filter($monthsWithData, function($m) use ($month) {
                        return intval($m) === intval($month);
                    });
                }
            }

            if ($deviceId) {
                $monthsWithData = DB::table('downloads')
                    ->select('month')
                    ->where('downloads.year', $year)
                    ->where('downloads.device_id', $deviceId)
                    ->distinct()
                    ->pluck('month')
                    ->toArray();

                if ($month) {
                    $monthsWithData = array_filter($monthsWithData, function($m) use ($month) {
                        return intval($m) === intval($month);
                    });
                }
            }

                $monthsWithData = array_map('intval', $monthsWithData);
                sort($monthsWithData);

                $firstSheet = true;
                foreach ($monthsWithData as $mth) {
                    if ($firstSheet) {
                        $sheet = $spreadsheet->getActiveSheet();
                        $firstSheet = false;
                    } else {
                        $sheet = $spreadsheet->createSheet();
                    }

                    $sheet->setTitle($monthNames[$mth - 1]);
                    $sheet->setCellValue('A1', 'Día');
                    $sheet->setCellValue('B1', 'Dispositivo');
                    $sheet->setCellValue('C1', 'Descargas');
                    $sheet->getColumnDimension('A')->setWidth(15);
                    $sheet->getColumnDimension('B')->setWidth(40);
                    $sheet->getColumnDimension('C')->setWidth(15);
                    $sheet->getStyle('A1:C1')->getFont()->setBold(true);

                    $daysInMonth = Carbon::create($year, $mth, 1)->daysInMonth;

                    $query = DB::table('downloads')
                        ->selectRaw('downloads.day, devices.name as device_name, SUM(downloads.count) as total')
                        ->join('devices', 'downloads.device_id', '=', 'devices.id')
                        ->where('downloads.year', $year)
                        ->where('downloads.month', $mth);

                    if ($areaFilter) {
                        $query->where('devices.area', $areaFilter);
                    }

                    if ($deviceId) {
                        $query->where('downloads.device_id', $deviceId);
                    }

                    $dailyDownloads = $query->groupBy('downloads.day', 'devices.name', 'downloads.device_id')
                        ->orderBy('downloads.day', 'asc')
                        ->orderBy('devices.name', 'asc')
                        ->get();

                    $downloadsByDay = [];
                    foreach ($dailyDownloads as $download) {
                        $day = (int)$download->day;
                        $deviceName = $download->device_name ?? '—';

                        if (!isset($downloadsByDay[$day])) {
                            $downloadsByDay[$day] = [];
                        }

                        if (!isset($downloadsByDay[$day][$deviceName])) {
                            $downloadsByDay[$day][$deviceName] = 0;
                        }

                        $downloadsByDay[$day][$deviceName] += (int)($download->total ?? 0);
                    }

                    $row = 2;
                    for ($day = 1; $day <= $daysInMonth; $day++) {
                        $dateStr = sprintf('%04d-%02d-%02d', $year, $mth, $day);

                        if (isset($downloadsByDay[$day]) && count($downloadsByDay[$day]) > 0) {
                            foreach ($downloadsByDay[$day] as $deviceName => $total) {
                                $sheet->setCellValue("A{$row}", $dateStr);
                                $sheet->setCellValue("B{$row}", $deviceName);
                                $sheet->setCellValue("C{$row}", $total);
                                $row++;
                            }

                            if (empty($deviceId)) {
                                $sheet->setCellValue("A{$row}", $dateStr);
                                $sheet->setCellValue("B{$row}", 'Web Client');
                                $sheet->setCellValue("C{$row}", __('Not applicable'));
                                $row++;
                            }
                        }
                    }
                }
            }

            $writer = new Xlsx($spreadsheet);
            ob_start();
            $writer->save('php://output');
            $xlsData = ob_get_clean();

            $filename = 'Download History - ' . now()->format('Ymd His') . '.xlsx';

            $to = $auth->email ?? config('mail.from.address');
            $subject = __('Download History') . ' - ' . ($allYearsMode ? ($pdfData['year_range_label'] ?? __('Multiple years')) : ($pd['year'] ?? date('Y')));
            $body = '';

            $meta = [];
            $meta['year'] = $allYearsMode ? ($pdfData['year_range_label'] ?? __('Multiple years')) : ($pd['year'] ?? $request->input('year') ?? date('Y'));
            $meta['is_multi_year'] = $allYearsMode;

            if ($month !== null && $month >= 1 && $month <= 12) {
                $meta['year'] = $monthNames[$month - 1] . ' ' . $year;
            }

            $deviceId = $request->input('device_id') ?? $pd['device_id'] ?? null;
            if ($deviceId) {
                $meta['device_id'] = $deviceId;
                try {
                    $devName = DB::table('devices')->where('id', $deviceId)->value('name');
                    $meta['device_name'] = $devName ?: null;
                } catch (\Throwable $_e) { }
            }
            $meta['devices_count'] = is_array($pd['devices'] ?? null) ? count($pd['devices']) : 0;
            $meta['selected_all'] = empty($deviceId);
            if ($request->filled('title')) $meta['title'] = $request->input('title');
            if ($request->filled('description')) $meta['description'] = $request->input('description');

            Mail::to($to)->send(new DownloadsExcelMail(
                $subject,
                $body,
                $xlsData,
                $filename,
                $meta,
                $pdfBytes,
                $pdfFilename
            ));

            session()->flash('swal', [
                'icon' => 'success',
                'title' => __('Well done!'),
                'text' => __('Email sent successfully.')
            ]);

            return response()->json(['message' => __('Email sent successfully.')]);
        } catch (\Throwable $e) {
            \Log::error('historyEmail error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            try {
                $csv = fopen('php://temp', 'r+');
                fputcsv($csv, ['id', 'device_id', 'device_name', 'protocol', 'device_area', 'year', 'month', 'count', 'created_at']);
                foreach ($pd['download_rows'] ?? [] as $r) {
                    fputcsv($csv, [
                        $r['id'] ?? '',
                        $r['device_id'] ?? '',
                        $r['device_name'] ?? '',
                        $r['protocol'] ?? '',
                        $r['device_area'] ?? '',
                        $r['year'] ?? '',
                        $r['month'] ?? '',
                        $r['count'] ?? '',
                        $r['created_at'] ?? '',
                    ]);
                }
                rewind($csv);
                $csvData = stream_get_contents($csv);
                fclose($csv);

                $filename = 'Download History' . ' - ' . now()->format('Ymd His') . '.csv';
                $metaFallback = $meta ?? [];
                Mail::to($auth->email ?? config('mail.from.address'))
                    ->send(new DownloadsExcelMail(
                        $subject,
                        $body,
                        $csvData,
                        $filename,
                        $metaFallback,
                        $pdfBytes,
                        $pdfFilename
                    ));

                session()->flash('swal', [
                    'icon' => 'success',
                    'title' => __('Well done!'),
                    'text' => __('Email sent with CSV fallback')
                ]);

                return response()->json(['message' => __('Email sent with CSV fallback')]);
            } catch (\Throwable $_e) {
                \Log::error('historyEmail fallback error', ['error' => $_e->getMessage()]);
                return response()->json(['message' => 'Failed to send email'], 500);
            }
        }
    }
}
