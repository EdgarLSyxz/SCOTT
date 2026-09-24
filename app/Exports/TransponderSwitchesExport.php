<?php

namespace App\Exports;

use App\Models\TransponderStateEvent;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransponderSwitchesExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    /**
     * Return a collection of rows to export.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $rows = TransponderStateEvent::with(['transponder', 'user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function (TransponderStateEvent $ev) {
                return [
                    'timestamp' => $ev->created_at?->format('Y-m-d H:i:s'),
                    'transponder_code' => $ev->transponder?->code ?? '—',
                    'from_site' => $ev->from_site ?? '—',
                    'to_site' => $ev->to_site ?? '—',
                    'previous_duration_seconds' => $ev->previous_duration_seconds ?? 0,
                    'previous_duration_human' => $ev->previous_duration_seconds !== null ? $this->humanDuration((int) $ev->previous_duration_seconds) : '—',
                    'user_name' => $ev->user?->name ?? '—',
                    'commands_log' => $ev->commands_log ?? '',
                ];
            });

        return new Collection($rows->values()->toArray());
    }

    public function headings(): array
    {
        return [
            'Timestamp',
            'Transponder Code',
            'From Site',
            'To Site',
            'Previous Duration Seconds',
            'Previous Duration',
            'User',
            'Commands Log',
        ];
    }

    private function humanDuration(int $seconds): string
    {
        if ($seconds < 0) {
            $seconds = 0;
        }

        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs = $seconds % 60;

        if ($days > 0) {
            return sprintf('%dd %02d:%02d:%02d', $days, $hours, $minutes, $secs);
        }

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
}
