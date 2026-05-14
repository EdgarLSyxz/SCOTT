<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Report Resolved Notification') }}</title>
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

        .badge-resolved {
            background: #057A55;
            color: #fff;
        }

        .badge-pending {
            background: #D8DE6A;
            color: #fff;
        }

        .badge-type {
            background: #E02424;
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
            <h2>{{ __('Report Resolved Notification') }}</h2>
        </div>

        <div class="details">
            <h3>📌 {{ __('Report Details') }}</h3>
            <table>
                <tr>
                    <th>{{ __('Folio') }}</th>
                    <td>{{ $report->id }}</td>
                </tr>
                <tr>
                    <th>{{ __('Category') }}</th>
                    <td>{{ $report->category }}</td>
                </tr>
                <tr>
                    <th>{{ __('Type') }}</th>
                    <td>
                        <span class="badge badge-type">{{ ucfirst($report->type) }}</span>
                    </td>
                </tr>
                <tr>
                    <th>{{ __('Status') }}</th>
                    <td>
                        <span class="badge badge-resolved">{{ ucfirst($report->status) }}</span>
                    </td>
                </tr>
                <tr>
                    <th>{{ __('Reviewed By') }}</th>
                    <td>{{ $report->reviewed_by }}</td>
                </tr>
                <tr>
                    <th>{{ __('Reported By') }}</th>
                    <td>{{ $reportedBy->name }} ({{ $reportedBy->email }})</td>
                </tr>
                <tr>
                    <th>{{ __('Attended By') }}</th>
                    <td>{{ $attendedBy->name }} ({{ $attendedBy->email }})</td>
                </tr>
                <tr>
                    <th>{{ __('Created At') }}</th>
                    <td>{{ $report->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <th>{{ __('Resolved At') }}</th>
                    <td>{{ $report->updated_at->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <th>{{ __('Duration') }}</th>
                    <td>
                        @php
$createdAt = \Carbon\Carbon::parse($report->created_at);
$resolvedAt = \Carbon\Carbon::parse($report->updated_at);
$totalSeconds = intval($createdAt->diffInSeconds($resolvedAt));

if ($totalSeconds < 60) {
    echo $totalSeconds . ' seconds';
} elseif ($totalSeconds < 3600) {
    echo floor($totalSeconds / 60) . ' minutes';
} else {
    $hours = floor($totalSeconds / 3600);
    $minutes = floor(($totalSeconds % 3600) / 60);
    echo $hours . ' hours ' . ($minutes > 0 ? $minutes . ' minutes' : '');
}
                        @endphp
                    </td>
                </tr>
            </table>
        </div>

        <div class="channels">
            <h3>📡 {{ __('Channels Involved') }}</h3>
            <table>
                <tr>
                    <th>{{ __('Channel') }}</th>
                    <th>
                        @if(($report->area ?? '') === \App\Models\Report::AREA_DTH)
                            {{ __('Stage') }}
                        @else
                            {{ __('Stage and Protocol') }}
                        @endif
                    </th>
                    <th>{{ __('Problem') }}</th>
                </tr>
                @foreach ($channels->sortBy('channel.number') as $detail)
                    <tr>
                        <td>
                            {{ $detail->channel->number }}
                            <span class="channel-name">{{ strtoupper($detail->channel->name) }}</span>
                        </td>
                        <td class="stage-protocol">
                            {{ $detail->stage->name }}
                            @if(($report->area ?? '') !== \App\Models\Report::AREA_DTH)
                                <span class="protocol">({{ $detail->protocol }})</span>
                            @endif
                        </td>
                        <td>{{ $detail->media }}</td>
                    </tr>
                @endforeach
            </table>
        </div>

        <div class="footer">
            <p class="footer-subtitle">{{ __('SCOTT') }}</p>
        </div>
    </div>
</body>

</html>
