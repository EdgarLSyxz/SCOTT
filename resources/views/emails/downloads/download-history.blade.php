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

      <br>

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

      @if(!empty($meta) && is_array($meta))
        <div style="margin-bottom:12px;">
          <h3 style="margin:0 0 6px 0; font-size:14px; color:#0f172a;">
            {{ __('Detalles del informe:') }}
          </h3>

          <div style="margin-top:6px;margin-bottom:8px;">
            <p style="margin:0 0 6px 0;"><strong>{{ __('Selección:') }}</strong>
              @if(!empty($meta['selected_all']))
                {{ __('Todos los dispositivos') }}@if(isset($meta['devices_count'])) ({{ $meta['devices_count'] }})@endif
              @else
                {{ $meta['device_name'] ?? ($meta['device_id'] ?? __('No especificado')) }}
              @endif
            </p>

            <p style="margin:0 0 6px 0;"><strong>{{ __('Año:') }}</strong> {{ $meta['year'] ?? date('Y') }}</p>

            @if(!empty($meta['title']))
              <p style="margin:0 0 6px 0;"><strong>{{ __('Título:') }}</strong> {{ $meta['title'] }}</p>
            @endif

            @if(!empty($meta['description']))
              <p style="margin:0 0 6px 0;"><strong>{{ __('Descripción:') }}</strong> {{ $meta['description'] }}</p>
            @endif
          </div>

          @php
            $alreadyShown = ['selected_all','devices_count','device_name','device_id','year','title','description'];
          @endphp
          <table style="width:100%; border-collapse:collapse; margin-top:6px;">
            @foreach($meta as $k => $v)
              @continue(in_array($k, $alreadyShown))
              @if($v !== null && $v !== '')
                <tr>
                  <td style="width:220px; font-weight:600; padding:6px 8px; border-bottom:1px solid #e5e7eb; color:#374151;">{{ __(ucwords(str_replace('_', ' ', $k))) }}</td>
                  <td style="padding:6px 8px; border-bottom:1px solid #e5e7eb; color:#111;">@if(is_array($v)){{ implode(', ', $v) }}@else{{ $v }}@endif</td>
                </tr>
              @endif
            @endforeach
          </table>
        </div>
      @endif

      <br>

      <div class="footer">
        <p style="margin:0 0 8px 0; color:#374151; font-size:13px;">
            Cordialmente,<br>
            Sistema de Comunicaciones OTT • DTH (SCOTT)
        </p>
      </div>
    </div>
  </div>
</body>
</html>
