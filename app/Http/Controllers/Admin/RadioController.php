<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Radio;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RadioController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $user = Auth::user();

        if (! ($user && ($user->id === 1 || ($user->area ?? null) === 'DTH'))) {
            abort(403);
        }

        $radios = Radio::when($user && $user->id !== 1, function ($query) use ($user) {
            return $query->where('area', $user->area);
        })->orderByDesc('created_at')->get();

        return view('admin.radios.index', compact('radios'));
    }

    public function create()
    {
        $user = Auth::user();

        if (! ($user && ($user->id === 1 || ($user->area ?? null) === 'DTH'))) {
            abort(403);
        }

        $this->authorize('create', Radio::class);

        return view('admin.radios.create');
    }

    public function store(Request $request) {}

    public function show(Radio $radio)
    {
        $user = Auth::user();

        if (! ($user && ($user->id === 1 || ($user->area ?? null) === 'DTH'))) {
            abort(403);
        }

        $this->authorize('view', $radio);

        return view('admin.radios.show', compact('radio'));
    }

    public function edit(Radio $radio)
    {
        $user = Auth::user();

        if (! ($user && ($user->id === 1 || ($user->area ?? null) === 'DTH'))) {
            abort(403);
        }

        $this->authorize('update', $radio);

        return view('admin.radios.edit', compact('radio'));
    }

    public function update(Request $request, string $id) {}

    public function destroy(Radio $radio)
    {
        $user = Auth::user();

        if (! ($user && ($user->id === 1 || ($user->area ?? null) === 'DTH'))) {
            abort(403);
        }

        $this->authorize('delete', $radio);

        if ($radio->image_url && Storage::disk('public')->exists($radio->image_url)) {

            Storage::disk('public')->delete($radio->image_url);
        }

        $radio->delete();

        return redirect()->route('admin.devices.index')->with('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => __('Device deleted successfully.'),
        ]);
    }
}
