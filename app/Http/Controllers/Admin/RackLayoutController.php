<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rack;
use App\Models\RackEquipment;
use App\Models\RackEquipmentHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RackLayoutController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Rack::class);

        $racks = Rack::orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(15);

        $racksWithCoords = Rack::withCoordinates()
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get()
            ->map(function (Rack $r) {
                return [
                    'id' => $r->id,
                    'name' => $r->name,
                    'location' => $r->location,
                    'is_active' => (bool) $r->is_active,
                    'total_units' => (int) $r->total_units,
                    'occupied_count' => (int) $r->occupied_positions_count,
                    'latitude' => (float) $r->latitude,
                    'longitude' => (float) $r->longitude,
                    'show_url' => route('admin.rack-layout.show', $r),
                ];
            })
            ->values()
            ->all();

        return view('admin.rack-layout.index', [
            'racks' => $racks,
            'racksWithCoords' => $racksWithCoords,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Rack::class);

        return view('admin.rack-layout.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Rack::class);

        $request->validate([
            'name' => 'required|string|max:120',
            'location' => 'nullable|string|max:160',
            'total_units' => 'required|integer|min:1|max:100',
            'description' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'nullable|boolean',
        ], [], [
            'name' => __('rack name'),
            'location' => __('rack location'),
            'total_units' => __('total units'),
            'description' => __('description'),
            'latitude' => __('latitude'),
            'longitude' => __('longitude'),
            'is_active' => __('status'),
        ]);

        $rack = Rack::create([
            'name' => $request->name,
            'location' => $request->location,
            'total_units' => $request->total_units,
            'description' => $request->description,
            'latitude' => $request->latitude !== null && $request->latitude !== '' ? (float) $request->latitude : null,
            'longitude' => $request->longitude !== null && $request->longitude !== '' ? (float) $request->longitude : null,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => Auth::id(),
        ]);

        session()->flash('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => __('Rack created successfully.')
        ]);

        return redirect()->route('admin.rack-layout.show', $rack);
    }

    public function show(Rack $rack)
    {
        $this->authorize('view', $rack);

        $positions = $rack->positionsMap();
        $ipRanges = $rack->activeIpRanges()->limit(10)->get();

        $colorChoices = $this->getColorChoices();

        return view('admin.rack-layout.show', [
            'rack' => $rack,
            'positions' => $positions,
            'ipRanges' => $ipRanges,
            'colorChoices' => $colorChoices,
        ]);
    }

    public function editRack(Rack $rack)
    {
        $this->authorize('edit', $rack);

        return view('admin.rack-layout.edit-rack', compact('rack'));
    }

    public function updateRack(Request $request, Rack $rack)
    {
        $this->authorize('edit', $rack);

        $request->validate([
            'name' => 'required|string|max:120',
            'location' => 'nullable|string|max:160',
            'total_units' => 'required|integer|min:1|max:100',
            'description' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'nullable|boolean',
        ], [], [
            'name' => __('rack name'),
            'location' => __('rack location'),
            'total_units' => __('total units'),
            'description' => __('description'),
            'latitude' => __('latitude'),
            'longitude' => __('longitude'),
            'is_active' => __('status'),
        ]);

        $maxOccupiedPosition = (int) RackEquipment::where('rack_id', $rack->id)
            ->where('is_active', true)
            ->selectRaw('COALESCE(MAX(position + size_u - 1), 0) AS max_end')
            ->value('max_end');

        if ($request->integer('total_units') < $maxOccupiedPosition) {
            session()->flash('swal', [
                'icon' => 'error',
                'title' => __('Cannot update rack'),
                'text' => __('The new total units (:new) is lower than the highest occupied position (:pos). Please clear or move that equipment first.', [
                    ':new' => $request->integer('total_units'),
                    ':pos' => $maxOccupiedPosition,
                ]),
            ]);

            return redirect()->back()->withInput();
        }

        $rack->update([
            'name' => $request->name,
            'location' => $request->location,
            'total_units' => $request->total_units,
            'description' => $request->description,
            'latitude' => $request->latitude !== null && $request->latitude !== '' ? (float) $request->latitude : null,
            'longitude' => $request->longitude !== null && $request->longitude !== '' ? (float) $request->longitude : null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        session()->flash('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => __('Rack updated successfully.')
        ]);

        return redirect()->route('admin.rack-layout.show', $rack);
    }

    public function edit(Rack $rack, $position)
    {
        $this->authorize('edit', $rack);
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
        $this->authorize('update', $rack);
        $this->validatePosition($rack, $position);

        $request->validate([
            'equipment_name' => 'nullable|string|max:160',
            'equipment_model' => 'nullable|string|max:160',
            'equipment_role' => 'nullable|string|max:120',
            'ip_address' => 'nullable|string|max:64',
            'serial_number' => 'nullable|string|max:120',
            'mac_address' => 'nullable|string|max:32',
            'vendor' => 'nullable|string|max:120',
            'installation_date' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
            'color' => ['nullable', Rule::in(array_keys($this->getColorChoices()))],
            'size_u' => 'required|integer|min:1|max:' . $rack->total_units,
            'image_url' => 'nullable|image',
            'remove_image' => 'nullable|boolean',
        ], [], [
            'equipment_name' => __('equipment name'),
            'equipment_model' => __('model'),
            'equipment_role' => __('role / function'),
            'ip_address' => __('IP address'),
            'serial_number' => __('serial number'),
            'mac_address' => __('MAC address'),
            'vendor' => __('vendor'),
            'installation_date' => __('installation date'),
            'notes' => __('notes'),
            'color' => __('highlight color'),
            'size_u' => __('size (U)'),
            'image_url' => __('equipment image'),
            'remove_image' => __('remove image'),
        ]);

        $start = (int) $position;
        $size = (int) $request->input('size_u', 1);
        $end = $start + $size - 1;

        if ($end > (int) $rack->total_units) {
            session()->flash('swal', [
                'icon' => 'error',
                'title' => __('Cannot save equipment'),
                'text' => __('The equipment of :size U starting at U:start would end at U:end, but the rack only has :total units. Please reduce the size or move the equipment.', [
                    ':size' => $size,
                    ':start' => $start,
                    ':end' => $end,
                    ':total' => $rack->total_units,
                ]),
            ]);

            return redirect()->back()->withInput();
        }

        $existing = RackEquipment::where('rack_id', $rack->id)
            ->where('position', $start)
            ->first();

        $collision = $this->findCollision($rack, $start, $size, $existing?->id);
        if ($collision !== null) {
            session()->flash('swal', [
                'icon' => 'error',
                'title' => __('Position conflict'),
                'text' => __('The range U:start-U:end overlaps with ":name" (U:cstart-U:cend). Please choose a different size or clear that equipment first.', [
                    ':start' => $start,
                    ':end' => $end,
                    ':name' => $collision->equipment_name ?: __('unnamed equipment'),
                    ':cstart' => (int) $collision->position,
                    ':cend' => (int) $collision->end_position,
                ]),
            ]);

            return redirect()->back()->withInput();
        }

        $equipment = RackEquipment::updateOrCreate(
            ['rack_id' => $rack->id, 'position' => $start],
            [
                'rack_id' => $rack->id,
                'position' => $start,
                'size_u' => $size,
                'equipment_name' => $request->equipment_name,
                'equipment_model' => $request->equipment_model,
                'equipment_role' => $request->equipment_role,
                'ip_address' => $request->ip_address,
                'serial_number' => $request->serial_number,
                'mac_address' => $request->mac_address,
                'vendor' => $request->vendor,
                'installation_date' => $request->installation_date,
                'notes' => $request->notes,
                'color' => $request->color,
                'is_active' => $request->boolean('is_active', true),
                'updated_by' => Auth::id(),
            ]
        );

        $finalImagePath = $equipment->image_url;

        if ($request->boolean('remove_image') && $equipment->image_url) {
            if (Storage::disk('public')->exists($equipment->image_url)) {
                Storage::disk('public')->delete($equipment->image_url);
            }
            $finalImagePath = null;
        } elseif ($request->hasFile('image_url')) {
            $file = $request->file('image_url');
            $imageName = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            if ($equipment->image_url && Storage::disk('public')->exists($equipment->image_url)) {
                Storage::disk('public')->delete($equipment->image_url);
            }
            $file->storeAs('rack_equipment', $imageName, 'public');
            $finalImagePath = 'rack_equipment/' . $imageName;
        }

        if ($finalImagePath !== $equipment->image_url) {
            $equipment->image_url = $finalImagePath;
            $equipment->save();
        }

        session()->flash('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => __('Equipment updated successfully.')
        ]);

        return redirect()->route('admin.rack-layout.show', $rack);
    }

    public function destroy(Rack $rack, $position)
    {
        $this->authorize('delete', $rack);
        $this->validatePosition($rack, $position);

        $equipment = RackEquipment::where('rack_id', $rack->id)
            ->where('position', (int) $position)
            ->first();

        if ($equipment) {
            if ($equipment->image_url && Storage::disk('public')->exists($equipment->image_url)) {
                Storage::disk('public')->delete($equipment->image_url);
            }
            $equipment->delete();
        }

        session()->flash('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => __('Equipment cleared from position.')
        ]);

        return redirect()->route('admin.rack-layout.show', $rack);
    }

    public function destroyRack(Rack $rack)
    {
        $this->authorize('delete', $rack);

        $rackName = $rack->name;
        $equipmentCount = $rack->equipment()->count();
        $historyCount = $rack->history()->count();

        \DB::transaction(function () use ($rack) {
            $rack->equipment()->delete();
            $rack->history()->delete();
            $rack->delete();
        });

        session()->flash('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => __('Rack ":name" deleted successfully. (:eq equipment, :h history)', [
                ':name' => $rackName,
                ':eq' => $equipmentCount,
                ':h' => $historyCount,
            ]),
        ]);

        return redirect()->route('admin.rack-layout.index');
    }

    public function history(Request $request, Rack $rack)
    {
        $this->authorize('view', $rack);

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
        $this->authorize('view', $rack);
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
        $this->authorize('view', $rack);

        $positions = $rack->positionsMap();

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

    public function map()
    {
        $this->authorize('viewMap', Rack::class);

        $racks = Rack::withCoordinates()
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get()
            ->map(function (Rack $r) {
                return [
                    'id' => $r->id,
                    'name' => $r->name,
                    'location' => $r->location,
                    'description' => $r->description,
                    'is_active' => (bool) $r->is_active,
                    'total_units' => (int) $r->total_units,
                    'occupied_count' => (int) $r->occupied_positions_count,
                    'latitude' => (float) $r->latitude,
                    'longitude' => (float) $r->longitude,
                    'show_url' => route('admin.rack-layout.show', $r),
                ];
            })
            ->values()
            ->all();

        $racksWithoutCoords = Rack::where(function ($q) {
            $q->whereNull('latitude')->orWhereNull('longitude');
        })
            ->orderBy('name')
            ->get(['id', 'name', 'location', 'latitude', 'longitude']);

        return view('admin.rack-layout.map', [
            'racks' => $racks,
            'racksWithoutCoords' => $racksWithoutCoords,
        ]);
    }

    private function validatePosition(Rack $rack, $position): void
    {
        $pos = (int) $position;
        if ($pos < 1 || $pos > $rack->total_units) {
            abort(404, __('Invalid rack position.'));
        }
    }

    private function findCollision(Rack $rack, int $start, int $size, ?int $ignoreId = null): ?RackEquipment
    {
        $end = $start + $size - 1;

        return RackEquipment::where('rack_id', $rack->id)
            ->where('is_active', true)
            ->where('position', '<=', $end)
            ->whereRaw('position + size_u - 1 >= ?', [$start])
            ->when($ignoreId !== null, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->first();
    }

    private function getColorChoices(): array
    {
        return [
            'green' => __('Green - Highlighted'),
            'red' => __('Red - Critical'),
            'blue' => __('Blue - Info'),
            'yellow' => __('Yellow - Caution'),
            'orange' => __('Orange - Warning'),
        ];
    }
}
