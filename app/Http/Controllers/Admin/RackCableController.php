<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rack;
use App\Models\RackCable;
use App\Models\RackEquipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RackCableController extends Controller
{
    use AuthorizesRequests;

    public function index(Rack $rack, Request $request)
    {
        $this->authorize('viewAny', RackCable::class);

        $query = $rack->cables()->with(['sourceEquipment', 'creator']);

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('destination_label', 'like', "%{$search}%")
                    ->orWhere('destination_ip', 'like', "%{$search}%")
                    ->orWhere('source_port', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($request->filled('vlan')) {
            $query->where('vlan', (int) $request->input('vlan'));
        }
        if ($request->filled('cable_type')) {
            $query->where('cable_type', $request->input('cable_type'));
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $cables = $query->orderBy('source_position')
            ->orderBy('source_port')
            ->paginate(25)
            ->withQueryString();

        return view('admin.rack-layout.cables.index', [
            'rack' => $rack,
            'cables' => $cables,
            'cableTypes' => RackCable::CABLE_TYPES,
            'filters' => $request->only(['search', 'vlan', 'cable_type', 'is_active']),
        ]);
    }

    public function create(Rack $rack)
    {
        $this->authorize('create', RackCable::class);

        return view('admin.rack-layout.cables.create', [
            'rack' => $rack,
            'cable' => new RackCable(['rack_id' => $rack->id, 'is_active' => true]),
            'cableTypes' => RackCable::CABLE_TYPES,
            'colors' => RackCable::COLORS,
        ]);
    }

    public function store(Rack $rack, Request $request)
    {
        $this->authorize('create', RackCable::class);

        $data = $this->validateCable($request, $rack, null);

        $rack->cables()->create(array_merge($data, [
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]));

        session()->flash('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => __('Cable added successfully.'),
        ]);

        return redirect()->route('admin.rack-layout.cables.index', $rack);
    }

    public function edit(Rack $rack, RackCable $cable)
    {
        $this->authorize('update', $cable);

        if ((int) $cable->rack_id !== (int) $rack->id) {
            abort(404);
        }

        return view('admin.rack-layout.cables.edit', [
            'rack' => $rack,
            'cable' => $cable,
            'cableTypes' => RackCable::CABLE_TYPES,
            'colors' => RackCable::COLORS,
        ]);
    }

    public function update(Rack $rack, RackCable $cable, Request $request)
    {
        $this->authorize('update', $cable);

        if ((int) $cable->rack_id !== (int) $rack->id) {
            abort(404);
        }

        $data = $this->validateCable($request, $rack, $cable);

        $cable->update(array_merge($data, ['updated_by' => Auth::id()]));

        session()->flash('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => __('Cable updated successfully.'),
        ]);

        return redirect()->route('admin.rack-layout.cables.index', $rack);
    }

    public function destroy(Rack $rack, RackCable $cable)
    {
        $this->authorize('delete', $cable);

        if ((int) $cable->rack_id !== (int) $rack->id) {
            abort(404);
        }

        $cable->delete();

        session()->flash('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => __('Cable removed successfully.'),
        ]);

        return redirect()->route('admin.rack-layout.cables.index', $rack);
    }

    private function validateCable(Request $request, Rack $rack, ?RackCable $cable): array
    {
        $rules = [
            'source_position' => 'required|integer|min:1|max:' . (int) $rack->total_units,
            'source_equipment_id' => [
                'nullable',
                'integer',
                Rule::exists('rack_equipment', 'id')->where(function ($q) use ($rack) {
                    $q->where('rack_id', $rack->id);
                }),
            ],
            'source_port' => 'nullable|string|max:40',
            'destination_label' => 'required|string|max:160',
            'destination_ip' => 'nullable|string|max:64',
            'vlan' => 'nullable|integer|min:1|max:4094',
            'cable_type' => ['nullable', Rule::in(array_keys(RackCable::CABLE_TYPES))],
            'color' => ['nullable', Rule::in(array_keys(RackCable::COLORS))],
            'notes' => 'nullable|string|max:2000',
            'is_active' => 'nullable|boolean',
        ];

        $messages = [];
        $attrs = [
            'source_position' => __('slot (U)'),
            'source_equipment_id' => __('equipment'),
            'source_port' => __('source port'),
            'destination_label' => __('destination'),
            'destination_ip' => __('destination IP'),
            'vlan' => __('VLAN'),
            'cable_type' => __('cable type'),
            'color' => __('color'),
            'notes' => __('notes'),
            'is_active' => __('active'),
        ];

        $validated = $request->validate($rules, $messages, $attrs);

        $start = (int) $validated['source_position'];
        $end = $start + 1;
        $eqId = $validated['source_equipment_id'] ?? null;

        if ($eqId !== null) {
            $equipment = RackEquipment::find($eqId);
            if ($equipment) {
                $sizeU = max(1, (int) $equipment->size_u);
                $end = (int) $equipment->position + $sizeU - 1;
                if ($end > (int) $rack->total_units) {
                    $validated['source_position'] = $start;
                }
            }
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}
