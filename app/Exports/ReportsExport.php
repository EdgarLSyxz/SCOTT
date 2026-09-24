<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public $details;

    public $search;

    public function __construct($reports, $search = null)
    {
        $this->search = $search;

        $details = collect($reports)->flatMap(function ($report) {
            $filtered = $report->reportDetails;
            if ($this->search) {
                $search = strtolower($this->search);
                $filtered = $filtered->filter(function ($detail) use ($search) {
                    $number = strtolower($detail->channel->number ?? '');
                    $name = strtolower($detail->channel->name ?? '');

                    return str_contains($number, $search) || str_contains($name, $search);
                });
            }

            return $filtered->map(function ($detail) use ($report) {
                $detail->parentReport = $report;

                return $detail;
            });
        });

        $this->details = $details;
    }

    public function collection()
    {
        return $this->details;
    }

    public function headings(): array
    {
        return [
            'Folio',
            'Category',
            'Report Type',
            'Status',
            'Created At',
            'Updated At',
            'Reported By',
            'Reviewed By',
            'Attended By',
            'Duration',
            'Channel Number',
            'Channel Name',
            'Subcategory',
            'Protocol',
            'Media',
            'Description',
            'Content Losses',
        ];
    }

    public function map($detail): array
    {
        $report = $detail->parentReport;

        $number = $detail->channel->number ?? 'No Number';
        $name = $detail->channel->name ?? 'Unknown Channel';
        $subcategory = $detail->subcategory ?? 'Unknown Subcategory';
        $protocol = $detail->protocol ?? 'No Protocol';

        if (
            $report->type === 'Functions' &&
            in_array($subcategory, ['EPG', 'PC']) &&
            (is_null($detail->media) || $detail->media == '')
        ) {
            $media = 'Not Applicable';
        } else {
            $media = $detail->media ?? 'No Media';
        }
        if ($report->type === 'Functions' && $subcategory === 'CUTV') {
            $description = 'Not Applicable';
        } else {
            $description = $detail->description ?? 'No Applicable';
        }
        $losses = '';
        if ($report->type === 'Functions' && $subcategory === 'CUTV' && ! $detail->reportContentLosses->isEmpty()) {
            $losses = $detail->reportContentLosses->map(function ($loss) {
                $start = \Carbon\Carbon::parse($loss->start_time);
                $end = \Carbon\Carbon::parse($loss->end_time);
                $diff = $start->diff($end);
                $days = (int) $diff->format('%a');
                $hours = (int) $diff->format('%H');
                $minutes = (int) $diff->format('%I');
                $duration = '';
                if ($days > 0) {
                    $duration .= "{$days}d ";
                }
                if ($hours > 0 || $days > 0) {
                    $duration .= "{$hours}h ";
                }
                $duration .= "{$minutes}m";

                return "Start: {$start->format('d/m/Y H:i')}, End: {$end->format('d/m/Y H:i')}, Duration: {$duration}";
            })->join("\n");
        }

        return [
            $report->id,
            $report->category,
            $report->type,
            $report->status,
            $report->created_at->format('Y/m/d h:i A'),
            $report->updated_at->format('Y/m/d h:i A'),
            $report->reportedBy->name ?? 'N/A',
            $report->reviewed_by ?? '',
            $report->attendedBy->name ?? '',
            $report->duration ?? '',
            $number,
            $name,
            $subcategory,
            $protocol,
            $media,
            $description,
            $losses,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
