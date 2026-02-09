<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Package;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

            if (! $user->can('viewAny', Device::class) && ! $user->can('view', Device::class)) {
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
        if ($device->id === 10 && (! Auth::user() || Auth::id() !== 1)) {
            abort(403);
        }

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

        $allowedIds = [1, 2, 5, 7, 8];

        if (! ($userId && in_array((int) $userId, $allowedIds, true))) {
            abort(403);
        }

        $devices = Device::orderBy('name')->get(['id', 'name']);

        return view('admin.devices.monthly-downloads', compact('devices'));
    }

    public function packages()
    {
        $userId = Auth::id();

        $allowedIds = [1, 2, 5, 7, 8];

        if (! ($userId && in_array((int) $userId, $allowedIds, true))) {
            abort(403);
        }

        return view('admin.devices.packages');
    }

    public function processPDF(Request $request)
    {
        $userId = Auth::id();
        $user = Auth::user();

        $allowedIds = [1, 2, 5, 7, 8];

        if (! ($userId && in_array((int) $userId, $allowedIds, true))) {
            abort(403);
        }

        $request->validate([
            'pdf_file' => 'required|mimes:pdf|max:50000',
        ]);

        try {
            $pdfFile = $request->file('pdf_file');

            $pythonResponse = $this->callPythonAPI($pdfFile);

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
            $pythonUrl = config('services.python_packages_api.url', 'http://127.0.0.1:8000');
            $endpoint = rtrim($pythonUrl, '/') . '/api/process-pdf';

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

            \Log::error('Python API RequestException: ' . $e->getMessage() . ' response: ' . $body);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'status' => $resp ? $resp->getStatusCode() : null,
                'body' => $body,
            ];
        }
    }
}
