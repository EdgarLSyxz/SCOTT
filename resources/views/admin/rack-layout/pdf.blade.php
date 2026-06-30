<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('Rack layout') }} — {{ $rack->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            color: #1f2937;
            margin: 0;
            padding: 20px;
        }
        .header {
            border-bottom: 3px solid #9F24A5;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #9F24A5;
        }
        .header .meta {
            font-size: 10px;
            color: #6b7280;
            margin-top: 4px;
        }
        .title-banner {
            display: inline-block;
            background: #ec4899;
            color: #fff;
            font-weight: 700;
            font-size: 14px;
            padding: 4px 14px;
            border-radius: 4px;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table.rack {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            font-family: 'Courier New', Courier, monospace;
        }
        table.rack th {
            background: #f3e8f9;
            color: #6b21a8;
            padding: 6px 8px;
            text-align: left;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border: 1px solid #e9d5f3;
        }
        table.rack td {
            padding: 6px 8px;
            border: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        table.rack td.pos {
            font-weight: 700;
            text-align: center;
            color: #475569;
            background: #f8fafc;
            width: 40px;
        }
        table.rack td.label {
            font-weight: 700;
            color: #1e293b;
        }
        table.rack td.sub {
            color: #64748b;
            font-size: 9px;
        }
        table.rack tr.empty td.label { color: #cbd5e1; font-style: italic; font-weight: 400; }
        table.rack tr.bg-green td { background: #bbf7d0; }
        table.rack tr.bg-red td { background: #fecaca; }
        table.rack tr.bg-blue td { background: #bfdbfe; }
        table.rack tr.bg-yellow td { background: #fde68a; }
        table.rack tr.bg-orange td { background: #fed7aa; }
        .footer {
            margin-top: 14px;
            font-size: 9px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $rack->name }}</h1>
        <div class="meta">
            @if($rack->location) {{ $rack->location }} · @endif
            {{ $rack->total_units }}U · {{ __('Generated') }}: {{ now()->format('Y-m-d H:i') }}
        </div>
    </div>

    <div class="title-banner">{{ __('Rack positions') }}</div>

    <table class="rack">
        <thead>
            <tr>
                <th style="width: 40px; text-align: center;">U</th>
                <th>{{ __('Equipment') }}</th>
                <th style="width: 22%;">{{ __('Model / IP') }}</th>
                <th style="width: 22%;">{{ __('Role / Serial') }}</th>
            </tr>
        </thead>
        <tbody>
            @php $pdfSkip = []; @endphp
            @foreach($positions as $idx => $equipment)
                @php
                    if (in_array($idx, $pdfSkip, true)) {
                        continue;
                    }
                    $position = $idx + 1;
                    $isEmpty = ! $equipment || empty($equipment->equipment_name);
                    $colorClass = $equipment && $equipment->color ? 'bg-' . $equipment->color : '';
                    $sizeU = $equipment ? max(1, (int) $equipment->size_u) : 1;
                    $isSpanned = $sizeU > 1;
                    $rangeLabel = $isSpanned ? "U{$position}–U" . ($position + $sizeU - 1) . " ({$sizeU}U)" : "U{$position}";
                    if ($isSpanned) {
                        for ($s = 1; $s < $sizeU; $s++) {
                            $pdfSkip[] = $idx + $s;
                        }
                    }
                @endphp
                <tr class="{{ $isEmpty ? 'empty' : '' }} {{ $colorClass }}">
                    <td class="pos" @if($isSpanned) rowspan="{{ $sizeU }}" @endif>{{ $rangeLabel }}</td>
                    <td class="label">
                        {{ $equipment->equipment_name ?? '—' }}
                    </td>
                    <td class="sub">
                        {{ trim(implode(' · ', array_filter([$equipment->equipment_model ?? null, $equipment->ip_address ?? null]))) }}
                    </td>
                    <td class="sub">
                        {{ trim(implode(' · ', array_filter([$equipment->equipment_role ?? null, $equipment->serial_number ?? null]))) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">{{ config('app.name') }} — {{ __('Rack layout export') }}</div>
</body>
</html>
