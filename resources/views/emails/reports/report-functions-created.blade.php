<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('New Functions Report Created') }}</title>
    <style>
        body {
            font-family: 'Arial', Helvetica, sans-serif;
            background-color: #121212;
            color: #f1f1f1;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 30px auto;
            background: #222;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            border-left: 6px solid #ffffff;
        }

        .header {
            text-align: center;
            padding-bottom: 15px;
        }

        h2 {
            color: #ffffff;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
        }

        .details,
        .channels {
            background: #1a1a1a;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #ffffff;
        }

        .details h3,
        .channels h3 {
            margin-top: 0;
            color: #f1f1f1;
            font-size: 16px;
            font-weight: bold;
            border-bottom: 2px solid #444;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            border-radius: 12px;
            overflow: hidden;
        }

        table,
        th,
        td {
            border: 1px solid #333;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            font-size: 14px;
            white-space: nowrap;
        }

        th {
            background: #ffffff;
            color: #222;
            font-weight: bold;
        }

        td {
            background: #252525;
            color: #ddd;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 5px;
            text-transform: uppercase;
        }

        .badge-status {
            background: #374151;
            color: #fff;
        }

        .badge-type {
            background: #1C64F2;
            color: #fff;
        }

        .footer {
            text-align: center;
            font-size: 14px;
            color: #bbb;
            margin-top: 20px;
            padding-top: 10px;
            padding-bottom: 10px;
        }

        .footer-subtitle {
            margin: 0;
            font-size: 14px;
            opacity: 0.8;
        }

        @media only screen and (max-width: 600px) {

            .channels table th,
            .channels table td {
                text-align: center;
                padding: 8px;
            }

            .channels .channel-name {
                display: none;
            }

            .channels .stage-protocol .protocol {
                display: block;
                margin-top: 4px;
                font-size: 13px;
                color: #aaa;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>{{ __('New Functions Report Created') }}</h2>
        </div>

        <div class="details">
            <h3>📌 {{ __('Report Details') }}</h3>
            <table>
                <tr>
                    <th>{{ __('Folio') }}</th>
                    <td>{{ $report->id }}</td>
                </tr>
                <tr>
                    <th>{{ __('Type') }}</th>
                    <td><span class="badge badge-type">{{ ucfirst($report->type) }}</span></td>
                </tr>
                <tr>
                    <th>{{ __('Status') }}</th>
                    <td><span class="badge badge-status">{{ ucfirst($report->status) }}</span></td>
                </tr>
                <tr>
                    <th>{{ __('Reported By') }}</th>
                    <td>{{ $reportedBy->name }} ({{ $reportedBy->email }})</td>
                </tr>
                <tr>
                    <th>{{ __('Created At') }}</th>
                    <td>{{ $report->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            </table>
        </div>

        @foreach ($categories as $category)
            <div class="channels">
                <h3>📡 {{ $category['name'] }}</h3>

                @if (!empty($category['channels']))
                    @foreach (collect($category['channels'])->filter(fn($channel) => isset($channel['number']))->sortBy('number') as $channel)
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 35%;">{{ __('Channel') }}</th>
                                    <th style="width: 35%;">{{ __('Stage and Protocol') }}</th>
                                    <th style="width: 30%;">{{ __('Media') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        {{ $channel['number'] }}
                                        <span class="channel-name">{{ $channel['name'] }}</span>
                                    </td>
                                    <td class="stage-protocol">
                                        @php
            $stage = \App\Models\Stage::find($channel['stage']);
                                        @endphp
                                        {{ $stage ? $stage->name : '-' }}
                                        <span class="protocol">({{ $channel['protocol'] ?? '-' }})</span>
                                    </td>
                                    <td>{{ !empty($channel['media']) ? $channel['media'] : __('Not applicable') }}</td>
                                </tr>

                                @if ($category['name'] == 'CUTV' && !empty($channel['loss_periods']))
                                    @foreach ($channel['loss_periods'] as $period)
                                        @php
                    $start = \Carbon\Carbon::parse($period['start_time']);
                    $end = \Carbon\Carbon::parse($period['end_time']);
                    $diff = $start->diff($end);
                    $days = $diff->format('%a');
                    $hours = $diff->format('%H');
                    $minutes = $diff->format('%I');

                    $duration = ($days > 0 ? "{$days}d " : '') . "{$hours}h {$minutes}m";
                                        @endphp
                                        <tr>
                                            <td>{{ $start->format('d/m/Y H:i') }}</td>
                                            <td>{{ $end->format('d/m/Y H:i') }}</td>
                                            <td>{{ $duration }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    @endforeach
                @else
                    <table>
                        <tbody>
                            <tr>
                                <td colspan="3"
                                    style="text-align: center; font-weight: bold; color: #057A55; background: #252525;">
                                    {{ __('All channels on this function are working properly.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                @endif
            </div>
        @endforeach

        <div class="footer">
            <p class="footer-subtitle">
                {{ __('SCOTT') }}
            </p>
        </div>
    </div>
</body>

</html>
