<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lluvia finalizada en {{ $siteLabel }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
            background-color: #0f172a;
            color: #e2e8f0;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 720px;
            margin: 24px auto;
            background: #1e293b;
            padding: 28px;
            border-radius: 14px;
            border-left: 6px solid #10b981;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
        }

        h1,
        h2,
        h3 {
            color: #f8fafc;
            margin: 0 0 12px;
        }

        h1 {
            font-size: 22px;
        }

        h2 {
            font-size: 16px;
            color: #6ee7b7;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        p {
            line-height: 1.55;
            margin: 0 0 10px;
            color: #cbd5e1;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .badge.clear {
            background: #047857;
            color: #d1fae5;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin: 18px 0;
        }

        .info-card {
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 10px;
            padding: 12px 14px;
        }

        .info-card .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #94a3b8;
            margin-bottom: 4px;
        }

        .info-card .value {
            font-size: 18px;
            font-weight: 700;
            color: #f1f5f9;
            font-family: 'Consolas', 'Monaco', monospace;
        }

        .section {
            margin: 20px 0;
            padding: 16px;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 10px;
        }

        .timeline {
            margin-top: 10px;
        }

        .timeline-bar {
            display: flex;
            align-items: flex-end;
            gap: 2px;
            height: 60px;
        }

        .timeline-bar .bar {
            flex: 1;
            background: #475569;
            border-radius: 2px;
            min-height: 3px;
        }

        .timeline-bar .bar.light { background: #38bdf8; }
        .timeline-bar .bar.moderate { background: #2563eb; }
        .timeline-bar .bar.heavy { background: #1e3a8a; }

        .timeline-labels {
            display: flex;
            gap: 2px;
            margin-top: 4px;
            font-size: 9px;
            color: #64748b;
            font-family: 'Consolas', monospace;
        }

        .timeline-labels span {
            flex: 1;
            text-align: left;
        }

        .footer {
            margin-top: 24px;
            font-size: 11px;
            color: #64748b;
            text-align: center;
            border-top: 1px solid #334155;
            padding-top: 14px;
        }

        .summary-box {
            background: #064e3b;
            color: #ecfdf5;
            padding: 14px 16px;
            border-radius: 10px;
            margin: 14px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>
            ☀️ Lluvia finalizada en {{ $siteLabel }}
        </h1>
        <p>
            El episodio de lluvia en el sitio de uplink
            <strong>{{ $siteLabel }}</strong>
            ha terminado.
        </p>

        <div>
            <span class="badge clear">Sin lluvia</span>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <div class="label">Inicio</div>
                <div class="value" style="font-size: 14px;">
                    {{ $event->rain_started_at->format('Y-m-d H:i') }}
                </div>
            </div>
            <div class="info-card">
                <div class="label">Fin</div>
                <div class="value" style="font-size: 14px;">
                    {{ $event->rain_ended_at?->format('Y-m-d H:i') ?? '—' }}
                </div>
            </div>
            <div class="info-card">
                <div class="label">Duración total</div>
                <div class="value">{{ $duration }}</div>
            </div>
            <div class="info-card">
                <div class="label">Pico de precipitación</div>
                <div class="value">
                    {{ $event->peak_precipitation_mm !== null ? number_format((float) $event->peak_precipitation_mm, 2) . ' mm' : '—' }}
                </div>
            </div>
        </div>

        <div class="summary-box">
            <strong>📋 Resumen del evento</strong>
            <ul style="margin: 8px 0 0 0; padding-left: 18px;">
                <li>Sitio: <strong>{{ $siteLabel }}</strong></li>
                <li>Duración: <strong>{{ $duration }}</strong></li>
                @if($event->total_precipitation_mm !== null)
                    <li>Lluvia total estimada: <strong>{{ number_format((float) $event->total_precipitation_mm, 2) }} mm</strong></li>
                @endif
                @if($event->precipitation_hours !== null)
                    <li>Horas con lluvia estimadas: <strong>{{ number_format((float) $event->precipitation_hours, 1) }} h</strong></li>
                @endif
                @if($event->max_probability !== null)
                    <li>Probabilidad máxima durante el evento: <strong>{{ (int) $event->max_probability }} %</strong></li>
                @endif
                @if($event->condition_label)
                    <li>Condición climática: <strong>{{ $event->condition_label }}</strong></li>
                @endif
            </ul>
        </div>

        @if($forecast && !empty($forecast['hourly_timeline']['hours']))
            @php
                $hours = $forecast['hourly_timeline']['hours'];
                $maxMm = 0.0;
                foreach ($hours as $h) { if (($h['precipitation'] ?? 0) > $maxMm) { $maxMm = (float) $h['precipitation']; } }
            @endphp
            @if($maxMm > 0)
                <div class="section">
                    <h2>📊 Pronóstico próximas 72h</h2>
                    <p>El siguiente gráfico muestra la lluvia prevista para las siguientes horas en {{ $siteLabel }}.</p>
                    <div class="timeline">
                        <div class="timeline-bar">
                            @foreach($hours as $h)
                                @php
                                    $mm = (float) ($h['precipitation'] ?? 0);
                                    $intensity = $h['rain_intensity'] ?? 'none';
                                    $height = $mm > 0 ? max(8, (int) round(($mm / $maxMm) * 56)) : 3;
                                @endphp
                                <div class="bar {{ $intensity }}" style="height: {{ $height }}px;" title="{{ $h['hour'] }} · {{ number_format($mm, 1) }} mm"></div>
                            @endforeach
                        </div>
                        <div class="timeline-labels">
                            @php
                                $showEvery = max(1, (int) ceil(count($hours) / 8));
                            @endphp
                            @for($i = 0; $i < count($hours); $i++)
                                @if($i % $showEvery === 0)
                                    <span>{{ substr((string) ($hours[$i]['hour'] ?? ''), 0, 2) }}</span>
                                @else
                                    <span></span>
                                @endif
                            @endfor
                        </div>
                    </div>
                </div>
            @else
                <div class="section">
                    <h2>📊 Pronóstico próximas 72h</h2>
                    <p style="color: #6ee7b7;">✅ Sin lluvia prevista en las próximas 72 horas.</p>
                </div>
            @endif
        @endif

        <div class="footer">
            Notificación generada por SCOTT · Panel de Moduladores
            <br>
            {{ $event->rain_ended_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s') }}
        </div>
    </div>
</body>

</html>