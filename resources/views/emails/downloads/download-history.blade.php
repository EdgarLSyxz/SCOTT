<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: Arial, Helvetica, sans-serif; color: #111; }
    .container { padding: 16px; }
    .footer { margin-top: 18px; font-size: 12px; color: #666; }
  </style>
</head>
<body>
  <div class="container">
    <div style="max-width:600px;">
      <h2 style="margin:0 0 8px 0; color:#0f172a;">
        {{ 'Historial de descargas por dispositivo' }}
      </h2>

      @php $r = $report ?? []; @endphp

      <table style="width:100%; border-collapse:collapse; margin-bottom:12px;">
        <tr>
          <td style="vertical-align:top; width:100%;">
            <p style="margin:0 0 8px 0; font-size:14px; color:#0f172a; font-weight:700;">Estimado/a,</p>
            <p style="margin:6px 0 12px 0; font-size:13px; color:#334155; line-height:1.5;">
              Adjunto encontrará el informe de <strong>Historial de descargas por dispositivo</strong>. Se ha incluido el detalle en Excel y un PDF resumen listo para presentar.
            </p>

            <div style="display:flex; gap:12px; flex-wrap:wrap; margin:6px 0 14px 0;">
              <div style="flex:1 1 220px; margin-right:12px; border:1px solid #e6eef6; padding:10px; border-radius:8px; background:#f4ebf5;">
                <div style="font-size:13px; font-weight:700; color:#0f172a; margin-bottom:6px;">Excel</div>
                <div style="font-size:13px; color:#334155;">Detalle completo por dispositivo con conteos diarios y mensuales.</div>
              </div>
              <div style="flex:1 1 220px; border:1px solid #e6eef6; padding:10px; border-radius:8px; background:#f4ebf5;">
                <div style="font-size:13px; font-weight:700; color:#0f172a; margin-bottom:6px;">PDF</div>
                <div style="font-size:13px; color:#334155;">Resumen consolidado del periodo seleccionado, optimizado para impresión.</div>
              </div>
            </div>
        </tr>
      </table>

      <div style="border:1px solid #eef2f6; padding:10px; border-radius:8px; background:#ffffff; margin:0 0 14px 0;">
        <div style="font-size:13px; font-weight:700; color:#0f172a; margin-left: 6px; margin-bottom:8px;">Resumen del informe</div>
        <table style="width:100%; font-size:13px; border-collapse:collapse;">
          <tr>
            <td style="padding:6px 8px; color:#374151;"><strong>Selección</strong></td>
            <td style="padding:6px 8px; color:#111; text-align:right;">@if(!empty($r['selected_all'])) Todos los dispositivos @if(isset($r['devices_count'])) ({{ $r['devices_count'] }}) @endif @else {{ $r['device_name'] ?? ($r['device_id'] ?? 'No especificado') }} @endif</td>
          </tr>
          <tr>
            <td style="padding:6px 8px; color:#374151;"><strong>Año</strong></td>
            <td style="padding:6px 8px; color:#111; text-align:right;">{{ $r['year'] ?? date('Y') }}</td>
          </tr>
          <tr>
            <td style="padding:6px 8px; color:#374151;"><strong>Generado</strong></td>
            <td style="padding:6px 8px; color:#111; text-align:right;">{{ now()->format('Y-m-d H:i:s') }}</td>
          </tr>
        </table>
      </div>

      @php
        $meta = $report ?? [];
        if (empty($meta)) {
            $meta = [];
            if (!empty($title))
                $meta['title'] = $title;
            if (!empty($description))
                $meta['description'] = $description;
            if (!empty($year))
                $meta['year'] = $year;
            if (!empty($device_name))
                $meta['device_name'] = $device_name;
            if (!empty($device_id))
                $meta['device_id'] = $device_id;
        }
      @endphp

      <div class="footer">
        <p style="margin:0 0 8px 0; color:#374151; font-size:13px;">
            Cordialmente,<br>
            Sistema de Comunicaciones OTT (SCOTT)
        </p>
      </div>
    </div>
  </div>
</body>
</html>
