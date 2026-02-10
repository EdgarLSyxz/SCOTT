<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('Downloads report') }}</title>
    <style>
        :root {
            --brand: #9F24A5;
            --text: #1f2933;
            --muted: #52606d;
            --border: #e5e7eb;
            --bg: #f7f8fa;
        }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: var(--text); background: #ffffff; margin: 0; padding: 0; }
        .page { padding: 24px 28px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--brand); padding-bottom: 10px; margin-bottom: 18px; }
        .title { font-size: 20px; font-weight: 700; color: var(--brand); }
        .badge { background: var(--brand); color: #fff; padding: 4px 10px; border-radius: 999px; font-size: 11px; letter-spacing: 0.4px; }
        .meta-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-bottom: 16px; }
        .card { border: 1px solid var(--border); border-radius: 10px; background: var(--bg); padding: 12px 14px; margin-bottom: 12px; break-inside: avoid; page-break-inside: avoid; }
        .card h4 { margin: 0 0 6px 0; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--muted); }
        .card .value { font-size: 14px; font-weight: 700; color: var(--text); }
        .section { margin-top: 14px; margin-bottom: 16px; }
        .section h3 { margin: 0 0 8px 0; font-size: 14px; color: var(--text); }
        .chart-box { text-align: center; border: 1px solid var(--border); border-radius: 10px; padding: 10px; background: #fff; }
        .chart-box img { max-width: 100%; height: auto; max-height: 360px; object-fit: contain; }
        .compact-table { width:100%; border-collapse:collapse; font-size:11px; }
        .compact-table th, .compact-table td { padding:8px 10px; border-bottom:1px solid #eef2f6; }
        .compact-table thead th { background:#f8fafc; color:var(--muted); font-weight:700; text-align:left; }
        .compact-table td { color:var(--text); }
        .compact-table tr:nth-child(even) { background: #fbfcfd; }
        .group-card { page-break-inside: avoid; border-radius:8px; border:1px solid #eef2f6; background:#fff; padding:10px; margin-bottom:10px; }
        .device-header { display:flex; align-items:center; gap:8px; }
        .device-name { font-weight:700; font-size:13px; color:var(--text); }
        .device-meta { font-size:11px; color:var(--muted); }
        .device-total { font-weight:700; color:var(--text); }
        .device-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; align-items: start; margin-bottom: 24px; }
        .month-list { margin:6px 0 0 0; padding:0; list-style:none; font-size:11px; color:var(--muted); }
        .month-list li { margin-bottom:6px; }
        .card .card-header { display:flex; align-items:center; gap:8px; width:100%; }
        .card .card-header .device-name-inline { font-weight:700; font-size:13px; color:var(--text); flex:1; min-width:0; overflow:hidden; white-space:nowrap; text-overflow:ellipsis; }
        .card .card-header .device-total-inline { font-size:12px; color:var(--muted); flex:0 0 auto; }
        .details-section { page-break-before: always; }
        .details-table { page-break-inside: avoid; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        .note { font-size: 11px; color: var(--muted); margin-top: 6px; }
        .footer { font-size: 11px; color: var(--muted); text-align: center; margin-top: 10px; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div style="display:flex;align-items:center;gap:10px; width:100%;">
                @if(!empty($logo))
                    <img src="{{ $logo }}" alt="{{ config('app.name') }}" style="height:34px; width:auto; object-fit:cover; border-radius:8px; display:inline-block;" />
                @endif
                <div class="title" style="display:inline-block; margin:0;">{{ __('Downloads report by devices') }}</div>
            </div>
        </div>

        <div class="meta-grid">
            <div class="card">
                <h4>{{ __('Generated at') }}</h4>
                <div class="value">{{ now()->format('Y-m-d H:i:s') }}</div>
            </div>
            <div class="card">
                <h4>{{ __('Report year') }}</h4>
                <div class="value">{{ $year ?? date('Y') }}</div>
            </div>
            @php
                $__months_local = ['',
                    __('months.january'), __('months.february'), __('months.march'), __('months.april'), __('months.may'), __('months.june'),
                    __('months.july'), __('months.august'), __('months.september'), __('months.october'), __('months.november'), __('months.december')
                ];

                $top_raw = $summary['top_month_label'] ?? null;
                $top_month_label = '—';
                if (!empty($top_raw)) {
                    if (is_numeric($top_raw)) {
                        $mi = intval($top_raw);
                        $top_month_label = $__months_local[$mi] ?? $top_raw;
                    } else {
                        if (preg_match('/(\d{4})[-\/](\d{1,2})/', $top_raw, $m)) {
                            $year = $m[1]; $mi = intval($m[2]);
                            $top_month_label = ($__months_local[$mi] ?? $top_raw) . ' ' . $year;
                        } elseif (preg_match('/(\d{1,2})[-\/](\d{4})/', $top_raw, $m2)) {
                            $mi = intval($m2[1]); $year = $m2[2];
                            $top_month_label = ($__months_local[$mi] ?? $top_raw) . ' ' . $year;
                        } else {
                            $map = [
                                'january' => __('months.january'), 'february' => __('months.february'), 'march' => __('months.march'),
                                'april' => __('months.april'), 'may' => __('months.may'), 'june' => __('months.june'),
                                'july' => __('months.july'), 'august' => __('months.august'), 'september' => __('months.september'),
                                'october' => __('months.october'), 'november' => __('months.november'), 'december' => __('months.december'),
                                'jan' => __('months.january'), 'feb' => __('months.february'), 'mar' => __('months.march'),
                                'apr' => __('months.april'), 'jun' => __('months.june'), 'jul' => __('months.july'),
                                'aug' => __('months.august'), 'sep' => __('months.september'), 'oct' => __('months.october'),
                                'nov' => __('months.november'), 'dec' => __('months.december'),
                                'enero' => __('months.january'), 'febrero' => __('months.february'), 'marzo' => __('months.march'),
                                'abril' => __('months.april'), 'mayo' => __('months.may'), 'junio' => __('months.june'),
                                'julio' => __('months.july'), 'agosto' => __('months.august'), 'septiembre' => __('months.september'),
                                'octubre' => __('months.october'), 'noviembre' => __('months.november'), 'diciembre' => __('months.december'),
                                'ene' => __('months.january'), 'abr' => __('months.april'), 'ago' => __('months.august'),
                                'dic' => __('months.december')
                            ];
                            $found = false;
                            foreach ($map as $k => $v) {
                                if (stripos($top_raw, $k) !== false) {
                                    if (preg_match('/(\d{4})/', $top_raw, $y)) {
                                        $top_month_label = $v . ' ' . $y[1];
                                    } else {
                                        $top_month_label = $v;
                                    }
                                    $found = true; break;
                                }
                            }
                            if (!$found) {
                                if (preg_match('/(\d{1,2})$/', $top_raw, $m3)) {
                                    $mi = intval($m3[1]); if ($mi >=1 && $mi <=12) $top_month_label = $__months_local[$mi]; else $top_month_label = $top_raw;
                                } else {
                                    $top_month_label = $top_raw;
                                }
                            }
                        }
                    }
                }
            @endphp
            @if(!empty($summary))
                <div class="card">
                    <h4>{{ __('Total downloads') }}</h4>
                    <div class="value">{{ $summary['total'] ?? 0 }}</div>
                </div>
                <div class="card">
                    <h4>{{ __('Average per month') }}</h4>
                    <div class="value">{{ $summary['average'] ?? 0 }}</div>
                </div>
                <div class="card">
                    <h4>{{ __('Top month') }}</h4>
                    <div class="value">{{ $top_month_label }} ({{ $summary['top_month_value'] ?? 0 }})</div>
                </div>
            @endif
        </div>

        <br><br>

        @if(!empty($monthlyImage))
            <div class="section">
                <h3>{{ __('Monthly downloads') }}</h3>
                <div class="chart-box">
                    <img src="{{ $monthlyImage }}" alt="monthly chart">
                </div>
                <div class="note">{{ __('Figure 1. Monthly downloads for the selected year.') }}</div>
            </div>
        @endif

        @if(!empty($pieImage) && empty($device_id))
            <div class="section" style="margin-top: 12px;">
                <h3>{{ __('Protocol distribution') }}</h3>
                <div class="chart-box">
                    <img src="{{ $pieImage }}" alt="pie chart">
                </div>
                <div class="note">{{ __('Figure 2. Share of downloads by protocol.') }}</div>
            </div>
        @endif

        <br>

        <div class="section">
            <h3>{{ __('Downloads by device') }}</h3>
            @php
                $monthsFull = ['',
                    __('months.january'), __('months.february'), __('months.march'), __('months.april'), __('months.may'), __('months.june'),
                    __('months.july'), __('months.august'), __('months.september'), __('months.october'), __('months.november'), __('months.december')
                ];
            @endphp
            @if(!empty($devices) && count($devices))
                <div class="device-grid">
                    @foreach($devices as $d)
                        <div class="card" style="display:flex;flex-direction:column;margin-bottom:24px;">
                            <div class="card-header">
                                <div class="device-name-inline">{{ $d['name'] }}</div>
                                @if(!empty($d['no_aplica']))
                                    <div class="device-total-inline">{{ __('Total') }}: {{ __('No aplica') }}</div>
                                @else
                                    <div class="device-total-inline">{{ __('Total') }}: {{ $d['total'] }}</div>
                                @endif
                            </div>

                            <div style="width:100%">{!! $d['sparkline'] !!}</div>

                            @if(!empty($d['no_aplica']))
                                <div class="note">{{ __('No aplica') }}</div>
                            @else
                                <div style="font-size:11px;color:var(--muted);margin-top:12px;">
                                    <strong>{{ __('Monthly counts') }}:</strong>
                                    <ul class="month-list">
                                        @foreach($d['counts'] as $i => $c)
                                            @php
                                                if (is_numeric($i)) {
                                                    $mIndex = intval($i) + 1;
                                                    $lbl = $monthsFull[$mIndex] ?? ($period_labels[$i] ?? '');
                                                } else {
                                                    $lbl = $period_labels[$i] ?? '';
                                                }
                                            @endphp
                                            <li>{{ $lbl }}: {{ $c }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="note">{{ __('Figure 3. Monthly download trends per device for the selected range.') }}</div>
            @else
                <div class="card">
                    <div style="font-size:12px;color:var(--muted);">{{ __('No device breakdown available for the selected range.') }}</div>
                </div>
            @endif
        </div>

        <br><br><br>

        <div class="section details-section" style="margin-top:18px;">
            <h3>{{ __('Detailed downloads') }}</h3>
            <div style="overflow:auto; border:1px solid var(--border); border-radius:8px; padding:8px; background:#fff;">
                @if(!empty($grouped_by_device) && count($grouped_by_device))
                    @php $deviceCount = count($grouped_by_device); @endphp
                    @if($deviceCount > 1)
                        @php
                            $monthsArr = ['',
                                __('months.january'), __('months.february'), __('months.march'), __('months.april'), __('months.may'), __('months.june'),
                                __('months.july'), __('months.august'), __('months.september'), __('months.october'), __('months.november'), __('months.december')
                            ];
                            $byMonth = [];
                            foreach ($grouped_by_device as $dev) {
                                $name = $dev['name'] ?? $dev['device_name'] ?? __('Unknown device');
                                $protocol = $dev['protocol'] ?? '';
                                $area = $dev['device_area'] ?? '';
                                $counts = $dev['counts'] ?? $dev['months'] ?? [];
                                $devYear = $dev['year'] ?? $year ?? date('Y');
                                if (!is_array($counts)) continue;
                                foreach ($counts as $idx => $cnt) {
                                    $mIndex = intval($idx) + 1;
                                    if ($mIndex < 1 || $mIndex > 12) continue;
                                    $monthKey = sprintf('%04d-%02d', intval($devYear), $mIndex);
                                    $label = ($monthsArr[$mIndex] ?? '') . ' ' . $devYear;
                                    if (!isset($byMonth[$monthKey])) {
                                        $byMonth[$monthKey] = ['label' => $label, 'rows' => []];
                                    }
                                    $byMonth[$monthKey]['rows'][] = [
                                        'device_name' => $name,
                                        'protocol' => $protocol,
                                        'area' => $area,
                                        'count' => $cnt,
                                    ];
                                }
                            }
                            ksort($byMonth);
                        @endphp

                        @foreach($byMonth as $monthKey => $mdata)
                            <div class="group-card">
                                <div class="device-header" style="justify-content:space-between;">
                                    <div>
                                        <div class="device-name">{{ $mdata['label'] }}</div>
                                        <div class="device-meta">{{ __('Devices') }}</div>
                                    </div>
                                    <div class="device-total">{{ __('Total') }}: {{ array_sum(array_column($mdata['rows'], 'count')) }}</div>
                                </div>

                                <div style="margin-top:8px;">
                                    <table class="compact-table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Device') }}</th>
                                                <th>{{ __('Protocol') }}</th>
                                                <th style="text-align:right;">{{ __('Downloads') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($mdata['rows'] as $r)
                                                <tr>
                                                    <td>{{ $r['device_name'] }}</td>
                                                    <td>{{ $r['protocol'] }}{{ $r['area'] ? ' — ' . $r['area'] : '' }}</td>
                                                    <td style="text-align:right;">{{ $r['count'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    @else
                        @foreach($grouped_by_device as $device)
                            <div class="group-card">
                                <div class="device-header" style="justify-content:space-between;">
                                    <div>
                                        <div class="device-name">{{ $device['name'] ?? $device['device_name'] ?? __('Unknown device') }}</div>
                                        <div class="device-meta">{{ $device['protocol'] ?? '' }} @if(!empty($device['device_area'])) — {{ $device['device_area'] }} @endif</div>
                                    </div>
                                    <div class="device-total">{{ __('Total') }}: {{ $device['total'] ?? (is_array($device['counts'] ?? null) ? array_sum($device['counts']) : 0) }}</div>
                                </div>

                                <div style="margin-top:8px;">
                                    <table class="compact-table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Month') }}</th>
                                                <th style="text-align:right;">{{ __('Downloads') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $counts = $device['counts'] ?? $device['months'] ?? [];
                                                $monthsArr = ['',
                                                    __('months.january'), __('months.february'), __('months.march'), __('months.april'), __('months.may'), __('months.june'),
                                                    __('months.july'), __('months.august'), __('months.september'), __('months.october'), __('months.november'), __('months.december')
                                                ];
                                            @endphp
                                            @if(is_array($counts) && count($counts))
                                                @foreach($counts as $idx => $cnt)
                                                    @php
                                                        $lbl = $period_labels[$idx] ?? ($device['months_labels'][$idx] ?? null);
                                                        if (!$lbl) {
                                                            $mIndex = intval($idx) + 1;
                                                            $lbl = ($monthsArr[$mIndex] ?? '') . ' ' . ($device['year'] ?? $year ?? '');
                                                        }
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $lbl }}</td>
                                                        <td style="text-align:right;">{{ $cnt }}</td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="2" style="padding:8px;color:var(--muted);">{{ __('No monthly counts for this device.') }}</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    @endif
                @elseif(!empty($download_rows) && count($download_rows))
                    @php
                        $grouped = [];
                        foreach ($download_rows as $r) {
                            $deviceName = $r['device_name'] ?? ($r['device'] ?? '');
                            $protocol = $r['protocol'] ?? '';
                            $area = $r['device_area'] ?? ($r['area'] ?? '');
                            $month = $r['month'] ?? '';
                            $yearRow = $r['year'] ?? ($r['year'] ?? '');
                            $count = isset($r['count']) ? (int)$r['count'] : 0;
                            $key = implode('|', [$deviceName, $protocol, $area, $month, $yearRow]);
                            if (!isset($grouped[$key])) {
                                $grouped[$key] = [
                                    'device_name' => $deviceName,
                                    'protocol' => $protocol,
                                    'device_area' => $area,
                                    'month' => $month,
                                    'year' => $yearRow,
                                    'count' => 0,
                                ];
                            }
                            $grouped[$key]['count'] += $count;
                        }
                        $groupedRows = array_values($grouped);

                        $protocolGroups = [];
                        foreach ($groupedRows as $r) {
                            $p = trim(strtoupper((string)($r['protocol'] ?? '')));
                            if ($p === '') $p = 'UNKNOWN';
                            $protocolGroups[$p][] = $r;
                        }

                        $protocols = array_keys($protocolGroups);
                        natcasesort($protocols);
                        $protocols = array_values($protocols);

                        $orderedProtocols = [];
                        if (in_array('HLS', $protocols, true)) { $orderedProtocols[] = 'HLS'; }
                        if (in_array('DASH', $protocols, true)) { $orderedProtocols[] = 'DASH'; }
                        foreach ($protocols as $p) {
                            if ($p === 'HLS' || $p === 'DASH') continue;
                            $orderedProtocols[] = $p;
                        }

                        $orderedRows = [];
                        foreach ($orderedProtocols as $p) {
                            $rowsForP = $protocolGroups[$p] ?? [];
                            usort($rowsForP, function($a, $b) {
                                $cmp = strcmp($a['device_name'] ?? '', $b['device_name'] ?? '');
                                if ($cmp !== 0) return $cmp;
                                if (($a['year'] ?? '') != ($b['year'] ?? '')) return (($a['year'] ?? 0) <=> ($b['year'] ?? 0));
                                return (($a['month'] ?? 0) <=> ($b['month'] ?? 0));
                            });
                            foreach ($rowsForP as $r) { $orderedRows[] = $r; }
                        }

                        $filtered = [];
                        foreach ($orderedRows as $r) {
                            if (!empty($r['device_name']) && mb_strtolower($r['device_name']) === 'web client') continue;
                            $filtered[] = $r;
                        }
                        $orderedRows = $filtered;
                        $orderedRows[] = [
                            'device_name' => 'Web Client',
                            'protocol' => '',
                            'device_area' => '',
                            'month' => '',
                            'year' => '',
                            'count' => __('No aplica'),
                        ];

                        $groupedRows = $orderedRows;
                        $monthsArr = ['',
                            __('months.january'), __('months.february'), __('months.march'), __('months.april'), __('months.may'), __('months.june'),
                            __('months.july'), __('months.august'), __('months.september'), __('months.october'), __('months.november'), __('months.december')
                        ];
                    @endphp

                    <table class="compact-table">
                        <thead>
                            <tr>
                                <th>{{ __('Device') }}</th>
                                <th>{{ __('Protocol') }}</th>
                                <th style="text-align:right;">{{ __('Month') }}</th>
                                <th style="text-align:right;">{{ __('Year') }}</th>
                                <th style="text-align:right;">{{ __('Downloads') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groupedRows as $row)
                                @php
                                    $m = $row['month'];
                                    $monthLabel = $m;
                                    if (is_numeric($m)) {
                                        $mi = intval($m);
                                        $monthLabel = ($monthsArr[$mi] ?? $m);
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $row['device_name'] }}</td>
                                    <td>{{ $row['protocol'] }}</td>
                                    <td style="text-align:right;">{{ $monthLabel }}</td>
                                    <td style="text-align:right;">{{ $row['year'] }}</td>
                                    <td style="text-align:right;">{{ $row['count'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div style="padding:12px;color:var(--muted);">{{ __('No detailed download records for the selected range.') }}</div>
                @endif
            </div>
            <div class="note">{{ __('Table. Full download records for the selected range.') }}</div>
        </div>

        <div class="footer">{{ config('app.name') }}</div>
    </div>
</body>
</html>
