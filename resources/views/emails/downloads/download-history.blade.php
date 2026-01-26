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

      @php
        $meta = $report ?? [];
        if (empty($meta)) {
            $meta = [];
            if (!empty($title)) $meta['title'] = $title;
            if (!empty($description)) $meta['description'] = $description;
            if (!empty($year)) $meta['year'] = $year;
            if (!empty($device_name)) $meta['device_name'] = $device_name;
            if (!empty($device_id)) $meta['device_id'] = $device_id;
        }
      @endphp

      @if(!empty($meta) && is_array($meta))
        <div style="margin-bottom:12px;">
          <h3 style="margin:0 0 6px 0; font-size:14px; color:#0f172a;">{{ __('Report details') }}</h3>
          <table style="width:100%; border-collapse:collapse; margin-top:6px;">
            @foreach($meta as $k => $v)
              @if($v !== null && $v !== '')
                <tr>
                  <td style="width:160px; font-weight:600; padding:6px 8px; border-bottom:1px solid #e5e7eb; text-transform:capitalize; color:#374151;">{{ __(ucwords(str_replace('_',' ', $k))) }}</td>
                  <td style="padding:6px 8px; border-bottom:1px solid #e5e7eb; color:#111;">@if(is_array($v)){{ implode(', ', $v) }}@else{{ $v }}@endif</td>
                </tr>
              @endif
            @endforeach
          </table>
        </div>
      @endif

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
