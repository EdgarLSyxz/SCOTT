<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rack;
use App\Models\RackEquipment;
use App\Models\RackEquipmentHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RackLayoutController extends Controller
{
    public function index()
    {
        $racks = Rack::orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.rack-layout.index', compact('racks'));
    }

    public function create()
    {
        $this->ensureAdmin();

        return view('admin.rack-layout.create');
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $data = $this->validateRack($request);
        $data['created_by'] = Auth::id();

        $rack = Rack::create($data);

        return redirect()
            ->route('admin.rack-layout.show', $rack)
            ->with('swal', [
                'icon' => 'success',
                'title' => __('Well done!'),
                'text' => __('Rack created successfully.'),
            ]);
    }

    public function show(Rack $rack)
    {
        $rack->load(['equipment' => function ($q) {
            $q->orderBy('position');
        }]);

        $positionMap = $rack->equipment->keyBy('position');

        $positions = [];
        for ($i = 1; $i <= $rack->total_units; $i++) {
            $positions[] = $positionMap->get($i);
        }

        $colorChoices = $this->getColorChoices();

        return view('admin.rack-layout.show', [
            'rack' => $rack,
            'positions' => $positions,
            'colorChoices' => $colorChoices,
        ]);
    }

    public function edit(Rack $rack, $position)
    {
        $this->ensureAdmin();
        $this->validatePosition($rack, $position);

        $equipment = RackEquipment::firstOrNew([
            'rack_id' => $rack->id,
            'position' => (int) $position,
        ]);

        $colorChoices = $this->getColorChoices();

        return view('admin.rack-layout.edit', [
            'rack' => $rack,
            'position' => (int) $position,
            'equipment' => $equipment,
            'colorChoices' => $colorChoices,
        ]);
    }

    public function update(Request $request, Rack $rack, $position)
    {
        $this->ensureAdmin();
        $this->validatePosition($rack, $position);

        $data = $this->validateEquipment($request);
        $data['rack_id'] = $rack->id;
        $data['position'] = (int) $position;
        $data['updated_by'] = Auth::id();
        $data['is_active'] = $request->boolean('is_active', true);

        $equipment = RackEquipment::updateOrCreate(
            ['rack_id' => $rack->id, 'position' => (int) $position],
            $data
        );

        return redirect()
            ->route('admin.rack-layout.show', $rack)
            ->with('swal', [
                'icon' => 'success',
                'title' => __('Well done!'),
                'text' => __('Equipment updated successfully.'),
            ]);
    }

    public function destroy(Rack $rack, $position)
    {
        $this->ensureAdmin();
        $this->validatePosition($rack, $position);

        $equipment = RackEquipment::where('rack_id', $rack->id)
            ->where('position', (int) $position)
            ->first();

        if ($equipment) {
            $equipment->delete();
        }

        return redirect()
            ->route('admin.rack-layout.show', $rack)
            ->with('swal', [
                'icon' => 'success',
                'title' => __('Well done!'),
                'text' => __('Equipment cleared from position.'),
            ]);
    }

    public function destroyRack(Rack $rack)
    {
        $this->ensureAdmin();

        $rack->delete();

        return redirect()
            ->route('admin.rack-layout.index')
            ->with('swal', [
                'icon' => 'success',
                'title' => __('Well done!'),
                'text' => __('Rack deleted successfully.'),
            ]);
    }

    public function history(Request $request, Rack $rack)
    {
        $query = RackEquipmentHistory::where('rack_id', $rack->id)
            ->with('user');

        if ($request->filled('position')) {
            $query->where('position', (int) $request->input('position'));
        }
        if ($request->filled('change_type')) {
            $query->where('change_type', $request->input('change_type'));
        }
        if ($request->filled('from')) {
            $query->whereDate('changed_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('changed_at', '<=', $request->input('to'));
        }
        if ($request->filled('user_id')) {
            $query->where('changed_by', (int) $request->input('user_id'));
        }

        $history = $query->orderByDesc('changed_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.rack-layout.history', [
            'rack' => $rack,
            'history' => $history,
            'filters' => $request->only(['position', 'change_type', 'from', 'to', 'user_id']),
        ]);
    }

    public function positionHistory(Rack $rack, $position)
    {
        $this->validatePosition($rack, $position);

        $history = RackEquipmentHistory::where('rack_id', $rack->id)
            ->where('position', (int) $position)
            ->with('user')
            ->orderByDesc('changed_at')
            ->paginate(25);

        $current = RackEquipment::where('rack_id', $rack->id)
            ->where('position', (int) $position)
            ->first();

        return view('admin.rack-layout.position-history', [
            'rack' => $rack,
            'position' => (int) $position,
            'history' => $history,
            'current' => $current,
        ]);
    }

    public function exportPdf(Rack $rack)
    {
        $rack->load(['equipment' => function ($q) {
            $q->orderBy('position');
        }]);

        $positionMap = $rack->equipment->keyBy('position');
        $positions = [];
        for ($i = 1; $i <= $rack->total_units; $i++) {
            $positions[] = $positionMap->get($i);
        }

        $html = view('admin.rack-layout.pdf', [
            'rack' => $rack,
            'positions' => $positions,
        ])->render();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();

        $filename = 'Rack-' . preg_replace('/\s+/', '-', $rack->name) . '-' . now()->format('Ymd') . '.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function validateRack(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:160'],
            'total_units' => ['required', 'integer', 'min:1', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function validateEquipment(Request $request): array
    {
        return $request->validate([
            'equipment_name' => ['nullable', 'string', 'max:160'],
            'equipment_model' => ['nullable', 'string', 'max:160'],
            'equipment_role' => ['nullable', 'string', 'max:120'],
            'ip_address' => ['nullable', 'string', 'max:64'],
            'serial_number' => ['nullable', 'string', 'max:120'],
            'mac_address' => ['nullable', 'string', 'max:32'],
            'vendor' => ['nullable', 'string', 'max:120'],
            'installation_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'color' => ['nullable', Rule::in(array_keys($this->getColorChoices()))],
        ]);
    }

    private function validatePosition(Rack $rack, $position): void
    {
        $pos = (int) $position;
        if ($pos < 1 || $pos > $rack->total_units) {
            abort(404, __('Invalid rack position.'));
        }
    }

    private function ensureAdmin(): void
    {
        $user = Auth::user();
        if (! $user || $user->id !== 1) {
            abort(403, __('Only administrators can perform this action.'));
        }
    }

    private function getColorChoices(): array
    {
        return [
            '' => __('Default'),
            'green' => __('Green - Highlighted'),
            'red' => __('Red - Critical'),
            'blue' => __('Blue - Info'),
            'yellow' => __('Yellow - Caution'),
            'orange' => __('Orange - Warning'),
        ];
    }
}
