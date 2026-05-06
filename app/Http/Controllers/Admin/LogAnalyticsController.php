<?php

namespace App\Http\Controllers\Admin;

use App\Models\LogAnalytic;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
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

            $logAnalytic = LogAnalytic::create([
                'user_id' => $user->id,
                'filename' => $originalName,
                'report_date' => $normalizedReportDate,
                'data' => $result['data'] ?? [],
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

                        $normalized = [];
                        $normalized['report_date'] = $parsed['report_date'] ?? $parsed['reportDate'] ?? null;

                        if (isset($parsed['data']) && is_array($parsed['data'])) {
                            $normalized['data'] = $parsed['data'];
                        } elseif (isset($parsed['category_map']) && is_array($parsed['category_map'])) {
                            $data = [];
                            foreach ($parsed['category_map'] as $catName => $catPayload) {
                                if (is_array($catPayload)) {
                                    if (isset($catPayload['rows']) && is_array($catPayload['rows'])) {
                                        $data[$catName] = $catPayload['rows'];
                                    } elseif (isset($catPayload['raw_lines']) && is_array($catPayload['raw_lines'])) {
                                        $data[$catName] = array_map(fn($ln) => ['raw' => $ln], $catPayload['raw_lines']);
                                    } else {
                                        $data[$catName] = $catPayload;
                                    }
                                }
                            }
                            $normalized['data'] = $data;
                        } elseif (isset($parsed['categories']) && is_array($parsed['categories'])) {
                            $data = [];
                            foreach ($parsed['categories'] as $cat) {
                                $name = $cat['name'] ?? ($cat['title'] ?? '');
                                if (! $name) continue;
                                if (isset($cat['rows']) && is_array($cat['rows'])) {
                                    $data[$name] = $cat['rows'];
                                } elseif (isset($cat['raw_lines']) && is_array($cat['raw_lines'])) {
                                    $data[$name] = array_map(fn($ln) => ['raw' => $ln], $cat['raw_lines']);
                                } else {
                                    $data[$name] = [];
                                }
                            }
                            $normalized['data'] = $data;
                        } else {
                            if (is_array($parsed)) {
                                $candidate = $parsed;
                                unset($candidate['report_date'], $candidate['title'], $candidate['success'], $candidate['categories'], $candidate['category_map'], $candidate['total_categories']);
                                $normalized['data'] = $candidate ?: [];
                            } else {
                                $normalized['data'] = [];
                            }
                        }

                        return $normalized;
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

            $normalized = [];
            $normalized['report_date'] = $parsed['report_date'] ?? ($parsed['reportDate'] ?? null);
            if (isset($parsed['data']) && is_array($parsed['data'])) {
                $normalized['data'] = $parsed['data'];
            } elseif (isset($parsed['category_map']) && is_array($parsed['category_map'])) {
                $data = [];
                foreach ($parsed['category_map'] as $catName => $catPayload) {
                    if (is_array($catPayload)) {
                        if (isset($catPayload['rows']) && is_array($catPayload['rows'])) {
                            $data[$catName] = $catPayload['rows'];
                        } elseif (isset($catPayload['raw_lines']) && is_array($catPayload['raw_lines'])) {
                            $data[$catName] = array_map(fn($ln) => ['raw' => $ln], $catPayload['raw_lines']);
                        } else {
                            $data[$catName] = $catPayload;
                        }
                    }
                }
                $normalized['data'] = $data;
            } elseif (isset($parsed['categories']) && is_array($parsed['categories'])) {
                $data = [];
                foreach ($parsed['categories'] as $cat) {
                    $name = $cat['name'] ?? ($cat['title'] ?? '');
                    if (! $name) continue;
                    if (isset($cat['rows']) && is_array($cat['rows'])) {
                        $data[$name] = $cat['rows'];
                    } elseif (isset($cat['raw_lines']) && is_array($cat['raw_lines'])) {
                        $data[$name] = array_map(fn($ln) => ['raw' => $ln], $cat['raw_lines']);
                    } else {
                        $data[$name] = [];
                    }
                }
                $normalized['data'] = $data;
            } else {
                if (is_array($parsed)) {
                    $candidate = $parsed;
                    unset($candidate['report_date'], $candidate['title'], $candidate['success']);
                    $normalized['data'] = $candidate ?: [];
                } else {
                    $normalized['data'] = [];
                }
            }

            return $normalized;
        } catch (ProcessFailedException $e) {
            return ['error' => 'Process failed: ' . $e->getMessage()];
        } catch (\Exception $e) {
            return ['error' => 'Parsing error: ' . $e->getMessage()];
        }
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
