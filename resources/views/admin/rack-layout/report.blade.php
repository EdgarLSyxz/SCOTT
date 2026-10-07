<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Rack layouts general report') }}</title>
    <style>
        @page {
            margin: 110px 40px 70px 40px;
            header: pdf-header;
            footer: pdf-footer;
        }

        * { box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            color: #1f2937;
            margin: 0;
            padding: 0;
            font-size: 10.5px;
            line-height: 1.45;
        }

        /* === HEADER (repeats every page) === */
        .pdf-header {
            position: fixed;
            top: -90px;
            left: 0;
            right: 0;
            height: 80px;
            border-bottom: 3px solid #9F24A5;
            padding-bottom: 8px;
        }
        .pdf-header .brand {
            display: table;
            width: 100%;
        }
        .pdf-header .brand-left {
            display: table-cell;
            vertical-align: middle;
            width: 65%;
        }
        .pdf-header .brand-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 35%;
            font-size: 9px;
            color: #6b7280;
        }
        .pdf-header h1 {
            margin: 0;
            font-size: 16px;
            color: #9F24A5;
            font-weight: 700;
        }
        .pdf-header .subtitle {
            margin: 2px 0 0 0;
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .pdf-header .doc-tag {
            display: inline-block;
            background: #9F24A5;
            color: #fff;
            font-size: 8.5px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 3px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* === FOOTER (repeats every page) === */
        .pdf-footer {
            position: fixed;
            bottom: -50px;
            left: 0;
            right: 0;
            height: 30px;
            border-top: 1px solid #e5e7eb;
            padding-top: 6px;
            font-size: 8.5px;
            color: #9ca3af;
        }
        .pdf-footer .row {
            display: table;
            width: 100%;
        }
        .pdf-footer .left {
            display: table-cell;
            text-align: left;
        }
        .pdf-footer .center {
            display: table-cell;
            text-align: center;
        }
        .pdf-footer .right {
            display: table-cell;
            text-align: right;
        }

        /* === COVER === */
        .cover {
            page-break-after: always;
            padding: 50px 10px;
            text-align: center;
        }
        .cover .badge {
            display: inline-block;
            background: linear-gradient(135deg, #9F24A5 0%, #be30c9 100%);
            color: #fff;
            font-weight: 700;
            font-size: 11px;
            padding: 5px 18px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 24px;
        }
        .cover h1 {
            font-size: 36px;
            color: #1f2937;
            margin: 14px 0 6px 0;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .cover .subtitle {
            font-size: 16px;
            color: #6b7280;
            margin: 4px 0 22px 0;
            font-weight: 500;
        }
        .cover .divider {
            width: 80px;
            height: 4px;
            background: #9F24A5;
            margin: 16px auto;
            border-radius: 2px;
        }
        .cover .summary-box {
            margin: 36px auto 0 auto;
            background: #fafafa;
            border: 1px solid #e9d5f3;
            border-radius: 8px;
            padding: 24px 30px;
            max-width: 80%;
            text-align: left;
        }
        .cover .summary-box .row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        .cover .summary-box .lbl {
            display: table-cell;
            font-size: 10px;
            color: #6b21a8;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            font-weight: 700;
            width: 40%;
            padding: 6px 0;
        }
        .cover .summary-box .val {
            display: table-cell;
            font-size: 12px;
            color: #1f2937;
            font-weight: 700;
            text-align: right;
        }
        .cover .footer-note {
            margin-top: 50px;
            font-size: 9px;
            color: #9ca3af;
        }
        .cover .footer-note .name {
            font-weight: 700;
            color: #6b7280;
        }

        /* === SECTION TITLES === */
        h2.section {
            font-size: 13px;
            color: #1f2937;
            font-weight: 700;
            margin: 16px 0 10px 0;
            padding: 6px 12px;
            background: linear-gradient(to right, #f3e8f9 0%, #fdf5fe 100%);
            border-left: 4px solid #9F24A5;
            border-radius: 0 4px 4px 0;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        h3.rack-title {
            font-size: 14px;
            color: #1f2937;
            font-weight: 700;
            margin: 16px 0 8px 0;
            padding: 6px 0;
            border-bottom: 2px solid #9F24A5;
            display: table;
            width: 100%;
        }
        h3.rack-title .name {
            display: table-cell;
            vertical-align: middle;
        }
        h3.rack-title .meta {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            font-size: 9.5px;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        h3.rack-title .badge-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-left: 6px;
        }
        h3.rack-title .badge-status.active { background: #dcfce7; color: #166534; }
        h3.rack-title .badge-status.inactive { background: #fee2e2; color: #991b1b; }

        /* === INDEX TABLE (TOC) === */
        table.toc {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            font-size: 10.5px;
        }
        table.toc thead th {
            background: #f3e8f9;
            color: #6b21a8;
            padding: 8px 10px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border: 1px solid #e9d5f3;
            font-weight: 700;
        }
        table.toc tbody td {
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        table.toc tbody tr.inactive td { background: #f8fafc; color: #64748b; }
        table.toc tbody td.name { font-weight: 700; color: #1e293b; }
        table.toc tbody td.num { font-family: monospace; text-align: center; }
        table.toc tbody td.muted { color: #94a3b8; font-style: italic; }

        /* === STATS GRID === */
        .stats-grid {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
            margin: 6px 0 12px 0;
        }
        .stats-grid .stat {
            display: table-cell;
            background: #ffffff;
            border: 1px solid #e9d5f3;
            border-radius: 6px;
            padding: 10px 12px;
            vertical-align: middle;
        }
        .stats-grid .stat .v {
            font-size: 18px;
            font-weight: 800;
            color: #9F24A5;
            line-height: 1;
        }
        .stats-grid .stat .l {
            font-size: 8.5px;
            color: #6b21a8;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-top: 3px;
            font-weight: 600;
        }
        .stats-grid .stat.green .v { color: #16a34a; }
        .stats-grid .stat.gray .v { color: #64748b; }
        .stats-grid .stat.blue .v { color: #2563eb; }
        .stats-grid .stat.amber .v { color: #d97706; }

        /* === EQUIPMENT TABLE === */
        table.equipment {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-top: 6px;
        }
        table.equipment th {
            background: #f3e8f9;
            color: #6b21a8;
            padding: 6px 5px;
            text-align: left;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border: 1px solid #e9d5f3;
            font-weight: 700;
        }
        table.equipment td {
            padding: 6px 5px;
            border: 1px solid #e5e7eb;
            vertical-align: middle;
            word-wrap: break-word;
        }
        table.equipment td.pos {
            font-weight: 700;
            text-align: center;
            color: #475569;
            background: #f8fafc;
            width: 42px;
        }
        table.equipment td.name { font-weight: 700; color: #1e293b; }
        table.equipment td.sub { color: #475569; font-size: 8.5px; }
        table.equipment td.sub.muted { color: #94a3b8; font-style: italic; }
        table.equipment tr.empty td { background: #f8fafc; }
        table.equipment tr.empty td.name { color: #cbd5e1; font-style: italic; font-weight: 400; }
        table.equipment tr.bg-green td { background: #dcfce7; }
        table.equipment tr.bg-red td { background: #fee2e2; }
        table.equipment tr.bg-blue td { background: #dbeafe; }
        table.equipment tr.bg-yellow td { background: #fef3c7; }
        table.equipment tr.bg-orange td { background: #ffedd5; }
        table.equipment img.thumb {
            width: 96px;
            height: 96px;
            object-fit: contain;
            border: 1px solid #e9d5f3;
            border-radius: 5px;
            background: #ffffff;
            padding: 3px;
        }
        table.equipment .no-image {
            display: inline-block;
            width: 96px;
            height: 96px;
            background: #f1f5f9;
            border: 1px dashed #cbd5e1;
            border-radius: 5px;
            text-align: center;
            line-height: 96px;
            font-size: 28px;
            color: #cbd5e1;
        }

        /* === IP TABLE === */
        table.ip {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            margin-top: 6px;
        }
        table.ip th {
            background: #ecfeff;
            color: #0e7490;
            padding: 6px 5px;
            text-align: left;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border: 1px solid #cffafe;
            font-weight: 700;
        }
        table.ip td {
            padding: 5px;
            border: 1px solid #e5e7eb;
        }
        table.ip td.mono {
            font-family: monospace;
            font-weight: 600;
            color: #0e7490;
        }
        table.ip tr.empty td {
            color: #94a3b8;
            font-style: italic;
            text-align: center;
            padding: 10px;
        }

        /* === NOTES === */
        .notes {
            background: #fffbeb;
            border-left: 3px solid #f59e0b;
            padding: 8px 12px;
            border-radius: 0 4px 4px 0;
            font-size: 9.5px;
            color: #374151;
            line-height: 1.45;
            margin-bottom: 6px;
        }
        .notes .title {
            font-weight: 700;
            color: #92400e;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            font-size: 8.5px;
            margin-bottom: 3px;
            display: block;
        }
        .empty-note {
            color: #9ca3af;
            font-style: italic;
            font-size: 9.5px;
            padding: 6px 12px;
            background: #f9fafb;
            border-radius: 4px;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

    {{-- HEADER (repeats every page) --}}
    <div class="pdf-header">
        <div class="brand">
            <div class="brand-left">
                <div class="doc-tag">{{ __('General report') }}</div>
                <h1>{{ __('Rack layouts — global overview') }}</h1>
                <div class="subtitle">{{ $totals['racks_count'] }} {{ trans_choice('rack|racks', $totals['racks_count']) }} · {{ __('Generated') }} {{ now()->format('Y-m-d') }}</div>
            </div>
            <div class="brand-right">
                <div><strong>{{ config('app.name') }}</strong></div>
                <div>{{ now()->format('Y-m-d H:i') }}</div>
                <div>{{ __('By') }}: {{ $generatedBy ?? '—' }}</div>
            </div>
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="pdf-footer">
        <div class="row">
            <div class="left">{{ config('app.name') }} — {{ __('Rack layouts report') }}</div>
            <div class="center">{{ __('General overview') }}</div>
            <div class="right">{{ __('Page') }} <span class="pageno"></span> / <span class="totalpages"></span></div>
        </div>
    </div>

    {{-- COVER --}}
    <div class="cover">
        @if(!empty($logoDataUri))
            <div style="margin-bottom: 18px;">
                <img src="{{ $logoDataUri }}" alt="{{ config('app.name') }}"
                     style="height: 70px; width: auto; max-width: 220px; object-fit: contain;" />
            </div>
        @endif
        <div class="badge">{{ __('Infrastructure report') }}</div>
        <h1>{{ __('Rack layouts') }}</h1>
        <div class="subtitle">{{ __('Global overview of all data center racks') }}</div>
        <div class="divider"></div>

        <div class="summary-box">
            <div class="row">
                <div class="lbl">{{ __('Total racks') }}</div>
                <div class="val">{{ $totals['racks_count'] }}</div>
            </div>
            <div class="row">
                <div class="lbl">{{ __('Active racks') }}</div>
                <div class="val">{{ $totals['active_racks'] }}</div>
            </div>
            <div class="row">
                <div class="lbl">{{ __('Total capacity (U)') }}</div>
                <div class="val">{{ $totals['total_units_sum'] }} U</div>
            </div>
            <div class="row">
                <div class="lbl">{{ __('Total occupied U') }}</div>
                <div class="val">{{ $totals['total_occupied_u_sum'] }} U</div>
            </div>
            <div class="row">
                <div class="lbl">{{ __('Global occupancy') }}</div>
                <div class="val">{{ number_format($totals['occupancy_percent'], 1) }}%</div>
            </div>
            <div class="row">
                <div class="lbl">{{ __('Installed equipment') }}</div>
                <div class="val">{{ $totals['total_equipment'] }}</div>
            </div>
            <div class="row">
                <div class="lbl">{{ __('IP ranges configured') }}</div>
                <div class="val">{{ $totals['total_ip_ranges'] }}</div>
            </div>
            <div class="row" style="border-top: 1px solid #e9d5f3; padding-top: 10px; margin-top: 8px;">
                <div class="lbl">{{ __('Report generated') }}</div>
                <div class="val">{{ now()->format('Y-m-d H:i') }}</div>
            </div>
        </div>

        <div class="footer-note">
            <div class="name">{{ config('app.name') }}</div>
            <div>{{ __('Confidential document generated by the system.') }}</div>
        </div>
    </div>

    {{-- GLOBAL STATS --}}
    <h2 class="section">{{ __('Global statistics') }}</h2>

    <div class="stats-grid">
        <div class="stat blue">
            <div class="v">{{ $totals['racks_count'] }}</div>
            <div class="l">{{ __('Total racks') }}</div>
        </div>
        <div class="stat green">
            <div class="v">{{ $totals['active_racks'] }}</div>
            <div class="l">{{ __('Active') }}</div>
        </div>
        <div class="stat gray">
            <div class="v">{{ max(0, $totals['racks_count'] - $totals['active_racks']) }}</div>
            <div class="l">{{ __('Inactive') }}</div>
        </div>
        <div class="stat amber">
            <div class="v">{{ number_format($totals['occupancy_percent'], 1) }}%</div>
            <div class="l">{{ __('Occupancy') }}</div>
        </div>
    </div>

    {{-- INDEX OF RACKS (TOC) --}}
    <h2 class="section">{{ __('Index of racks') }}</h2>

    <table class="toc">
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 26%;">{{ __('Rack') }}</th>
                <th style="width: 20%;">{{ __('Location') }}</th>
                <th style="width: 9%; text-align: center;">{{ __('Status') }}</th>
                <th style="width: 9%; text-align: center;">{{ __('Capacity') }}</th>
                <th style="width: 9%; text-align: center;">{{ __('Occupied') }}</th>
                <th style="width: 9%; text-align: center;">{{ __('Equipment') }}</th>
                <th style="width: 14%; text-align: center;">{{ __('Occupancy') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData as $i => $item)
                @php $r = $item['rack']; @endphp
                <tr class="{{ $r->is_active ? '' : 'inactive' }}">
                    <td class="num">{{ $i + 1 }}</td>
                    <td class="name">{{ $r->name }}</td>
                    <td>{{ $r->location ?: '—' }}</td>
                    <td class="num">
                        <span class="badge-status {{ $r->is_active ? 'active' : 'inactive' }}"
                              style="display:inline-block;padding:2px 6px;border-radius:3px;font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:0.4px;background:{{ $r->is_active ? '#dcfce7' : '#fee2e2' }};color:{{ $r->is_active ? '#166534' : '#991b1b' }}">
                            {{ $r->is_active ? __('Active') : __('Inactive') }}
                        </span>
                    </td>
                    <td class="num">{{ $r->total_units }} U</td>
                    <td class="num">{{ $item['stats']['total_occupied_u'] }} U</td>
                    <td class="num">{{ $item['stats']['equipment_count'] }}</td>
                    <td class="num">{{ number_format($item['stats']['occupancy_percent'], 1) }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="muted" style="text-align:center; padding: 14px;">
                        {{ __('No racks registered.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- PER-RACK DETAIL --}}
    @forelse($reportData as $idx => $item)
        @php
            $rack = $item['rack'];
            $stats = $item['stats'];
            $positions = $item['positions'];
            $ipRanges = $item['ipRanges'];
            $images = $item['images'];
        @endphp

        <div class="page-break"></div>

        <h3 class="rack-title">
            <span class="name">
                {{ $idx + 1 }}. {{ $rack->name }}
                <span class="badge-status {{ $rack->is_active ? 'active' : 'inactive' }}">
                    {{ $rack->is_active ? __('Active') : __('Inactive') }}
                </span>
            </span>
            <span class="meta">
                @if($rack->location){{ $rack->location }} · @endif
                {{ $rack->total_units }} U
            </span>
        </h3>

        @if($rack->description)
            <div class="notes" style="margin-bottom: 10px;">
                <span class="title">{{ __('Description') }}</span>
                {{ $rack->description }}
            </div>
        @endif

        <div class="stats-grid">
            <div class="stat">
                <div class="v">{{ $stats['total_occupied_u'] }} / {{ $rack->total_units }}</div>
                <div class="l">{{ __('Occupied U') }}</div>
            </div>
            <div class="stat green">
                <div class="v">{{ number_format($stats['occupancy_percent'], 1) }}%</div>
                <div class="l">{{ __('Occupancy') }}</div>
            </div>
            <div class="stat blue">
                <div class="v">{{ $stats['equipment_count'] }}</div>
                <div class="l">{{ __('Equipment') }}</div>
            </div>
            <div class="stat gray">
                <div class="v">{{ $stats['empty_positions'] }}</div>
                <div class="l">{{ __('Empty') }}</div>
            </div>
        </div>

        <h2 class="section" style="margin-top: 12px;">{{ __('Equipment') }}</h2>

        <table class="equipment">
            <thead>
                <tr>
                    <th style="width: 110px; text-align: center;">{{ __('Img') }}</th>
                    <th style="width: 44px; text-align: center;">{{ __('U') }}</th>
                    <th>{{ __('Equipment') }}</th>
                    <th style="width: 14%;">{{ __('Model / IP') }}</th>
                    <th style="width: 12%;">{{ __('Role') }}</th>
                    <th style="width: 9%;">{{ __('Vendor') }}</th>
                    <th style="width: 9%;">{{ __('Serial') }}</th>
                </tr>
            </thead>
            <tbody>
                @php $skip = []; @endphp
                @forelse($positions as $i => $eq)
                    @php
                        if (in_array($i, $skip, true)) { continue; }
                        $position = $i + 1;
                        $isEmpty = ! $eq || empty($eq->equipment_name);
                        $colorClass = $eq && $eq->color ? 'bg-' . $eq->color : '';
                        $sizeU = $eq ? max(1, (int) $eq->size_u) : 1;
                        $isSpanned = $sizeU > 1;
                        $rangeLabel = $isSpanned
                            ? "U{$position}–U".($position + $sizeU - 1)." ({$sizeU}U)"
                            : "U{$position}";
                        if ($isSpanned) {
                            for ($s = 1; $s < $sizeU; $s++) {
                                $skip[] = $i + $s;
                            }
                        }
                        $imageSrc = null;
                        if ($eq && !empty($eq->image_url) && !empty($images[$eq->id] ?? null)) {
                            $imageSrc = $images[$eq->id];
                        }
                    @endphp
                    <tr class="{{ $isEmpty ? 'empty' : '' }} {{ $colorClass }}">
                        <td style="text-align: center;">
                            @if($imageSrc)
                                <img class="thumb" src="{{ $imageSrc }}" alt="{{ $eq->equipment_name }}">
                            @else
                                <span class="no-image">·</span>
                            @endif
                        </td>
                        <td class="pos" @if($isSpanned) rowspan="{{ $sizeU }}" @endif>{{ $rangeLabel }}</td>
                        <td class="name">
                            {{ $eq->equipment_name ?? '—' }}
                            @if($eq && $eq->mac_address)
                                <div class="sub">MAC: {{ strtoupper($eq->mac_address) }}</div>
                            @endif
                        </td>
                        <td class="sub">
                            @if($eq && $eq->equipment_model)<div>{{ $eq->equipment_model }}</div>@endif
                            @if($eq && $eq->ip_address)
                                <div style="color:#0e7490;font-weight:600;">{{ $eq->ip_address }}</div>
                            @endif
                            @if(!$eq || (!$eq->equipment_model && !$eq->ip_address))
                                <span class="sub muted">—</span>
                            @endif
                        </td>
                        <td class="sub">{{ $eq->equipment_role ?? '—' }}</td>
                        <td class="sub">{{ $eq->vendor ?? '—' }}</td>
                        <td class="sub" style="font-family: monospace;">{{ $eq->serial_number ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="sub muted" style="text-align:center; padding: 14px;">{{ __('No equipment.') }}</td></tr>
                @endforelse
            </tbody>
        </table>

        <h2 class="section" style="margin-top: 14px;">{{ __('IP addressing') }}</h2>

        @if($ipRanges->isEmpty())
            <div class="empty-note">{{ __('No IP ranges registered.') }}</div>
        @else
            <table class="ip">
                <thead>
                    <tr>
                        <th style="width: 22%;">{{ __('IP range') }}</th>
                        <th style="width: 12%;">{{ __('Mask') }}</th>
                        <th style="width: 10%;">{{ __('VLAN') }}</th>
                        <th style="width: 14%;">{{ __('Gateway') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th style="width: 10%;">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ipRanges as $range)
                        <tr>
                            <td class="mono">{{ $range->ip_range ?? '—' }}</td>
                            <td>{{ $range->mask ?? '—' }}</td>
                            <td>{{ $range->vlan ?? '—' }}</td>
                            <td class="mono">{{ $range->gateway ?? '—' }}</td>
                            <td>{{ $range->description ?? '—' }}</td>
                            <td>{{ $range->is_active ? __('Active') : __('Inactive') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @php
            $rackNotes = $rack->equipment()
                ->whereNotNull('notes')
                ->where('notes', '!=', '')
                ->get();
        @endphp
        @if($rackNotes->isNotEmpty())
            <h2 class="section" style="margin-top: 14px;">{{ __('Equipment notes') }}</h2>
            @foreach($rackNotes as $n)
                <div class="notes">
                    <span class="title">
                        {{ $n->equipment_name ?: __('Unnamed equipment') }} ·
                        U{{ $n->position }}{{ $n->size_u > 1 ? '–U'.($n->position + $n->size_u - 1) : '' }}
                    </span>
                    {{ $n->notes }}
                </div>
            @endforeach
        @endif

    @empty
        <div class="page-break"></div>
        <div class="empty-note">{{ __('No racks registered to display.') }}</div>
    @endforelse

</body>
</html>