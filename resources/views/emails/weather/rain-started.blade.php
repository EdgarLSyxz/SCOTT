<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lluvia detectada en {{ $siteLabel }}</title>
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
            border-left: 6px solid #38bdf8;
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
            color: #93c5fd;
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

        .badge.rain {
            background: #1d4ed8;
            color: #dbeafe;
        }

        .badge.intensity-light {
            background: #0ea5e9;
            color: #f0f9ff;
        }

        .badge.intensity-moderate {
            background: #2563eb;
            color: #eff6ff;
        }

        .badge.intensity-heavy {
            background: #1e3a8a;
            color: #fff;
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

        .forecast-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 13px;
        }

        .forecast-table th {
            text-align: left;
            padding: 6px 8px;
            font-size: 10px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border-bottom: 1px solid #334155;
        }

        .forecast-table td {
            padding: 8px;
            border-bottom: 1px solid #1e293b;
            color: #e2e8f0;
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

        .alert {
            background: #1d4ed8;
            color: #fff;
            padding: 14px 16px;
            border-radius: 10px;
            margin: 14px 0;
        }

        .alert strong { color: #fff; }
    </style>
</head>

<body>
    <div class="container">
        <h1>
            🌧️ Lluvia detectada en {{ $siteLabel }}
        </h1>
        <p>
            El sistema ha detectado el inicio de precipitación en el sitio de uplink
            <strong>{{ $siteLabel }}</strong>
            a las <strong>{{ $event->rain_started_at->format('H:i') }}</strong>
            del {{ $event->rain_started_at->format('Y-m-d') }}.
        </p>

        <div>
            <span class="badge rain">Lloviendo</span>
            @if(!empty($event->intensity) && $event->intensity !== 'none')
                <span class="badge intensity-{{ $event->intensity }}">
                    @switch($event->intensity)
                        @case('light') Lluvia ligera @break
                        @case('moderate') Lluvia moderada @break
                        @case('heavy') Lluvia fuerte @break
                    @endswitch
                </span>
            @endif
        </div>

        <div class="info-grid">
            <div class="info-card">
                <div class="label">Temperatura</div>
                <div class="value">
                    {{ $event->temperature_c !== null ? number_format((float) $event->temperature_c, 1) . ' °C' : '—' }}
                </div>
            </div>
            <div class="info-card">
                <div class="label">Humedad</div>
                <div class="value">
                    {{ $event->humidity !== null ? (int) $event->humidity . ' %' : '—' }}
                </div>
            </div>
            <div class="info-card">
                <div class="label">Viento</div>
                <div class="value">
                    {{ $event->wind_kmh !== null ? number_format((float) $event->wind_kmh, 1) . ' km/h' : '—' }}
                </div>
            </div>
            <div class="info-card">
                <div class="label">Condición</div>
                <div class="value" style="font-size: 14px; font-family: inherit;">
                    {{ $event->condition_label ?? '—' }}
                </div>
            </div>
        </div>

        @if($forecast && !empty($forecast['daily']))
            @php
                $today = $forecast['daily'][0] ?? null;
                $windows = $today['rain_windows'] ?? [];
                $peak = $today['peak_hour'] ?? null;
            @endphp
            @if($today)
                <div class="section">
                    <h2>📊 Pronóstico del día</h2>
                    <div class="info-grid">
                        <div class="info-card">
                            <div class="label">Probabilidad máxima</div>
                            <div class="value">
                                {{ $today['precipitation_probability_max'] !== null ? (int) $today['precipitation_probability_max'] . ' %' : '—' }}
                            </div>
                        </div>
                        <div class="info-card">
                            <div class="label">Lluvia acumulada esperada</div>
                            <div class="value">
                                {{ $today['precipitation_sum'] !== null ? number_format((float) $today['precipitation_sum'], 1) . ' mm' : '—' }}
                            </div>
                        </div>
                        @if($peak)
                            <div class="info-card">
                                <div class="label">Pico estimado</div>
                                <div class="value">
                                    {{ substr((string) $peak['hour'], 0, 2) }}:00 ·
                                    {{ number_format((float) $peak['precipitation'], 1) }} mm
                                </div>
                            </div>
                        @endif
                        <div class="info-card">
                            <div class="label">Horas con lluvia</div>
                            <div class="value">
                                {{ $today['precipitation_hours'] !== null ? number_format((float) $today['precipitation_hours'], 1) . ' h' : '—' }}
                            </div>
                        </div>
                    </div>

                    @if(count($windows) > 0)
                        <h3 style="font-size: 13px; margin-top: 16px;">Franjas previstas de lluvia</h3>
                        <table class="forecast-table">
                            <thead>
                                <tr>
                                    <th>Inicio</th>
                                    <th>Fin</th>
                                    <th>Acumulado</th>
                                    <th>Probabilidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($windows as $win)
                                    <tr>
                                        <td>{{ $win['start_hour'] }}</td>
                                        <td>{{ $win['end_hour'] }}</td>
                                        <td>{{ number_format((float) $win['precipitation_sum'], 1) }} mm</td>
                                        <td>{{ (int) $win['probability_max'] }} %</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    @php
                        $hours = $forecast['hourly_timeline']['hours'] ?? [];
                        $maxMm = 0.0;
                        foreach ($hours as $h) { if (($h['precipitation'] ?? 0) > $maxMm) { $maxMm = (float) $h['precipitation']; } }
                    @endphp
                    @if(count($hours) > 0 && $maxMm > 0)
                        <h3 style="font-size: 13px; margin-top: 16px;">Línea de tiempo (72h)</h3>
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
                    @endif
                </div>
            @endif
        @endif

        <div class="alert">
            <strong>📡 Acción sugerida:</strong> Verificar el estado de las conmutaciones
            y la señal del uplink en {{ $siteLabel }}. Recibirás una notificación
            automática cuando la lluvia finalice.
        </div>

        <div class="footer">
            Notificación generada por SCOTT · Panel de Conmutaciones
            <br>
            {{ $event->rain_started_at->format('Y-m-d H:i:s') }}
        </div>
    </div>
</body>

</html>