<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rack;
use App\Models\RackIpRange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RackIpRangeController extends Controller
{
    use AuthorizesRequests;

    public function index(Rack $rack, Request $request)
    {
        $this->authorize('manageIpAddressing', $rack);

        $query = $rack->ipRanges();

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('cidr_range', 'like', "%{$search}%")
                    ->orWhere('mask', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('vlan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('vlan')) {
            $query->where('vlan', (int) $request->input('vlan'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $ipRanges = $query->orderBy('vlan')->orderBy('cidr_range')
            ->paginate(25)->withQueryString();

        $allRanges = $rack->ipRanges();
        $stats = [
            'total' => (clone $allRanges)->count(),
            'active' => (clone $allRanges)->where('is_active', true)->count(),
            'vlans' => (clone $allRanges)->whereNotNull('vlan')->distinct()->count('vlan'),
        ];

        $vlans = (clone $allRanges)->whereNotNull('vlan')
            ->select('vlan')->distinct()->orderBy('vlan')->pluck('vlan');

        return view('admin.rack-layout.ip-addressing.index', [
            'rack' => $rack,
            'ipRanges' => $ipRanges,
            'stats' => $stats,
            'vlans' => $vlans,
            'filters' => $request->only(['search', 'vlan', 'status']),
        ]);
    }

    public function create(Rack $rack)
    {
        $this->authorize('create', RackIpRange::class);

        return view('admin.rack-layout.ip-addressing.form', [
            'rack' => $rack,
            'ipRange' => new RackIpRange(['rack_id' => $rack->id, 'is_active' => true]),
            'mode' => 'create',
        ]);
    }

    public function store(Rack $rack, Request $request)
    {
        $this->authorize('create', RackIpRange::class);
        $data = $this->validateIpRange($request, null);

        $rack->ipRanges()->create(array_merge($data, [
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]));

        session()->flash('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => __('IP Range added successfully.'),
        ]);

        return redirect()->route('admin.rack-layout.ip-addressing.index', $rack);
    }

    public function edit(Rack $rack, RackIpRange $ipRange)
    {
        $this->authorize('update', $ipRange);

        if ((int) $ipRange->rack_id !== (int) $rack->id) {
            abort(404);
        }

        return view('admin.rack-layout.ip-addressing.form', [
            'rack' => $rack,
            'ipRange' => $ipRange,
            'mode' => 'edit',
        ]);
    }

    public function update(Rack $rack, RackIpRange $ipRange, Request $request)
    {
        $this->authorize('update', $ipRange);

        if ((int) $ipRange->rack_id !== (int) $rack->id) {
            abort(404);
        }

        $data = $this->validateIpRange($request, $ipRange);
        $ipRange->update(array_merge($data, ['updated_by' => Auth::id()]));

        session()->flash('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => __('IP Range updated successfully.'),
        ]);

        return redirect()->route('admin.rack-layout.ip-addressing.index', $rack);
    }

    public function destroy(Rack $rack, RackIpRange $ipRange)
    {
        $this->authorize('delete', $ipRange);

        if ((int) $ipRange->rack_id !== (int) $rack->id) {
            abort(404);
        }

        $ipRange->delete();

        session()->flash('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => __('IP Range deleted successfully.'),
        ]);

        return redirect()->route('admin.rack-layout.ip-addressing.index', $rack);
    }

    private function validateIpRange(Request $request, ?RackIpRange $ipRange): array
    {
        $rules = [
            'cidr_range' => 'required|string|max:64',
            'mask' => 'required|string|max:32',
            'vlan' => 'nullable|integer|min:1|max:4094',
            'description' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ];
        $attrs = [
            'cidr_range' => __('CIDR range'),
            'mask' => __('mask'),
            'vlan' => __('VLAN'),
            'description' => __('description'),
            'is_active' => __('active'),
        ];
        $validated = $request->validate($rules, [], $attrs);
        $validated['is_active'] = $request->boolean('is_active', true);
        return $validated;
    }
}
