<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GrafanaPanel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class GrafanaController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', GrafanaPanel::class);

        $user = auth()->user();

        $query = GrafanaPanel::query();

        if ($user && in_array($user->area, ['OTT', 'DTH'])) {
            if ($user->area === 'DTH') {
                $query->where(function ($q) {
                    $q->where('area', 'DTH')
                        ->orWhereIn('id', [1, 3]);
                });
            } else {
                $query->where('area', $user->area);
            }
        }

        $panels = $query->get();

        $panels = $panels->sortBy(function ($panel) {
            if ($panel->id == 2) {
                return 3;
            }
            if ($panel->id == 3) {
                return 2;
            }

            return $panel->id;
        })->values();

        return view('admin.grafana.index', compact('panels'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', GrafanaPanel::class);

        return view('admin.grafana.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'area' => ['required', 'string', 'in:OTT,DTH'],
            'url' => ['required', 'string', 'max:255'],
            'api_url' => ['nullable', 'string', 'max:255'],
            'endpoint' => ['nullable', 'string', 'max:255'],
            'api_key' => ['nullable', 'string', 'max:255'],
        ]);

        $panel = GrafanaPanel::create([
            'name' => $validated['name'],
            'area' => $validated['area'],
            'url' => $validated['url'],
            'api_url' => $validated['api_url'] ?? null,
            'endpoint' => $validated['endpoint'] ?? null,
            'api_key' => $validated['api_key'] ?? null,
        ]);

        session()->flash('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => __('New Grafana panel created successfully.'),
        ]);

        return redirect()->route('admin.grafana.show', $panel);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $panel = GrafanaPanel::findOrFail($id);

        return view('admin.grafana.show', compact('panel'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $panel = GrafanaPanel::findOrFail($id);

        $this->authorize('edit', $panel);

        $isShared = in_array($panel->id, [1, 3]);

        return view('admin.grafana.edit', compact('panel', 'isShared'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $panel = GrafanaPanel::findOrFail($id);

        $isShared = in_array($panel->id, [1, 3]);

        if ($isShared && $request->has('area')) {
            session()->flash('swal', [
                'icon' => 'error',
                'title' => __('Invalid action!'),
                'text' => __('You are not allowed to change the area of a shared panel.'),
            ]);

            return redirect()->back()->withInput();
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:255'],
            'api_url' => ['nullable', 'string', 'max:255'],
            'endpoint' => ['nullable', 'string', 'max:255'],
            'api_key' => ['nullable', 'string', 'max:255'],
        ];

        if (! $isShared) {
            $rules['area'] = ['required', 'string', 'in:OTT,DTH'];
        }

        $validated = $request->validate($rules);

        $data = [
            'name' => $validated['name'],
            'url' => $validated['url'],
            'api_url' => $validated['api_url'] ?? null,
            'endpoint' => $validated['endpoint'] ?? null,
            'api_key' => $validated['api_key'] ?? null,
        ];

        if (! $isShared) {
            $data['area'] = $validated['area'];
        }

        $panel->update($data);

        session()->flash('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => __('Grafana panel updated successfully.'),
        ]);

        return redirect()->route('admin.grafana.show', $panel);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $panel = GrafanaPanel::findOrFail($id);

        $this->authorize('delete', $panel);

        $panel->delete();

        session()->flash('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => __('Grafana panel deleted successfully.'),
        ]);

        return redirect()->route('admin.grafana.index');
    }
}
