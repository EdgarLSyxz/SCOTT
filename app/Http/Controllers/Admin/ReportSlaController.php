<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\ReportSlaSetting;
use Illuminate\Http\Request;

class ReportSlaController extends Controller
{
    public function index()
    {
        $this->authorizeAccess();

        $areas = Report::getAreas();

        $settings = ReportSlaSetting::whereIn('area', $areas)
            ->get()
            ->keyBy('area');

        return view('admin.sla.index', [
            'areas' => $areas,
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $this->authorizeAccess();

        $areas = Report::getAreas();

        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.is_active' => ['nullable', 'boolean'],
            'settings.*.level_1_minutes' => ['required', 'integer', 'min:1', 'max:10080'],
            'settings.*.level_2_minutes' => ['required', 'integer', 'min:1', 'max:10080'],
            'settings.*.level_3_minutes' => ['required', 'integer', 'min:1', 'max:10080'],
        ]);

        foreach ($areas as $area) {
            $data = $validated['settings'][$area] ?? null;

            if (! $data) {
                continue;
            }

            $l1 = (int) $data['level_1_minutes'];
            $l2 = (int) $data['level_2_minutes'];
            $l3 = (int) $data['level_3_minutes'];

            if (! ($l1 < $l2 && $l2 < $l3)) {
                return back()
                    ->withInput()
                    ->withErrors([
                        "settings.$area.level_1_minutes" => __('SLA Levels for :area must be ascending (L1 < L2 < L3).', ['area' => $area]),
                    ]);
            }

            ReportSlaSetting::updateOrCreate(
                ['area' => $area],
                [
                    'is_active' => (bool) ($data['is_active'] ?? false),
                    'level_1_minutes' => $l1,
                    'level_2_minutes' => $l2,
                    'level_3_minutes' => $l3,
                ]
            );
        }

        return back()->with('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => __('SLA Parameters were updated successfully.'),
        ]);
    }

    private function authorizeAccess(): void
    {
        $user = auth()->user();
        $allowedUserIds = [1, 2, 3, 5, 7, 8];

        if (! $user || ! in_array((int) $user->id, $allowedUserIds, true)) {
            abort(403);
        }
    }
}
