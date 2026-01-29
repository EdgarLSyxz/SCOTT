<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PackageController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $allowedIds = [1, 2, 5, 7, 8];

        if (! ($user && in_array($user->id, $allowedIds, true))) {
            abort(403);
        }

        $packages = Package::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.packages.index', compact('packages'));
    }

    public function show(Package $package)
    {
        $user = Auth::user();

        $allowedIds = [1, 2, 5, 7, 8];

        if (! ($user && in_array($user->id, $allowedIds, true))) {
            abort(403);
        }

        if ($package->user_id !== $user->id) {
            abort(403);
        }

        return response()->json(['ok' => true, 'data' => $package->data], 200);
    }

    public function destroy(Package $package)
    {
        $user = Auth::user();

        $allowedIds = [1, 2, 5, 7, 8];

        if (! ($user && in_array($user->id, $allowedIds, true))) {
            abort(403);
        }

        if ($package->user_id !== $user->id) {
            abort(403);
        }

        $package->delete();

        return response()->json(['ok' => true, 'message' => 'Package deleted'], 200);
    }

    public function store(Request $request)
    {
        $payload = $request->json()->all();

        if (empty($payload)) {
            $raw = $request->input('data');
            if ($raw) {
                try {
                    $payload = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
                } catch (\Throwable $e) {
                    return response()->json(['ok' => false, 'error' => 'Invalid JSON payload'], 422);
                }
            }
        }

        if (empty($payload)) {
            return response()->json(['ok' => false, 'error' => 'No JSON payload provided'], 422);
        }

        try {
            $upload = new Package();
            $upload->user_id = Auth::id();
            $upload->filename = $request->input('filename');
            $upload->data = $payload;
            $upload->save();

            return response()->json(['ok' => true, 'id' => $upload->id], 201);
        } catch (\Throwable $e) {
            Log::error('PdfUpload store error: '.$e->getMessage());
            return response()->json(['ok' => false, 'error' => 'Server error'], 500);
        }
    }
}
