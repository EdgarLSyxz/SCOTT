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
        .year-charts-page { page-break-before: always; page-break-inside: avoid; }
        .year-charts-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; align-items: start; }
        .global-protocol-card { page-break-before: always; page-break-inside: avoid; }
        .protocol-global-bar-track { width: 100%; background: #eef2f6; border-radius: 999px; height: 12px; overflow: hidden; }
        .protocol-global-bar-fill { height: 100%; border-radius: 999px; }
        .platform-device-text {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 12px;
            letter-spacing: 0.2px;
            font-weight: 700;
        }
        .downloads-device-title { page-break-after: avoid; }
        .downloads-device-card { page-break-inside: auto; }
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
            @php
                $__months_local = [
                    '',
                    __('months.january'),
                    __('months.february'),
                    __('months.march'),
                    __('months.april'),
                    __('months.may'),
                    __('months.june'),
                    __('months.july'),
                    __('months.august'),
                    __('months.september'),
                    __('months.october'),
                    __('months.november'),
                    __('months.december')
                ];
                $isMonthlySelected = !empty($month);
            @endphp
            <div class="card">
                <h4>{{ __('Generated at') }}</h4>
                <div class="value">{{ now()->format('Y-m-d H:i:s') }}</div>
            </div>
            <div class="card">
                <h4>{{ __('Report year') }}</h4>
                @php
                    $reportYearValue = !empty($is_multi_year)
                        ? ($year_range_label ?? __('Multiple years'))
                        : ($year ?? date('Y'));

                    if (empty($is_multi_year) && !empty($month)) {
                        $mnum = intval($month);
                        $mname = $__months_local[$mnum] ?? null;
                        $reportYearValue = ($mname ? $mname . ' ' : '') . ($year ?? date('Y'));
                    }
                @endphp
                <div class="value">{{ $reportYearValue }}</div>
            </div>
            @php
                $isMonthlyEmailSummary = !empty($summary['is_monthly_email']) && !empty($month);                $isDeviceMonthMode = !empty($summary['is_device_month_mode']);
                $top_raw = $summary['top_month_label'] ?? null;
                $top_month_label = '—';
                if ($isMonthlyEmailSummary) {
                    $top_month_label = !empty($top_raw) ? $top_raw : '—';
                } elseif (!empty($top_raw)) {
                    if (is_numeric($top_raw)) {
                        $mi = intval($top_raw);
                        $top_month_label = $__months_local[$mi] ?? $top_raw;
                    } else {
                        if (preg_match('/(\d{4})[-\/](\d{1,2})/', $top_raw, $m)) {
                            $year = $m[1];
                            $mi = intval($m[2]);
                            $top_month_label = ($__months_local[$mi] ?? $top_raw) . ' ' . $year;
                        } elseif (preg_match('/(\d{1,2})[-\/](\d{4})/', $top_raw, $m2)) {
                            $mi = intval($m2[1]);
                            $year = $m2[2];
                            $top_month_label = ($__months_local[$mi] ?? $top_raw) . ' ' . $year;
                        } else {
                            $map = [
                                'january' => __('months.january'),
                                'february' => __('months.february'),
                                'march' => __('months.march'),
                                'april' => __('months.april'),
                                'may' => __('months.may'),
                                'june' => __('months.june'),
                                'july' => __('months.july'),
                                'august' => __('months.august'),
                                'september' => __('months.september'),
                                'october' => __('months.october'),
                                'november' => __('months.november'),
                                'december' => __('months.december'),
                                'jan' => __('months.january'),
                                'feb' => __('months.february'),
                                'mar' => __('months.march'),
                                'apr' => __('months.april'),
                                'jun' => __('months.june'),
                                'jul' => __('months.july'),
                                'aug' => __('months.august'),
                                'sep' => __('months.september'),
                                'oct' => __('months.october'),
                                'nov' => __('months.november'),
                                'dec' => __('months.december'),
                                'enero' => __('months.january'),
                                'febrero' => __('months.february'),
                                'marzo' => __('months.march'),
                                'abril' => __('months.april'),
                                'mayo' => __('months.may'),
                                'junio' => __('months.june'),
                                'julio' => __('months.july'),
                                'agosto' => __('months.august'),
                                'septiembre' => __('months.september'),
                                'octubre' => __('months.october'),
                                'noviembre' => __('months.november'),
                                'diciembre' => __('months.december'),
                                'ene' => __('months.january'),
                                'abr' => __('months.april'),
                                'ago' => __('months.august'),
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
                                    $found = true;
                                    break;
                                }
                            }
                            if (!$found) {
                                if (preg_match('/(\d{1,2})$/', $top_raw, $m3)) {
                                    $mi = intval($m3[1]);
                                    if ($mi >= 1 && $mi <= 12)
                                        $top_month_label = $__months_local[$mi];
                                    else
                                        $top_month_label = $top_raw;
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
                    <h4>
                        @if($isMonthlySelected)
                            {{ __('Monthly downloads') }}: {{ $__months_local[intval($month)] ?? $month }}
                        @else
                            {{ __('Total downloads') }}
                        @endif
                    </h4>
                    <div class="value">{{ number_format($summary['total'] ?? 0) }}</div>
                </div>
                <div class="card">
                    <h4>{{ $isDeviceMonthMode ? __('Average per day') : ($isMonthlyEmailSummary ? __('Average per device') : __('Average per month')) }}</h4>
                    <div class="value">{{ number_format($summary['average'] ?? 0) }}</div>
                </div>
                @if(empty($is_multi_year))
                <div class="card">
                    <h4>{{ $isDeviceMonthMode ? __('Top day') : ($isMonthlyEmailSummary ? __('Top device') : __('Top month')) }}</h4>
                    <div class="value">{{ $top_month_label }} ({{ $summary['top_month_value'] ?? 0 }})</div>
                </div>
                @endif
            @endif
        </div>

        <br><br>

        @php
            $chartsByYear = (isset($charts_by_year) && is_array($charts_by_year)) ? $charts_by_year : [];
            $protocolSummaryByYear = (isset($protocol_summary_by_year) && is_array($protocol_summary_by_year)) ? $protocol_summary_by_year : [];
            $protocolGlobalSummary = (isset($protocol_summary) && is_array($protocol_summary)) ? $protocol_summary : null;
            $renderedMultiYearCharts = false;
            $annualFigureNumber = 1;
            ksort($chartsByYear);
            ksort($protocolSummaryByYear);
        @endphp

        @if((!empty($is_multi_year) || !empty($month)) && !empty($download_rows))
            @php
                $plataformas = [];
                foreach ($download_rows as $_r) {
                    $_proto = strtoupper(trim((string)($_r['protocol'] ?? '')));
                    if (!in_array($_proto, ['HLS', 'DASH'])) continue;
                    $_name  = $_r['device_name'] ?? 'Unknown';
                    $_key   = $_proto . '||' . $_name;
                    if (!isset($plataformas[$_key])) {
                        $plataformas[$_key] = ['name' => $_name, 'protocol' => $_proto, 'total' => 0];
                    }
                    $plataformas[$_key]['total'] += (int)($_r['count'] ?? 0);
                }
                usort($plataformas, function($a, $b) {
                    $op = ['HLS' => 0, 'DASH' => 1];
                    $pa = $op[$a['protocol']] ?? 2;
                    $pb = $op[$b['protocol']] ?? 2;
                    return $pa !== $pb ? $pa - $pb : strcmp($a['name'], $b['name']);
                });
                $plataformasTotal = array_sum(array_column($plataformas, 'total'));
                $platformsMonthLabel = null;
                if (!empty($month)) {
                    $platformsMonthLabel = $__months_local[intval($month)] ?? $month;
                }
            @endphp
            @if(!empty($plataformas))
            <div class="section" style="page-break-before: always; page-break-inside: avoid;">
                <h3>{{ __('Platforms') }}</h3>
                <div class="note" style="margin-bottom: 8px;">
                    @if(!empty($platformsMonthLabel))
                        {{ __('Total platform downloads for :month across selected years.', ['month' => $platformsMonthLabel]) }}
                    @else
                        {{ __('Total platform downloads across all selected years.') }}
                    @endif
                </div>
                <table class="compact-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('Device') }}</th>
                            <th>{{ __('Protocol') }}</th>
                            <th style="text-align:right;">{{ __('Total downloads') }}</th>
                            <th style="text-align:right;">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plataformas as $_pi => $_p)
                        <tr>
                            <td>{{ $_pi + 1 }}</td>
                            <td class="platform-device-text">{{ $_p['name'] }}</td>
                            <td>
                                <span style="font-weight:700; color:{{ $_p['protocol'] === 'HLS' ? '#00A7C4' : '#8B5CF6' }};">
                                    {{ $_p['protocol'] }}
                                </span>
                            </td>
                            <td style="text-align:right;">{{ number_format($_p['total']) }}</td>
                            <td style="text-align:right;">
                                {{ $plataformasTotal > 0 ? number_format($_p['total'] / $plataformasTotal * 100, 1) : 0 }}%
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="font-weight:700; background:#f0f4f8;">
                            <td colspan="3">{{ __('Total') }}</td>
                            <td style="text-align:right;">{{ number_format($plataformasTotal) }}</td>
                            <td style="text-align:right;">100%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div style="page-break-after: always;"></div>
            @endif
        @endif

        @if(!empty($is_multi_year) && is_array($protocolGlobalSummary))
            @php
                $globalTotalDownloads = (int) ($protocolGlobalSummary['download_total'] ?? 0);
                $globalHls = is_array($protocolGlobalSummary['HLS'] ?? null) ? $protocolGlobalSummary['HLS'] : [];
                $globalDash = is_array($protocolGlobalSummary['DASH'] ?? null) ? $protocolGlobalSummary['DASH'] : [];
                $globalHlsDownloads = (int) ($globalHls['download_total'] ?? 0);
                $globalDashDownloads = (int) ($globalDash['download_total'] ?? 0);
                $globalHlsPct = (float) ($globalHls['download_percent'] ?? 0);
                $globalDashPct = (float) ($globalDash['download_percent'] ?? 0);
            @endphp

            @php
                $yearTotalsForBar = [];
                foreach ($download_rows as $_br) {
                    $_by = (int)($_br['year'] ?? 0);
                    if ($_by <= 0) continue;
                    $yearTotalsForBar[$_by] = ($yearTotalsForBar[$_by] ?? 0) + (int)($_br['count'] ?? 0);
                }
                ksort($yearTotalsForBar);
                $grandYearTotal = array_sum($yearTotalsForBar);
                $chartGlobalBarSrc = $chart_global_bar ?? null;
            @endphp

            @if(!empty($chartGlobalBarSrc))
            <div class="section" style="page-break-before: always; page-break-inside: avoid;">
                <h3>{{ __('Annual downloads comparison') }}</h3>
                <div class="chart-box">
                    <img src="{{ $chartGlobalBarSrc }}" alt="global year bar chart" style="max-height: 280px;">
                </div>
                <table class="compact-table" style="margin-top: 10px;">
                    <thead>
                        <tr>
                            <th>{{ __('Year') }}</th>
                            <th style="text-align:right;">{{ __('Total downloads') }}</th>
                            <th style="text-align:right;">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($yearTotalsForBar as $_yr => $_yt)
                        <tr>
                            <td style="font-weight:700;">{{ $_yr }}</td>
                            <td style="text-align:right;">{{ number_format($_yt) }}</td>
                            <td style="text-align:right;">
                                {{ $grandYearTotal > 0 ? number_format($_yt / $grandYearTotal * 100, 1) : 0 }}%
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="font-weight:700; background:#f0f4f8;">
                            <td>{{ __('Total') }}</td>
                            <td style="text-align:right;">{{ number_format($grandYearTotal) }}</td>
                            <td style="text-align:right;">100%</td>
                        </tr>
                    </tfoot>
                </table>
                <div class="note">{{ __('Figure') }} {{ $annualFigureNumber++ }}. {{ __('Total annual downloads comparison across selected years.') }}</div>
            </div>
            @endif

            <div class="section global-protocol-card">
                <h3>{{ __('Global protocol distribution (all years)') }}</h3>
                <div class="card" style="margin-bottom: 8px;">
                    <div style="font-size: 12px; color: var(--muted); margin-bottom: 10px;">
                        {{ __('Total downloads') }}: <strong style="color: var(--text);">{{ number_format($globalTotalDownloads, 0) }}</strong>
                    </div>

                    <div style="margin-bottom: 10px;">
                        <div style="display:flex; justify-content:space-between; font-size:11px; margin-bottom:4px;">
                            <span style="font-weight:700; color:#00A7C4;">HLS</span>
                            <span style="color: var(--text);">{{ number_format($globalHlsPct, 1) }}% {{ __('of') }} {{ __('Downloads') }} · {{ number_format($globalHlsDownloads, 0) }} / {{ number_format($globalTotalDownloads, 0) }} {{ __('Downloads') }}</span>
                        </div>
                        <div class="protocol-global-bar-track">
                            <div class="protocol-global-bar-fill" style="width: {{ max(0, min(100, $globalHlsPct)) }}%; background:#00A7C4;"></div>
                        </div>
                    </div>

                    <div>
                        <div style="display:flex; justify-content:space-between; font-size:11px; margin-bottom:4px;">
                            <span style="font-weight:700; color:#8B5CF6;">DASH</span>
                            <span style="color: var(--text);">{{ number_format($globalDashPct, 1) }}% {{ __('of') }} {{ __('Downloads') }} · {{ number_format($globalDashDownloads, 0) }} / {{ number_format($globalTotalDownloads, 0) }} {{ __('Downloads') }}</span>
                        </div>
                        <div class="protocol-global-bar-track">
                            <div class="protocol-global-bar-fill" style="width: {{ max(0, min(100, $globalDashPct)) }}%; background:#8B5CF6;"></div>
                        </div>
                    </div>
                </div>
                <div class="note">{{ __('Figure') }} {{ $annualFigureNumber++ }}. {{ __('Global downloads by protocol across all selected years.') }}</div>
            </div>
        @endif

        @if(!empty($is_multi_year) && !empty($chartsByYear))
            @foreach($chartsByYear as $chartYear => $chartSet)
                @php
                    $chartSet = is_array($chartSet) ? $chartSet : [];
                    $yearMonthlyImage = $chartSet['monthly'] ?? null;
                    $yearPieImage = $chartSet['pie'] ?? null;
                    $yearProtocolSummary = $protocolSummaryByYear[$chartYear] ?? null;
                    $yearProtocolTotal = is_array($yearProtocolSummary) ? (int) ($yearProtocolSummary['download_total'] ?? 0) : 0;
                    $yearHlsSummary = is_array($yearProtocolSummary) ? ($yearProtocolSummary['HLS'] ?? null) : null;
                    $yearDashSummary = is_array($yearProtocolSummary) ? ($yearProtocolSummary['DASH'] ?? null) : null;
                @endphp

                @if(!empty($yearMonthlyImage) || !empty($yearPieImage))
                    <div class="section year-charts-page">
                        <h3>{{ __('Charts by year') }}: {{ $chartYear }}</h3>
                        <div class="year-charts-grid">
                            <div>
                                @if(!empty($yearMonthlyImage))
                                    <div class="chart-box">
                                        <img src="{{ $yearMonthlyImage }}" alt="monthly chart {{ $chartYear }}" style="max-height: 230px;">
                                    </div>
                                    <div class="note">{{ __('Figure') }} {{ $annualFigureNumber++ }}. {{ __('Monthly downloads for year') }} {{ $chartYear }}.<br><br></div>
                                @else
                                    <div class="card" style="margin-bottom: 0;">
                                        <div style="font-size: 12px; color: var(--muted);">{{ __('No monthly chart available for this year.') }}</div>
                                    </div>
                                @endif
                            </div>

                            <div>
                                @if(!empty($yearPieImage))
                                    <div class="chart-box" style="padding: 6px;">
                                        <img src="{{ $yearPieImage }}" alt="pie chart {{ $chartYear }}" style="max-height: 230px;">
                                    </div>

                                    @if(is_array($yearProtocolSummary))
                                        <table style="width:100%; border-collapse: collapse; margin-top: 6px; font-size: 9px; font-family: DejaVu Sans, Arial, Helvetica, sans-serif;">
                                            <tbody>
                                                <tr>
                                                    <td style="padding: 5px 6px; border: 1px solid #eef2f6; background: #f8fafc; color: #1f2933; width: 50%;">
                                                        <span style="font-weight: 700; color: #00A7C4;">HLS</span>
                                                        — {{ number_format((float) ($yearHlsSummary['download_percent'] ?? 0), 1) }}% {{ __('of') }} {{ __('Downloads') }}
                                                        · {{ number_format((int) ($yearHlsSummary['download_total'] ?? 0), 0) }} / {{ number_format($yearProtocolTotal, 0) }} {{ __('Downloads') }}
                                                    </td>
                                                    <td style="padding: 5px 6px; border: 1px solid #eef2f6; background: #f8fafc; color: #1f2933; width: 50%;">
                                                        <span style="font-weight: 700; color: #8B5CF6;">DASH</span>
                                                        — {{ number_format((float) ($yearDashSummary['download_percent'] ?? 0), 1) }}% {{ __('of') }} {{ __('Downloads') }}
                                                        · {{ number_format((int) ($yearDashSummary['download_total'] ?? 0), 0) }} / {{ number_format($yearProtocolTotal, 0) }} {{ __('Downloads') }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    @endif

                                    <div class="note">{{ __('Figure') }} {{ $annualFigureNumber++ }}. {{ __('Share of downloads by protocol for year :year', ['year' => $chartYear]) }}</div>
                                @else
                                    <div class="card" style="margin-bottom: 0;">
                                        <div style="font-size: 12px; color: var(--muted);">{{ __('No protocol chart available for this year.') }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @php $renderedMultiYearCharts = true; @endphp
                @endif
            @endforeach
        @endif

        @if(empty($is_multi_year) || !$renderedMultiYearCharts)
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
                    <div class="chart-box" style="padding: 6px;">
                        <img src="{{ $pieImage }}" alt="pie chart" style="max-height: 300px;">
                    </div>
                    @php
                        $protocolSummary = $protocol_summary ?? null;
                        $totalProtocolDownloads = is_array($protocolSummary) ? (int) ($protocolSummary['download_total'] ?? 0) : 0;
                        $hlsSummary = is_array($protocolSummary) ? ($protocolSummary['HLS'] ?? null) : null;
                        $dashSummary = is_array($protocolSummary) ? ($protocolSummary['DASH'] ?? null) : null;
                    @endphp
                    @if(is_array($protocolSummary))
                        <table style="width:100%; border-collapse: collapse; margin-top: 6px; font-size: 9px; font-family: DejaVu Sans, Arial, Helvetica, sans-serif;">
                            <tbody>
                                <tr>
                                    <td style="padding: 5px 6px; border: 1px solid #eef2f6; background: #f8fafc; color: #1f2933; width: 50%;">
                                        <span style="font-weight: 700; color: #00A7C4;">HLS</span>
                                        — {{ number_format((float) ($hlsSummary['download_percent'] ?? 0), 1) }}% {{ __('of') }} {{ __('Downloads') }}
                                        · {{ number_format((int) ($hlsSummary['download_total'] ?? 0), 0) }} / {{ number_format($totalProtocolDownloads, 0) }} {{ __('Downloads') }}
                                    </td>
                                    <td style="padding: 5px 6px; border: 1px solid #eef2f6; background: #f8fafc; color: #1f2933; width: 50%;">
                                        <span style="font-weight: 700; color: #8B5CF6;">DASH</span>
                                        — {{ number_format((float) ($dashSummary['download_percent'] ?? 0), 1) }}% {{ __('of') }} {{ __('Downloads') }}
                                        · {{ number_format((int) ($dashSummary['download_total'] ?? 0), 0) }} / {{ number_format($totalProtocolDownloads, 0) }} {{ __('Downloads') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    @endif
                    <div class="note">{{ __('Figure 2. Share of downloads by protocol.') }}</div>
                </div>
            @endif
        @endif

        @php
            $competitorRankingRows = $competitor_ranking['rows'] ?? [];
            $competitorSnapshotDate = $competitor_ranking['snapshot_date'] ?? null;
        @endphp

        @if(!empty($competitorRankingRows))
            <div class="section" style="page-break-before: always;">
                <h3>{{ __('Competitor apps rating') }}</h3>
                <div class="note" style="margin-bottom: 8px;">
                    {{ __('Snapshot date') }}:
                    <strong>{{ $competitorSnapshotDate ? \Carbon\Carbon::parse($competitorSnapshotDate)->format('d/m/Y') : '—' }}</strong>
                </div>
                <table class="compact-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('Application') }}</th>
                            <th>{{ __('Rating') }}</th>
                            <th>{{ __('Downloads') }}</th>
                            <th>{{ __('Reviews') }}</th>
                            <th>{{ __('Trend') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($competitorRankingRows as $rankRow)
                            @php
                                $trend = strtolower((string)($rankRow['movement'] ?? 'new'));
                                $delta = (int)($rankRow['rank_delta'] ?? 0);
                                if ($trend === 'up') {
                                    $trendLabel = '+' . abs($delta);
                                } elseif ($trend === 'down') {
                                    $trendLabel = (string)$delta;
                                } elseif ($trend === 'same') {
                                    $trendLabel = '0';
                                } else {
                                    $trendLabel = __('New');
                                }
                            @endphp
                            <tr>
                                <td>{{ $rankRow['rank_position'] ?? '—' }}</td>
                                <td>{{ $rankRow['app_name'] ?? 'N/A' }}</td>
                                <td>{{ $rankRow['rating'] ?? '—' }}</td>
                                <td>{{ $rankRow['downloads_label'] ?? '—' }}</td>
                                <td>{{ $rankRow['reviews_label'] ?? '—' }}</td>
                                <td>{{ $trendLabel }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="note">{{ __('Figure') }} {{ !empty($is_multi_year) ? $annualFigureNumber++ : 3 }}. {{ __('Top competitor apps by rating for the selected range.') }}</div>
            </div>
        @endif

        <br>

        <div class="section" style="page-break-before: always;">
            <h3 class="downloads-device-title">{{ __('Downloads by device') }}</h3>
            @php
                $monthsFull = [
                    '',
                    __('months.january'),
                    __('months.february'),
                    __('months.march'),
                    __('months.april'),
                    __('months.may'),
                    __('months.june'),
                    __('months.july'),
                    __('months.august'),
                    __('months.september'),
                    __('months.october'),
                    __('months.november'),
                    __('months.december')
                ];
            @endphp

            @if(!empty($download_rows) && count($download_rows))
                @php
                    $deviceGroups = [];
                    foreach ($download_rows as $r) {
                        $deviceName = $r['device_name'] ?? '';
                        $protocol = $r['protocol'] ?? '';
                        $year = $r['year'] ?? '';
                        $month = $r['month'] ?? '';
                        $day = $r['day'] ?? '';
                        $count = isset($r['count']) ? (int) $r['count'] : 0;

                        if (!isset($deviceGroups[$deviceName])) {
                            $deviceGroups[$deviceName] = [
                                'name' => $deviceName,
                                'protocol' => $protocol,
                                'records' => []
                            ];
                        }

                        $deviceGroups[$deviceName]['records'][] = [
                            'year' => $year,
                            'month' => $month,
                            'day' => $day,
                            'count' => $count,
                            'date' => !empty($year) && !empty($month) && !empty($day)
                                ? sprintf('%04d-%02d-%02d', $year, $month, $day)
                                : ''
                        ];
                    }

                    foreach ($deviceGroups as &$group) {
                        usort($group['records'], function ($a, $b) {
                            if (($a['year'] ?? 0) != ($b['year'] ?? 0))
                                return (($a['year'] ?? 0) <=> ($b['year'] ?? 0));
                            if (($a['month'] ?? 0) != ($b['month'] ?? 0))
                                return (($a['month'] ?? 0) <=> ($b['month'] ?? 0));
                            return (($a['day'] ?? 0) <=> ($b['day'] ?? 0));
                        });
                    }

                    $protocolOrder = ['HLS' => 0, 'DASH' => 1];
                    usort($deviceGroups, function ($a, $b) use ($protocolOrder) {
                        $aProto = $protocolOrder[$a['protocol']] ?? 999;
                        $bProto = $protocolOrder[$b['protocol']] ?? 999;
                        if ($aProto !== $bProto)
                            return $aProto <=> $bProto;
                        return strcmp($a['name'] ?? '', $b['name'] ?? '');
                    });

                    $monthsArr = [
                        '',
                        __('months.january'),
                        __('months.february'),
                        __('months.march'),
                        __('months.april'),
                        __('months.may'),
                        __('months.june'),
                        __('months.july'),
                        __('months.august'),
                        __('months.september'),
                        __('months.october'),
                        __('months.november'),
                        __('months.december')
                    ];
                @endphp

                @foreach($deviceGroups as $device)
                    <div class="group-card downloads-device-card" style="margin-bottom: 20px; padding: 0; border: 2px solid #9F24A5;">
                        <div style="background-color: #7a1d82; color: white; padding: 16px; border-radius: 6px 6px 0 0; font-family: DejaVu Sans, Arial, Helvetica, sans-serif;">
                            <div style="font-weight: 700; font-size: 15px; color: #ffffff; margin: 0; letter-spacing: 0.3px; font-family: DejaVu Sans, Arial, Helvetica, sans-serif;">{{ $device['name'] }}</div>
                            <div style="color: #e0d4ff; margin-top: 4px; font-size: 12px; font-weight: 700; font-family: DejaVu Sans, Arial, Helvetica, sans-serif;">{{ $device['protocol'] ?? 'Unknown' }}</div>
                        </div>

                        @php
                            $recordsByMonth = [];
                            foreach ($device['records'] as $r) {
                                $mKey = $r['year'] . '-' . str_pad($r['month'], 2, '0', STR_PAD_LEFT);
                                if (!isset($recordsByMonth[$mKey])) {
                                    $recordsByMonth[$mKey] = [];
                                }
                                $recordsByMonth[$mKey][] = $r;
                            }
                        @endphp

                        <div style="padding: 14px 16px;">
                            @foreach($recordsByMonth as $monthKey => $records)
                                @php
                                    $firstRecord = $records[0];
                                    $m = intval($firstRecord['month']);
                                    $monthLabel = ($monthsArr[$m] ?? $m) . ' ' . ($firstRecord['year'] ?? '');
                                    $monthTotal = array_sum(array_map(fn($r) => intval($r['count']), $records));
                                @endphp
                                <div style="margin-bottom: 16px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                        <div style="font-weight: 700; font-size: 12px; color: #9F24A5; text-transform: uppercase; letter-spacing: 0.4px; font-family: DejaVu Sans, Arial, Helvetica, sans-serif;">{{ $monthLabel }}</div>
                                        <div style="font-weight: 700; font-size: 11px; color: #1f2933; background: #f0e6f8; padding: 3px 8px; border-radius: 4px; font-family: DejaVu Sans, Arial, Helvetica, sans-serif;">{{ number_format($monthTotal, 0) }}</div>
                                    </div>
                                    <table style="width:100%; border-collapse: collapse; font-size: 10px; font-family: DejaVu Sans, Arial, Helvetica, sans-serif;">
                                        <tbody>
                                            @php
                                                $cols = 5;
                                                $rowCount = ceil(count($records) / $cols);
                                            @endphp
                                            @for($row = 0; $row < $rowCount; $row++)
                                                <tr>
                                                    @for($col = 0; $col < $cols; $col++)
                                                        @php
                                                            $idx = $row * $cols + $col;
                                                            $record = $records[$idx] ?? null;
                                                        @endphp
                                                        <td style="padding: 6px 4px; border: 1px solid #e5e7eb; background: #ffffff; text-align: center; width: 20%; font-family: DejaVu Sans, Arial, Helvetica, sans-serif;">
                                                            @if($record)
                                                                <div style="font-weight: 700; color: #9F24A5; font-size: 11px;">{{ str_pad(intval($record['day']), 2, '0', STR_PAD_LEFT) }}</div>
                                                                <div style="color: #1f2933; margin-top: 2px; font-weight: 700; font-size: 10px;">{{ intval($record['count']) }}</div>
                                                            @else
                                                                <div style="color: #b0b8c1; font-size: 9px;">—</div>
                                                            @endif
                                                        </td>
                                                    @endfor
                                                </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                @if(empty($device_id))
                    <div class="group-card" style="background:#f0f4f8; border-left:4px solid #9ca3af; margin-bottom:16px;">
                        <div class="device-name">Web Client</div>
                        <div class="device-meta">{{ __('Not applicable') }}</div>
                    </div>
                @endif
            @elseif(!empty($devices) && count($devices))
                <div class="device-grid">
                    @foreach($devices as $d)
                        <div class="card" style="display:flex;flex-direction:column;margin-bottom:24px;">
                            <div class="card-header">
                                <div class="device-name-inline">{{ $d['name'] }}</div>
                                @if(!empty($d['no_aplica']))
                                    <div class="device-total-inline">{{ __('Total') }}: {{ __('Not applicable') }}</div>
                                @else
                                    <div class="device-total-inline">{{ __('Total') }}: {{ $d['total'] }}</div>
                                @endif
                            </div>

                            <div style="width:100%">{!! $d['sparkline'] ?? '' !!}</div>

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

        <div class="footer">{{ config('app.name') }}</div>
    </div>
</body>
</html>
