<?php

namespace App\Http\Controllers\Admin;

use App\Models\LogAnalytic;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Exception\ProcessFailedException;

class LogAnalyticsController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $validated = $request->validate([
                'file' => 'required|file|mimes:txt|max:10240',
            ]);

            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $file = $validated['file'];
            $originalName = $file->getClientOriginalName();

            $storagePath = Storage::disk('local')->putFileAs(
                'log_uploads',
                $file,
                time() . '_' . $originalName
            );

            $fullPath = Storage::disk('local')->path($storagePath);

            $result = $this->parseLogFile($fullPath);

            if (isset($result['error'])) {
                Storage::disk('local')->delete($storagePath);
                throw ValidationException::withMessages([
                    'file' => $result['error']
                ]);
            }

            $rawReportDate = $result['report_date'] ?? null;
            $normalizedReportDate = null;
            if (!empty($rawReportDate)) {
                try {
                    $dt = \Carbon\Carbon::parse($rawReportDate);
                    $normalizedReportDate = $dt->format('Y-m-d');
                } catch (\Exception $e) {
                    $formats = [
                        'd/m/Y H:i:s', 'd/m/Y', 'd-m-Y H:i:s', 'd-m-Y', 'd.m.Y H:i:s', 'd.m.Y', 'Y-m-d H:i:s', 'Y-m-d'
                    ];
                    foreach ($formats as $fmt) {
                        try {
                            $dt = \Carbon\Carbon::createFromFormat($fmt, $rawReportDate);
                            if ($dt !== false) {
                                $normalizedReportDate = $dt->format('Y-m-d');
                                break;
                            }
                        } catch (\Exception $_) {}
                    }
                }
            }

            if (empty($normalizedReportDate)) {
                $normalizedReportDate = now()->format('Y-m-d');
            }

            $receivedData = is_array($result['data'] ?? null) ? $result['data'] : [];
            $enrichedData = $this->enrichCategoriesPayload($receivedData);

            $logAnalytic = LogAnalytic::create([
                'user_id' => $user->id,
                'filename' => $originalName,
                'report_date' => $normalizedReportDate,
                'data' => $enrichedData,
            ]);

            Log::info('log_analytics.ingestion.saved', [
                'report_id' => $logAnalytic->id,
                'user_id' => $user->id,
                'filename' => $originalName,
                'received_categories_total' => count($receivedData),
                'saved_categories_total' => count($enrichedData),
                'has_top_channels_by_traffic_per_hour' => $this->hasTopChannelsByTrafficPerHourCategory($enrichedData),
            ]);

            Storage::disk('local')->delete($storagePath);

            return response()->json([
                'success' => true,
                'message' => __('Log report uploaded successfully.'),
                'report_id' => $logAnalytic->id,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => true,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function parseLogFile($filePath)
    {
        try {
            $apiUrl = env('LOG_PARSER_URL') ?: env('PYTHON_LOG_ANALYTICS_API_URL') ?: config('services.python_log_analytics_api.url') ?: config('services.log_parser.url') ?: null;
            if ($apiUrl) {
                if (stripos($apiUrl, '/extract') === false && stripos($apiUrl, '/api/process-txt') === false) {
                    $apiUrl = rtrim($apiUrl, '/') . '/extract';
                }

                try {
                    $timeout = config('services.python_log_analytics_api.timeout', 60);

                    $response = Http::timeout($timeout)
                        ->attach('file', fopen($filePath, 'r'), basename($filePath))
                        ->post($apiUrl);

                    if (! $response->successful()) {
                        try {
                            $response = Http::timeout($timeout)
                                ->attach('txt_file', fopen($filePath, 'r'), basename($filePath))
                                ->post($apiUrl);
                        } catch (\Exception $innerEx) {
                            \Log::debug('Parser API fallback attempt failed: ' . $innerEx->getMessage());
                        }
                    }

                    if ($response->successful()) {
                        $parsed = $response->json();
                        return $this->normalizeParserPayload($parsed);
                    }

                    \Log::warning('Parser API returned non-success (' . ($response->status() ?? 'n/a') . '): ' . ($response->body() ?? ''));
                } catch (\Exception $ex) {
                    \Log::warning('Parser API call failed, falling back to local parser: ' . $ex->getMessage());
                }
            }

            $pythonScript = base_path('app/Scripts/parse_log_report.py');

            if (! file_exists($pythonScript)) {
                return ['error' => 'Parser script not found'];
            }

            $pythonCmd = 'python3';

            $result = Process::run([$pythonCmd, $pythonScript, $filePath]);

            if (! $result->successful()) {
                $pythonCmd = 'python';
                $result = Process::run([$pythonCmd, $pythonScript, $filePath]);
            }

            if (! $result->successful()) {
                $errorMsg = $result->errorOutput();
                return ['error' => 'Failed to parse log file: ' . ($errorMsg ?: 'Unknown error')];
            }

            $output = trim($result->output());

            $parsed = json_decode($output, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Invalid JSON response from parser: ' . json_last_error_msg()];
            }

            return $this->normalizeParserPayload($parsed);
        } catch (ProcessFailedException $e) {
            return ['error' => 'Process failed: ' . $e->getMessage()];
        } catch (\Exception $e) {
            return ['error' => 'Parsing error: ' . $e->getMessage()];
        }
    }

    private function normalizeParserPayload($parsed): array
    {
        $normalized = [
            'report_date' => is_array($parsed)
                ? ($parsed['report_date'] ?? $parsed['reportDate'] ?? null)
                : null,
            'data' => [],
        ];

        if (!is_array($parsed)) {
            return $normalized;
        }

        if (isset($parsed['data']) && is_array($parsed['data'])) {
            foreach ($parsed['data'] as $catName => $catPayload) {
                $normalized['data'][(string) $catName] = $this->normalizeCategoryPayloadToRows($catPayload);
            }

            return $normalized;
        }

        if (isset($parsed['category_map']) && is_array($parsed['category_map'])) {
            foreach ($parsed['category_map'] as $catName => $catPayload) {
                $normalized['data'][(string) $catName] = $this->normalizeCategoryPayloadToRows($catPayload);
            }

            return $normalized;
        }

        if (isset($parsed['categories']) && is_array($parsed['categories'])) {
            foreach ($parsed['categories'] as $cat) {
                if (!is_array($cat)) {
                    continue;
                }

                $name = (string) ($cat['name'] ?? ($cat['title'] ?? ''));
                if ($name === '') {
                    continue;
                }

                if (array_key_exists('rows', $cat)) {
                    $normalized['data'][$name] = $this->normalizeCategoryPayloadToRows($cat['rows']);
                } elseif (array_key_exists('raw_lines', $cat)) {
                    $normalized['data'][$name] = $this->normalizeCategoryPayloadToRows($cat['raw_lines']);
                } else {
                    $normalized['data'][$name] = [];
                }
            }

            return $normalized;
        }

        $candidate = $parsed;
        unset(
            $candidate['report_date'],
            $candidate['reportDate'],
            $candidate['title'],
            $candidate['success'],
            $candidate['categories'],
            $candidate['category_map'],
            $candidate['total_categories']
        );

        foreach ($candidate as $catName => $catPayload) {
            $normalized['data'][(string) $catName] = $this->normalizeCategoryPayloadToRows($catPayload);
        }

        return $normalized;
    }

    private function normalizeCategoryPayloadToRows($payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        if (isset($payload['rows']) && is_array($payload['rows'])) {
            return $payload['rows'];
        }

        if (isset($payload['raw_lines']) && is_array($payload['raw_lines'])) {
            return array_map(fn($line) => ['raw' => (string) $line], $payload['raw_lines']);
        }

        return array_values($payload) === $payload ? $payload : [];
    }

    private function enrichCategoriesPayload(array $data): array
    {
        $normalized = [];
        foreach ($data as $categoryName => $items) {
            $normalized[(string) $categoryName] = $this->normalizeCategoryPayloadToRows($items);
        }

        if (!$this->hasTopChannelsByTrafficPerHourCategory($normalized)) {
            $derived = $this->buildTopChannelsByTrafficPerHourCategory($normalized);
            if (!empty($derived)) {
                $normalized['TOP CANALES POR TRAFICO POR HORA'] = $derived;
            }
        }

        return $normalized;
    }

    private function hasTopChannelsByTrafficPerHourCategory(array $data): bool
    {
        foreach (array_keys($data) as $categoryName) {
            $normalized = strtoupper(trim((string) $categoryName));
            if ($normalized === 'TOP CANALES POR TRAFICO POR HORA') {
                return true;
            }
        }

        return false;
    }

    private function buildTopChannelsByTrafficPerHourCategory(array $data): array
    {
        $sourceRows = [];

        foreach ($data as $categoryName => $items) {
            $name = strtoupper(trim((string) $categoryName));
            if (str_contains($name, 'TOP CANALES POR TRAFICO POR HORA')) {
                continue;
            }

            if (!str_contains($name, 'TRAFICO') || !str_contains($name, 'HORA')) {
                continue;
            }

            if (!is_array($items) || empty($items)) {
                continue;
            }

            foreach ($items as $row) {
                if (is_array($row)) {
                    $sourceRows[] = $row;
                }
            }
        }

        if (empty($sourceRows)) {
            return [];
        }

        $byHourAndChannel = [];
        foreach ($sourceRows as $row) {
            $hour = $this->extractHourBucket($row);
            $label = $this->extractChannelLabel($row);
            $trafficValue = $this->extractTrafficValue($row);

            if ($hour === null || $label === null) {
                continue;
            }

            $hourKey = (string) $hour;
            $channelKey = mb_strtolower(trim($label));

            if (!isset($byHourAndChannel[$hourKey])) {
                $byHourAndChannel[$hourKey] = [];
            }

            if (!isset($byHourAndChannel[$hourKey][$channelKey])) {
                $byHourAndChannel[$hourKey][$channelKey] = [
                    'hour' => $hourKey,
                    'label' => $label,
                    'value' => 0.0,
                ];
            }

            $byHourAndChannel[$hourKey][$channelKey]['value'] += $trafficValue;
        }

        if (empty($byHourAndChannel)) {
            return [];
        }

        $result = [];
        ksort($byHourAndChannel);

        foreach ($byHourAndChannel as $hour => $channels) {
            $rows = array_values($channels);
            usort($rows, fn($a, $b) => ($b['value'] ?? 0) <=> ($a['value'] ?? 0));

            foreach (array_slice($rows, 0, 10) as $index => $row) {
                $result[] = [
                    'hour' => $hour,
                    'rank' => $index + 1,
                    'label' => $row['label'],
                    'value' => round((float) ($row['value'] ?? 0), 4),
                ];
            }
        }

        return $result;
    }

    private function extractHourBucket(array $row): ?string
    {
        $candidates = [
            'hour', 'hora', 'time', 'time_slot', 'slot', 'franja',
        ];

        foreach ($candidates as $key) {
            if (!array_key_exists($key, $row)) {
                continue;
            }

            $value = trim((string) $row[$key]);
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function extractChannelLabel(array $row): ?string
    {
        $candidates = [
            'label', 'name', 'channel', 'canal', 'channel_name', 'service_name', 'service',
        ];

        foreach ($candidates as $key) {
            if (!array_key_exists($key, $row)) {
                continue;
            }

            $value = trim((string) $row[$key]);
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function extractTrafficValue(array $row): float
    {
        $candidates = [
            'value', 'count', 'traffic', 'trafico', 'throughput',
        ];

        foreach ($candidates as $key) {
            if (!array_key_exists($key, $row)) {
                continue;
            }

            if (is_numeric($row[$key])) {
                return (float) $row[$key];
            }
        }

        return 0.0;
    }

    public function delete($id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $logAnalytic = LogAnalytic::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$logAnalytic) {
                return response()->json(['error' => 'Report not found'], 404);
            }

            $logAnalytic->delete();

            return response()->json([
                'success' => true,
                'message' => __('Log report deleted successfully'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
