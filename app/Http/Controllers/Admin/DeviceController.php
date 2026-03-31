<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Package;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DeviceController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $user = Auth::user();

        if (! ($user && $user->id === 1)) {
            if (! $user) {
                abort(403);
            }

            if (! $user->can('viewAny', Device::class)) {
                abort(403);
            }
        }

        $devices = Device::when($user && $user->id !== 1, function ($query) use ($user) {
            return $query->where('area', $user->area);
        })->paginate(10);

        return view('admin.devices.index', compact('devices'));
    }

    public function create()
    {
        $this->authorize('create', Device::class);

        return view('admin.devices.create');
    }

    public function store(Request $request)
    {

    }

    public function show(Device $device)
    {
        $this->authorize('view', $device);

        return view('admin.devices.show', compact('device'));
    }

    public function edit(Device $device)
    {
        if ($device->id === 10 && (! Auth::user() || Auth::id() !== 1)) {
            abort(403);
        }

        $this->authorize('update', $device);

        return view('admin.devices.edit', compact('device'));
    }

    public function update(Request $request, string $id)
    {

    }

    public function destroy(Device $device)
    {
        if ($device->id === 10 && (! Auth::user() || Auth::id() !== 1)) {
            abort(403);
        }

        $this->authorize('delete', $device);

        if ($device->image_url && Storage::disk('public')->exists($device->image_url)) {
            Storage::disk('public')->delete($device->image_url);
        }

        $device->delete();

        return redirect()->route('admin.devices.index')->with('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => __('Device deleted successfully.'),
        ]);
    }

    public function monthlyDownloads()
    {
        $userId = Auth::id();

        $allowedIds = [1, 2, 3, 5, 7, 8];

        $devices = Device::orderBy('name')->get(['id', 'name']);

        return view('admin.devices.monthly-downloads', compact('devices'));
    }

    public function packages()
    {
        $userId = Auth::id();

        $allowedIds = [1, 2, 3, 5, 7, 8];

        if (! ($userId && in_array((int) $userId, $allowedIds, true))) {
            abort(403);
        }

        return view('admin.devices.packages');
    }

    public function processPDF(Request $request)
    {
        $userId = Auth::id();
        $user = Auth::user();

        $allowedIds = [1, 2, 3, 5, 7, 8];

        if (! ($userId && in_array((int) $userId, $allowedIds, true))) {
            abort(403);
        }

        $phpFilesInfo = [];
        if (isset($_FILES['pdf_file'])) {
            $phpFilesInfo = [
                'name' => $_FILES['pdf_file']['name'] ?? null,
                'type' => $_FILES['pdf_file']['type'] ?? null,
                'size' => $_FILES['pdf_file']['size'] ?? null,
                'error' => $_FILES['pdf_file']['error'] ?? null,
                'error_message' => $this->getUploadErrorMessage($_FILES['pdf_file']['error'] ?? 0),
                'tmp_name' => isset($_FILES['pdf_file']['tmp_name']) ? 'set' : 'not set',
                'tmp_exists' => isset($_FILES['pdf_file']['tmp_name']) && file_exists($_FILES['pdf_file']['tmp_name']),
            ];
        }

        \Log::info('processPDF received', [
            'has_file' => $request->hasFile('pdf_file'),
            'files_count' => count($request->files->all()),
            'files_keys' => array_keys($request->files->all()),
            'input_keys' => array_keys($request->all()),
            'content_type' => $request->header('content-type'),
            'content_length' => $request->header('content-length'),
            'php_files_info' => $phpFilesInfo,
            'php_ini_upload_max' => ini_get('upload_max_filesize'),
            'php_ini_post_max' => ini_get('post_max_size'),
            'files_array_content' => array_map(function($file) {
                if (is_object($file)) {
                    return [
                        'class' => get_class($file),
                        'is_valid' => method_exists($file, 'isValid') ? $file->isValid() : 'N/A',
                        'size' => method_exists($file, 'getSize') ? $file->getSize() : 'N/A',
                    ];
                }
                return ['type' => gettype($file), 'value' => (string)$file];
            }, $request->files->all()),
        ]);

        $pdfFile = null;
        if ($request->hasFile('pdf_file')) {
            $pdfFile = $request->file('pdf_file');
        }

        if (!$pdfFile) {
            \Log::warning('processPDF no file received', [
                'user_id' => $userId,
                'has_files' => $request->hasFile('pdf_file'),
                'all_files' => array_keys($request->files->all()),
                'php_files_info' => $phpFilesInfo,
            ]);
            return response()->json(['success' => true, 'message' => 'Request received but no pdf_file found. Check logs.'], 200);
        }

        try {

            \Log::info('processPDF called', [
                'user_id' => $userId,
                'user_email' => $user->email ?? null,
                'filename' => $pdfFile ? $pdfFile->getClientOriginalName() : null,
                'size' => $pdfFile ? $pdfFile->getSize() : null,
            ]);

            $pythonResponse = $this->callPythonAPI($pdfFile);

            \Log::info('processPDF python response', [
                'user_id' => $userId,
                'status' => is_array($pythonResponse) && isset($pythonResponse['success']) ? $pythonResponse['success'] : 'unknown',
                'summary_keys' => is_array($pythonResponse) ? array_keys($pythonResponse) : null,
            ]);

            if (is_array($pythonResponse) && isset($pythonResponse['success']) && $pythonResponse['success'] === false) {
                $message = $pythonResponse['message'] ?? __('Error processing PDF via Python API');
                $payload = ['success' => false, 'message' => $message];

                if (isset($pythonResponse['status'])) {
                    $payload['python_status'] = $pythonResponse['status'];
                }

                if (isset($pythonResponse['body'])) {
                    $payload['python_body'] = $pythonResponse['body'];
                }

                return response()->json($payload, 500);
            }

            try {
                $package = new Package();
                $package->user_id = $user->id;
                $package->filename = $pdfFile->getClientOriginalName();
                $package->data = $pythonResponse['packages'] ?? $pythonResponse;
                $package->save();
            } catch (\Exception $e) {
                \Log::warning('Failed to save package data: ' . $e->getMessage());
            }

            return response()->json($pythonResponse);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function callPythonAPI($pdfFile)
    {
        try {
            $pythonUrl = config('services.python_packages_api.url', 'http://172.16.126.166:8000');
            $endpoint = rtrim($pythonUrl, '/') . '/api/process-pdf';

            \Log::info('callPythonAPI start', [
                'endpoint' => $endpoint,
                'filename' => $pdfFile ? $pdfFile->getClientOriginalName() : null,
                'realpath' => $pdfFile ? $pdfFile->getRealPath() : null,
            ]);

            $client = new \GuzzleHttp\Client();

            $response = $client->post($endpoint, [
                'multipart' => [
                    [
                        'name' => 'pdf_file',
                        'contents' => fopen($pdfFile->getRealPath(), 'r'),
                        'filename' => $pdfFile->getClientOriginalName(),
                    ],
                ],
                'timeout' => config('services.python_packages_api.timeout', 120),
            ]);

            \Log::info('callPythonAPI response status', ['status' => $response->getStatusCode()]);
            \Log::info('callPythonAPI response body (truncated)', ['body' => substr((string)$response->getBody(), 0, 400)]);

            $status = $response->getStatusCode();
            $body = (string) $response->getBody();

            if ($status >= 200 && $status < 300) {
                return json_decode($body, true);
            }

            \Log::error('Python API Returned non-2xx: ' . $status . ' body: ' . $body);

            return [
                'success' => false,
                'message' => 'Python API Returned non-2xx response',
                'status' => $status,
                'body' => $body,
            ];
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $resp = $e->getResponse();
            $body = $resp ? (string) $resp->getBody() : null;

            \Log::error('Python API RequestException', ['message' => $e->getMessage(), 'status' => $resp ? $resp->getStatusCode() : null, 'body' => $body]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'status' => $resp ? $resp->getStatusCode() : null,
                'body' => $body,
            ];
        } catch (\Exception $e) {
            \Log::error('Python API Unexpected Exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    private function getUploadErrorMessage($errorCode)
    {
        $errorMessages = [
            UPLOAD_ERR_OK => 'No error',
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary directory',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by PHP extension',
        ];
        return $errorMessages[$errorCode] ?? 'Unknown error';
    }

    public function logAnalytics()
    {
        $userId = Auth::id();

        $allowedIds = [1, 2, 3, 5, 7, 8];

        if (! ($userId && in_array((int) $userId, $allowedIds, true))) {
            abort(403);
        }

        return view('admin.devices.log-analytics');
    }
}
