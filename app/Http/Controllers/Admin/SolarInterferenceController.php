<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SolarInterference;
use App\Models\SolarInterferenceUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SolarInterferenceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user || ! $user->can('viewAny', SolarInterferenceUpload::class)) {
            abort(403);
        }

        $query = SolarInterferenceUpload::query()->orderByDesc('created_at');

        if ($request->filled('search')) {
            $term = trim((string) $request->input('search'));
            $query->where('document_name', 'like', "%{$term}%");
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $uploads = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => SolarInterferenceUpload::count(),
            'active' => SolarInterferenceUpload::where('is_active', true)->count(),
            'records' => SolarInterference::count(),
            'states' => SolarInterference::where('section', SolarInterference::SECTION_STATE)->distinct('region_name')->count('region_name'),
            'satellites' => SolarInterference::where('section', SolarInterference::SECTION_SATELLITE)->distinct('region_name')->count('region_name'),
        ];

        return view('admin.solar-interferences.index', compact('uploads', 'stats'));
    }

    public function create()
    {
        $user = Auth::user();
        if (! $user || ! $user->can('create', SolarInterferenceUpload::class)) {
            abort(403);
        }

        return view('admin.solar-interferences.create');
    }

    public function show(SolarInterferenceUpload $upload)
    {
        $user = Auth::user();
        if (! $user || ! $user->can('view', $upload)) {
            abort(403);
        }

        $records = SolarInterference::forDocument($upload->document_name)
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get();

        return view('admin.solar-interferences.show', [
            'upload' => $upload,
            'records' => $records,
        ]);
    }

    public function destroy(SolarInterferenceUpload $upload)
    {
        $user = Auth::user();
        if (! $user || ! $user->can('delete', $upload)) {
            abort(403);
        }

        $documentName = $upload->document_name;
        $deleted = SolarInterference::forDocument($documentName)->delete();
        $upload->delete();

        return redirect()->route('admin.solar-interferences.index')->with('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => __(':count records removed from the upload.', ['count' => $deleted]),
        ]);
    }

    public function toggleStatus(SolarInterferenceUpload $upload)
    {
        $user = Auth::user();
        if (! $user || ! $user->can('update', $upload)) {
            abort(403);
        }

        $upload->update(['is_active' => ! $upload->is_active]);

        return redirect()->route('admin.solar-interferences.show', $upload)->with('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => $upload->is_active
                ? __('Upload marked as active and now visible on the dashboard.')
                : __('Upload marked as inactive and hidden from the dashboard.'),
        ]);
    }
}
