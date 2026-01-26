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
      <h2 style="margin:0 0 8px 0; color:#0f172a;">{{ config('app.name') }}</h2>
      <p style="margin:0 0 12px 0; color:#111;">Hola,</p>

      <div style="background:#f8fafc; padding:12px; border-radius:6px; margin-bottom:12px; color:#111;">
        {!! nl2br(e($body ?? '')) !!}
      </div>

      @if(!empty($summary) && is_array($summary))
        <table style="width:100%; border-collapse:collapse; margin-bottom:12px;">
          <tr>
            <td style="font-weight:600; padding:6px 8px; border-bottom:1px solid #e5e7eb;">Total</td>
            <td style="padding:6px 8px; border-bottom:1px solid #e5e7eb;">{{ $summary['total'] ?? '-' }}</td>
          </tr>
          <tr>
            <td style="font-weight:600; padding:6px 8px; border-bottom:1px solid #e5e7eb;">Promedio/mes</td>
            <td style="padding:6px 8px; border-bottom:1px solid #e5e7eb;">{{ $summary['average'] ?? '-' }}</td>
          </tr>
          <tr>
            <td style="font-weight:600; padding:6px 8px;">Mes superior</td>
            <td style="padding:6px 8px;">{{ $summary['top_month_label'] ?? '-' }} ({{ $summary['top_month_value'] ?? 0 }})</td>
          </tr>
        </table>
      @endif

      <p style="margin:0 0 8px 0; color:#374151; font-size:13px;">El archivo Excel está adjunto a este correo. Si necesitas que lo reenviemos a otra dirección, responde este correo.</p>

      <div class="footer">
        {{ config('app.name') }}
      </div>
    </div>
  </div>
</body>
</html>
