<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Rack report') }} — {{ $rack->name }}</title>
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
            width: 60%;
        }
        .pdf-header .brand-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 40%;
            font-size: 9px;
            color: #6b7280;
        }
        .pdf-header h1 {
            margin: 0;
            font-size: 17px;
            color: #9F24A5;
            font-weight: 700;
        }
        .pdf-header .subtitle {
            margin: 2px 0 0 0;
            font-size: 9.5px;
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

        /* === COVER PAGE === */
        .cover {
            page-break-after: always;
            padding: 60px 10px;
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
            font-size: 38px;
            color: #1f2937;
            margin: 14px 0 6px 0;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .cover .rack-name {
            font-size: 22px;
            color: #9F24A5;
            margin: 4px 0 28px 0;
            font-weight: 700;
        }
        .cover .divider {
            width: 80px;
            height: 4px;
            background: #9F24A5;
            margin: 16px auto;
            border-radius: 2px;
        }
        .cover .meta-grid {
            margin: 36px auto 0 auto;
            display: table;
            border-collapse: separate;
            border-spacing: 14px 6px;
            max-width: 80%;
        }
        .cover .meta-grid .row { display: table-row; }
        .cover .meta-grid .label,
        .cover .meta-grid .value {
            display: table-cell;
            padding: 10px 16px;
            text-align: left;
            vertical-align: middle;
        }
        .cover .meta-grid .label {
            background: #f3e8f9;
            color: #6b21a8;
            font-weight: 700;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border-radius: 6px 0 0 6px;
            min-width: 130px;
        }
        .cover .meta-grid .value {
            background: #fafafa;
            color: #1f2937;
            font-size: 11px;
            font-weight: 600;
            border: 1px solid #e9d5f3;
            border-radius: 0 6px 6px 0;
            border-left: none;
        }
        .cover .footer-note {
            margin-top: 60px;
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
            margin: 18px 0 10px 0;
            padding: 6px 12px;
            background: linear-gradient(to right, #f3e8f9 0%, #fdf5fe 100%);
            border-left: 4px solid #9F24A5;
            border-radius: 0 4px 4px 0;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        /* === INFO GRID === */
        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .info-grid .row { display: table-row; }
        .info-grid .cell {
            display: table-cell;
            border: 1px solid #e9d5f3;
            padding: 8px 10px;
            vertical-align: top;
            width: 25%;
            background: #fafafa;
        }
        .info-grid .cell .lbl {
            font-size: 8.5px;
            color: #6b21a8;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            font-weight: 700;
            margin-bottom: 2px;
            display: block;
        }
        .info-grid .cell .val {
            font-size: 11px;
            color: #1f2937;
            font-weight: 600;
        }

        /* === STATS === */
        .stats-grid {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
            margin: 6px 0 14px 0;
        }
        .stats-grid .stat {
            display: table-cell;
            background: #ffffff;
            border: 1px solid #e9d5f3;
            border-radius: 6px;
            padding: 12px 14px;
            vertical-align: middle;
        }
        .stats-grid .stat .v {
            font-size: 22px;
            font-weight: 800;
            color: #9F24A5;
            line-height: 1;
        }
        .stats-grid .stat .l {
            font-size: 9px;
            color: #6b21a8;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-top: 4px;
            font-weight: 600;
        }
        .stats-grid .stat.green .v { color: #16a34a; }
        .stats-grid .stat.gray .v { color: #64748b; }
        .stats-grid .stat.blue .v { color: #2563eb; }

        /* === EQUIPMENT TABLE === */
        table.equipment {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            margin-top: 6px;
        }
        table.equipment th {
            background: #f3e8f9;
            color: #6b21a8;
            padding: 7px 6px;
            text-align: left;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border: 1px solid #e9d5f3;
            font-weight: 700;
        }
        table.equipment td {
            padding: 7px 6px;
            border: 1px solid #e5e7eb;
            vertical-align: middle;
            word-wrap: break-word;
        }
        table.equipment td.pos {
            font-weight: 700;
            text-align: center;
            color: #475569;
            background: #f8fafc;
            width: 48px;
        }
        table.equipment td.name {
            font-weight: 700;
            color: #1e293b;
        }
        table.equipment td.sub {
            color: #475569;
            font-size: 9px;
        }
        table.equipment td.sub.muted {
            color: #94a3b8;
            font-style: italic;
        }
        table.equipment tr.empty td { background: #f8fafc; }
        table.equipment tr.empty td.name { color: #cbd5e1; font-style: italic; font-weight: 400; }
        table.equipment tr.bg-green td { background: #dcfce7; }
        table.equipment tr.bg-red td { background: #fee2e2; }
        table.equipment tr.bg-blue td { background: #dbeafe; }
        table.equipment tr.bg-yellow td { background: #fef3c7; }
        table.equipment tr.bg-orange td { background: #ffedd5; }
        table.equipment img.thumb {
            width: 42px;
            height: 42px;
            object-fit: contain;
            border: 1px solid #e9d5f3;
            border-radius: 4px;
            background: #ffffff;
            padding: 2px;
        }
        table.equipment .no-image {
            display: inline-block;
            width: 42px;
            height: 42px;
            background: #f1f5f9;
            border: 1px dashed #cbd5e1;
            border-radius: 4px;
            text-align: center;
            line-height: 42px;
            font-size: 16px;
            color: #cbd5e1;
        }

        /* === IP TABLE === */
        table.ip {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-top: 6px;
        }
        table.ip th {
            background: #ecfeff;
            color: #0e7490;
            padding: 7px 6px;
            text-align: left;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border: 1px solid #cffafe;
            font-weight: 700;
        }
        table.ip td {
            padding: 6px;
            border: 1px solid #e5e7eb;
        }
        table.ip tr.empty td {
            color: #94a3b8;
            font-style: italic;
            text-align: center;
            padding: 14px;
        }

        /* === NOTES BLOCK === */
        .notes {
            background: #fffbeb;
            border-left: 3px solid #f59e0b;
            padding: 10px 14px;
            border-radius: 0 4px 4px 0;
            font-size: 10px;
            color: #374151;
            line-height: 1.5;
        }
        .notes .title {
            font-weight: 700;
            color: #92400e;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            font-size: 9px;
            margin-bottom: 4px;
            display: block;
        }
        .empty-note {
            color: #9ca3af;
            font-style: italic;
            font-size: 10px;
            padding: 8px 14px;
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
                <div class="doc-tag">{{ __('Rack report') }}</div>
                <h1>{{ $rack->name }}</h1>
                <div class="subtitle">
                    @if($rack->location){{ $rack->location }} · @endif
                    {{ $rack->total_units }}U
                </div>
            </div>
            <div class="brand-right">
                <div><strong>{{ config('app.name') }}</strong></div>
                <div>{{ __('Generated') }}: {{ now()->format('Y-m-d H:i') }}</div>
                <div>{{ __('By') }}: {{ $generatedBy ?? '—' }}</div>
            </div>
        </div>
    </div>

    {{-- FOOTER (repeats every page) --}}
    <div class="pdf-footer">
        <div class="row">
            <div class="left">{{ config('app.name') }} — {{ __('Rack report') }}</div>
            <div class="center">{{ $rack->name }}</div>
            <div class="right">{{ __('Page') }} <span class="pageno"></span> / <span class="totalpages"></span></div>
        </div>
    </div>

    {{-- COVER PAGE --}}
    <div class="cover">
        @if(!empty($logoDataUri))
            <div style="margin-bottom: 18px;">
                <img src="{{ $logoDataUri }}" alt="{{ config('app.name') }}"
                     style="height: 70px; width: auto; max-width: 220px; object-fit: contain;" />
            </div>
        @endif
        <div class="badge">{{ __('Rack layout report') }}</div>
        <h1>{{ __('Detailed rack report') }}</h1>
        <div class="rack-name">{{ $rack->name }}</div>
        <div class="divider"></div>

        @if($rack->description)
            <p style="max-width: 75%; margin: 8px auto; color: #4b5563; font-size: 11.5px; line-height: 1.5;">
                {{ $rack->description }}
            </p>
        @endif

        <div class="meta-grid">
            <div class="row">
                <div class="label">{{ __('Location') }}</div>
                <div class="value">{{ $rack->location ?? '—' }}</div>
            </div>
            <div class="row">
                <div class="label">{{ __('Total units') }}</div>
                <div class="value">{{ $rack->total_units }} U</div>
            </div>
            <div class="row">
                <div class="label">{{ __('Status') }}</div>
                <div class="value">{{ $rack->is_active ? __('Active') : __('Inactive') }}</div>
            </div>
            <div class="row">
                <div class="label">{{ __('Equipment installed') }}</div>
                <div class="value">{{ $stats['equipment_count'] }} {{ trans_choice('item|items', $stats['equipment_count']) }}</div>
            </div>
            @if($rack->hasCoordinates())
                <div class="row">
                    <div class="label">{{ __('Coordinates') }}</div>
                    <div class="value">{{ number_format((float) $rack->latitude, 6) }}, {{ number_format((float) $rack->longitude, 6) }}</div>
                </div>
            @endif
            <div class="row">
                <div class="label">{{ __('Report date') }}</div>
                <div class="value">{{ now()->format('Y-m-d H:i') }}</div>
            </div>
        </div>

        <div class="footer-note">
            <div class="name">{{ config('app.name') }}</div>
            <div>{{ __('Confidential document generated by the system.') }}</div>
        </div>
    </div>

    {{-- SUMMARY --}}
    <h2 class="section">{{ __('Summary') }}</h2>

    <div class="info-grid">
        <div class="row">
            <div class="cell">
                <span class="lbl">{{ __('Rack name') }}</span>
                <div class="val">{{ $rack->name }}</div>
            </div>
            <div class="cell">
                <span class="lbl">{{ __('Location') }}</span>
                <div class="val">{{ $rack->location ?? '—' }}</div>
            </div>
            <div class="cell">
                <span class="lbl">{{ __('Total U') }}</span>
                <div class="val">{{ $rack->total_units }} U</div>
            </div>
            <div class="cell">
                <span class="lbl">{{ __('Status') }}</span>
                <div class="val">{{ $rack->is_active ? __('Active') : __('Inactive') }}</div>
            </div>
        </div>
        @if($rack->hasCoordinates())
            <div class="row">
                <div class="cell" style="width: 50%;">
                    <span class="lbl">{{ __('Latitude') }}</span>
                    <div class="val">{{ number_format((float) $rack->latitude, 6) }}</div>
                </div>
                <div class="cell" style="width: 50%;">
                    <span class="lbl">{{ __('Longitude') }}</span>
                    <div class="val">{{ number_format((float) $rack->longitude, 6) }}</div>
                </div>
            </div>
        @endif
    </div>

    <div class="stats-grid">
        <div class="stat">
            <div class="v">{{ $stats['total_occupied_u'] }} / {{ $rack->total_units }}</div>
            <div class="l">{{ __('Occupied units (U)') }}</div>
        </div>
        <div class="stat green">
            <div class="v">{{ number_format($stats['occupancy_percent'], 1) }}%</div>
            <div class="l">{{ __('Occupancy') }}</div>
        </div>
        <div class="stat blue">
            <div class="v">{{ $stats['equipment_count'] }}</div>
            <div class="l">{{ __('Installed equipment') }}</div>
        </div>
        <div class="stat gray">
            <div class="v">{{ $stats['empty_positions'] }}</div>
            <div class="l">{{ __('Empty positions') }}</div>
        </div>
    </div>

    @if($rack->description)
        <h2 class="section">{{ __('Description') }}</h2>
        <div class="notes">
            <span class="title">{{ __('Notes') }}</span>
            {{ $rack->description }}
        </div>
    @endif

    {{-- EQUIPMENT DETAIL --}}
    <h2 class="section">{{ __('Equipment detail') }}</h2>

    <table class="equipment">
        <thead>
            <tr>
                <th style="width: 42px; text-align: center;">{{ __('Image') }}</th>
                <th style="width: 50px; text-align: center;">{{ __('U') }}</th>
                <th>{{ __('Equipment') }}</th>
                <th style="width: 14%;">{{ __('Model / IP') }}</th>
                <th style="width: 14%;">{{ __('Role / Serial') }}</th>
                <th style="width: 11%;">{{ __('Vendor') }}</th>
                <th style="width: 11%;">{{ __('Installed') }}</th>
            </tr>
        </thead>
        <tbody>
            @php $skip = []; @endphp
            @forelse($positions as $idx => $eq)
                @php
                    if (in_array($idx, $skip, true)) { continue; }
                    $position = $idx + 1;
                    $isEmpty = ! $eq || empty($eq->equipment_name);
                    $colorClass = $eq && $eq->color ? 'bg-' . $eq->color : '';
                    $sizeU = $eq ? max(1, (int) $eq->size_u) : 1;
                    $isSpanned = $sizeU > 1;
                    $rangeLabel = $isSpanned
                        ? "U{$position}–U" . ($position + $sizeU - 1) . " ({$sizeU}U)"
                        : "U{$position}";
                    if ($isSpanned) {
                        for ($s = 1; $s < $sizeU; $s++) {
                            $skip[] = $idx + $s;
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
                            <span class="no-image"><i style="font-style: normal;">·</i></span>
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
                        @if($eq && $eq->equipment_model)
                            <div>{{ $eq->equipment_model }}</div>
                        @endif
                        @if($eq && $eq->ip_address)
                            <div style="color:#0e7490; font-weight:600;">{{ $eq->ip_address }}</div>
                        @endif
                        @if(!$eq || (!$eq->equipment_model && !$eq->ip_address))
                            <span class="sub muted">—</span>
                        @endif
                    </td>
                    <td class="sub">
                        @if($eq && $eq->equipment_role)
                            <div style="color:#1f2937; font-weight:600;">{{ $eq->equipment_role }}</div>
                        @endif
                        @if($eq && $eq->serial_number)
                            <div style="font-family: monospace;">{{ $eq->serial_number }}</div>
                        @endif
                        @if(!$eq || (!$eq->equipment_role && !$eq->serial_number))
                            <span class="sub muted">—</span>
                        @endif
                    </td>
                    <td class="sub">{{ $eq->vendor ?? '—' }}</td>
                    <td class="sub">
                        @if($eq && $eq->installation_date)
                            {{ $eq->installation_date->format('Y-m-d') }}
                        @else
                            <span class="sub muted">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="sub muted" style="text-align:center; padding: 18px;">
                        {{ __('No positions registered.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- IP ADDRESSING --}}
    <div class="page-break"></div>
    <h2 class="section">{{ __('IP addressing') }}</h2>

    @if($ipRanges->isEmpty())
        <div class="empty-note">{{ __('No IP ranges registered for this rack.') }}</div>
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
                        <td style="font-family: monospace; font-weight: 600; color:#0e7490;">{{ $range->ip_range ?? '—' }}</td>
                        <td>{{ $range->mask ?? '—' }}</td>
                        <td>{{ $range->vlan ?? '—' }}</td>
                        <td style="font-family: monospace;">{{ $range->gateway ?? '—' }}</td>
                        <td>{{ $range->description ?? '—' }}</td>
                        <td>{{ $range->is_active ? __('Active') : __('Inactive') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- NOTES SECTION (general) --}}
    @if($equipmentNotes->isNotEmpty())
        <h2 class="section">{{ __('Equipment notes') }}</h2>
        @foreach($equipmentNotes as $note)
            <div class="notes" style="margin-bottom: 8px;">
                <span class="title">
                    {{ $note['name'] }} · {{ $note['label'] }}
                </span>
                {{ $note['notes'] }}
            </div>
        @endforeach
    @endif

</body>
</html>