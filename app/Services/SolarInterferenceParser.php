<?php

namespace App\Services;

use App\Models\SolarInterference;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class SolarInterferenceParser
{
    private const STATE_KEYWORDS = [
        'Aguascalientes', 'Baja California Norte', 'Baja California Sur', 'Campeche',
        'Coahuila', 'Colima', 'Chiapas', 'Chihuahua', 'Ciudad De México',
        'Durango', 'Guanajuato', 'Guerrero', 'Hidalgo', 'Jalisco',
        'Estado de México', 'Michoacán', 'Morelos', 'Nayarit',
        'Nuevo León', 'Oaxaca', 'Puebla', 'Querétaro', 'Quintana Roo',
        'San Luis Potosí', 'Sinaloa', 'Sonora', 'Tabasco', 'Tamaulipas',
        'Tlaxcala', 'Veracruz', 'Yucatán', 'Zacatecas',
    ];

    private const STATE_HEADERS = [
        'Dia', 'Día',
    ];

    private const SATELLITE_KEYWORDS = [
        'EUTELSAT 65 W',
        'GALAXY 35',
        'INTELSAT 21',
        'INTELSAT 34',
        'EUTELSAT 115 WEST B',
        'EUTELSAT 117 WEST A',
    ];

    private const TELEPORT_KEYWORD = 'TRANSMISION DESDE TELEPUERTO';

    public function parse(string|UploadedFile $source): array
    {
        $parser = new Parser;

        try {
            if ($source instanceof UploadedFile) {
                $pdf = $parser->parseFile($source->getRealPath());
            } else {
                $pdf = $parser->parseFile($source);
            }
        } catch (\Throwable $e) {
            Log::error('SolarInterferenceParser: cannot parse PDF', ['error' => $e->getMessage()]);
            throw $e;
        }

        $text = $pdf->getText();
        $normalized = $this->normalizeText($text);

        return $this->buildRecords($normalized);
    }

    protected function normalizeText(string $text): string
    {
        $text = preg_replace("/\r\n?/", "\n", $text);
        $text = str_replace(["\t"], ' ', $text);
        $text = preg_replace('/[ ]{2,}/', ' ', $text);

        return $text;
    }

    protected function buildRecords(string $text): array
    {
        $chunks = $this->splitIntoChunks($text);
        $records = [];

        foreach ($chunks as $chunk) {
            if ($chunk['type'] === 'state') {
                $records = array_merge($records, $this->parseStateChunk($chunk['name'], $chunk['body']));
            } elseif ($chunk['type'] === 'satellite') {
                $records = array_merge($records, $this->parseSatelliteChunk($chunk['name'], $chunk['body']));
            } elseif ($chunk['type'] === 'teleport') {
                $records = array_merge($records, $this->parseTeleportChunk($chunk['body']));
            }
        }

        return $records;
    }

    protected function splitIntoChunks(string $text): array
    {
        $lines = explode("\n", $text);
        $chunks = [];
        $current = null;

        foreach ($lines as $line) {
            $trim = trim($line);
            if ($trim === '') {
                continue;
            }

            $stateName = $this->matchStateKeyword($trim);
            if ($stateName !== null) {
                $current = [
                    'type' => 'state',
                    'name' => $stateName,
                    'body' => [],
                ];
                $chunks[] = $current;

                continue;
            }

            $satelliteName = $this->matchSatelliteKeyword($trim);
            if ($satelliteName !== null) {
                if (preg_match('/\.{2,}/u', $satelliteName)) {
                    continue;
                }
                if (! in_array($satelliteName, self::SATELLITE_KEYWORDS, true)) {
                    continue;
                }
                $current = [
                    'type' => 'satellite',
                    'name' => $satelliteName,
                    'body' => [],
                ];
                $chunks[] = $current;

                continue;
            }

            if ($this->matchTeleportKeyword($trim)) {
                if (preg_match('/\.{2,}/u', $trim)) {
                    continue;
                }
                $current = [
                    'type' => 'teleport',
                    'name' => 'Telepuerto',
                    'body' => [],
                ];
                $chunks[] = $current;

                continue;
            }

            if ($current !== null) {
                $index = count($chunks) - 1;
                $chunks[$index]['body'][] = $line;
            }
        }

        return $chunks;
    }

    protected function parseStateChunk(string $stateName, array $lines): array
    {
        $records = [];
        foreach ($lines as $row) {
            $row = trim($row);
            if ($row === '') {
                continue;
            }
            if ($this->isHeaderRow($row)) {
                continue;
            }

            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})\s+(\d{1,2}:\d{2}:\d{2})\s+(\d{1,2}:\d{2}:\d{2})\s+(.+)$/', $row, $m)) {
                $duration = $this->normalizeDurationValue($m[6]);
                $records[] = [
                    'document_name' => '',
                    'section' => SolarInterference::SECTION_STATE,
                    'region_name' => $stateName,
                    'event_date' => sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]),
                    'start_time' => $m[4],
                    'end_time' => $m[5],
                    'duration_seconds' => $this->durationToSeconds($duration),
                    'channels' => null,
                    'affected_channels_count' => 0,
                ];
            }
        }

        return $records;
    }

    protected function parseSatelliteChunk(string $satelliteName, array $lines): array
    {
        $channelsBlock = [];
        $events = [];
        $captureChannels = false;
        $eventsStarted = false;

        foreach ($lines as $line) {
            $trim = trim($line);

            if (! $captureChannels) {
                if (preg_match('/^Canales afectados:/iu', $trim)) {
                    $captureChannels = true;
                }

                continue;
            }

            if (preg_match('/^(Fecha|Dia|Día)\s+/iu', $trim)) {
                $eventsStarted = true;

                continue;
            }

            if ($eventsStarted) {
                if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})\s+(\d{1,2}:\d{2})(?::\d{2})?\s+(\d{1,2})m(\d{1,2})s/iu', $trim, $m)) {
                    $events[] = [
                        'event_date' => sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]),
                        'start_time' => sprintf('%s:00', $m[4]),
                        'duration_seconds' => ((int) $m[5]) * 60 + (int) $m[6],
                    ];

                    continue;
                }

                if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})\s+(\d{1,2}:\d{2}:\d{2})\s+(\d{1,2}:\d{2}:\d{2})\s+(.+)$/u', $trim, $m)) {
                    $duration = $this->normalizeDurationValue($m[6]);
                    $events[] = [
                        'event_date' => sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]),
                        'start_time' => $m[4],
                        'end_time' => $m[5],
                        'duration_seconds' => $this->durationToSeconds($duration),
                    ];

                    continue;
                }
            } else {
                if ($trim !== '') {
                    $channelsBlock[] = $trim;
                }
            }
        }

        $channelsBlock = $this->cleanChannelsList($channelsBlock);

        $records = [];
        foreach ($events as $event) {
            $records[] = [
                'document_name' => '',
                'section' => SolarInterference::SECTION_SATELLITE,
                'region_name' => $satelliteName,
                'event_date' => $event['event_date'],
                'start_time' => $event['start_time'],
                'end_time' => $event['end_time'] ?? null,
                'duration_seconds' => $event['duration_seconds'],
                'channels' => $channelsBlock,
                'affected_channels_count' => count($channelsBlock),
            ];
        }

        return $records;
    }

    protected function cleanChannelsList(array $lines): array
    {
        $joined = implode(' | ', $lines);
        $joined = preg_replace('/\s{2,}/u', ' ', $joined);

        $parts = preg_split('/\s*\|\s*/u', $joined);
        $channels = [];
        $ignored = ['Interferencias solares', 'CANALES AFECTADOS', 'Canales afectados'];

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }

            $tokens = preg_split('/\s{2,}/u', $part);
            if (! $tokens || count($tokens) === 1) {
                $tokens = preg_split('/\s+/u', $part);
            }

            foreach ($tokens as $token) {
                $clean = trim($token, " \t\n\r\0\x0B,");
                if ($clean === '') {
                    continue;
                }
                foreach ($ignored as $ig) {
                    if (stripos($clean, $ig) !== false) {
                        continue 2;
                    }
                }
                if (preg_match('/^(Fecha|Dia|Día|Inicio|Hora|Duración|Duracion)\b/iu', $clean)) {
                    continue 2;
                }
                $channels[] = $clean;
            }
        }

        return array_values(array_unique($channels));
    }

    protected function parseTeleportChunk(array $lines): array
    {
        $records = [];
        foreach ($lines as $line) {
            $trim = trim($line);
            if ($trim === '') {
                continue;
            }
            if ($this->isHeaderRow($trim)) {
                continue;
            }

            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})\s+(\d{1,2}:\d{2}:\d{2})\s+(\d{1,2}:\d{2}:\d{2})\s+(.+)$/', $trim, $m)) {
                $duration = $this->normalizeDurationValue($m[6]);
                $records[] = [
                    'document_name' => '',
                    'section' => SolarInterference::SECTION_TELEPORT,
                    'region_name' => __('Telepuerto'),
                    'event_date' => sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]),
                    'start_time' => $m[4],
                    'end_time' => $m[5],
                    'duration_seconds' => $this->durationToSeconds($duration),
                    'channels' => null,
                    'affected_channels_count' => 0,
                ];
            }
        }

        return $records;
    }

    protected function matchStateKeyword(string $line): ?string
    {
        $stripped = trim($line);
        $stripped = preg_replace('/\s+/', ' ', $stripped);

        foreach (self::STATE_KEYWORDS as $keyword) {
            if (strcasecmp($stripped, $keyword) === 0) {
                return $keyword;
            }
        }

        return null;
    }

    protected function matchSatelliteKeyword(string $line): ?string
    {
        if (! preg_match('/^SAT[ÉE]LITE:\s*(.+)$/iu', $line, $m)) {
            return null;
        }

        $name = trim($m[1]);
        $name = preg_replace('/\.+/u', '', $name);
        $name = trim($name);

        if ($name === '') {
            return null;
        }

        foreach (self::SATELLITE_KEYWORDS as $keyword) {
            if (strcasecmp($name, $keyword) === 0) {
                return $keyword;
            }
        }

        return $name;
    }

    protected function matchTeleportKeyword(string $line): bool
    {
        return (bool) preg_match('/TRANSMISION\s+DESDE\s+TELEPUERTO/iu', $line);
    }

    protected function isHeaderRow(string $row): bool
    {
        $normalized = preg_replace('/\s+/', ' ', $row);
        foreach (self::STATE_HEADERS as $header) {
            if (stripos($normalized, $header.' Hora') !== false || stripos($normalized, $header.' Inicio') !== false) {
                return true;
            }
        }

        return false;
    }

    protected function splitChannelLine(string $line): array
    {
        $normalized = preg_replace('/[ \t]+/', ' ', $line);

        if (str_contains($normalized, "\t")) {
            $parts = preg_split('/\t+/', $normalized);
        } else {
            $parts = preg_split('/\s{2,}/', $normalized);
        }

        if (! $parts || count($parts) === 1) {
            $parts = explode(' ', $normalized);
        }

        return $parts ?: [];
    }

    protected function cleanChannelName(string $name): string
    {
        $name = trim($name);
        $name = trim($name, " \t\n\r\0\x0B,");
        if ($name === '' || str_contains($name, 'Canales afectados')) {
            return '';
        }

        return $name;
    }

    protected function normalizeDurationValue(string $raw): string
    {
        $raw = trim($raw);
        if (preg_match('/^(?:\d\s+)?(\d{1,2}:\d{2}:\d{2})$/', $raw, $m)) {
            return $m[1];
        }
        if (preg_match('/^(\d{1,2}:\d{2}:\d{2})(?:\s+\d)?$/', $raw, $m)) {
            return $m[1];
        }

        return $raw;
    }

    protected function durationToSeconds(string $duration): int
    {
        if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $duration, $m)) {
            return ((int) $m[1]) * 3600 + ((int) $m[2]) * 60 + (int) $m[3];
        }

        return 0;
    }
}
