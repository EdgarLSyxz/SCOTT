<?php

namespace App\Http\Controllers\Admin;

use App\Models\LogAnalytic;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
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

            $logAnalytic = LogAnalytic::create([
                'user_id' => $user->id,
                'filename' => $originalName,
                'report_date' => $result['report_date'] ?? now()->format('Y-m-d'),
                'data' => $result['data'] ?? [],
            ]);

            Storage::disk('local')->delete($storagePath);

            return response()->json([
                'success' => true,
                'message' => __('Log report uploaded successfully'),
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
            $pythonScript = base_path('app/Scripts/parse_log_report.py');

            if (!file_exists($pythonScript)) {
                return ['error' => 'Parser script not found'];
            }

            $result = Process::run([
                'python',
                $pythonScript,
                $filePath,
            ]);

            if (!$result->successful()) {
                return ['error' => 'Failed to parse log file: ' . $result->errorOutput()];
            }

            $output = $result->output();
            $parsed = json_decode($output, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Invalid JSON response from parser'];
            }

            return $parsed;
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
