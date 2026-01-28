<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DeviceController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Device::class);

        return view('admin.devices.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Device $device)
    {
        if ($device->id === 10 && (! Auth::user() || Auth::id() !== 1)) {
            abort(403);
        }

        $this->authorize('view', $device);

        return view('admin.devices.show', compact('device'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Device $device)
    {
        if ($device->id === 10 && (! Auth::user() || Auth::id() !== 1)) {
            abort(403);
        }

        $this->authorize('update', $device);

        return view('admin.devices.edit', compact('device'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
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
        $user = Auth::user();

        $allowedIds = [1, 2, 5, 7, 8];

        if (! ($user && in_array($user->id, $allowedIds, true))) {
            abort(403);
        }

        $devices = Device::orderBy('name')->get(['id', 'name']);

        return view('admin.devices.monthly-downloads', compact('devices'));
    }

    public function packages()
    {
        $user = Auth::user();

        $allowedIds = [1, 2, 5, 7, 8];

        if (! ($user && in_array($user->id, $allowedIds, true))) {
            abort(403);
        }

        return view('admin.devices.packages');
    }
}
