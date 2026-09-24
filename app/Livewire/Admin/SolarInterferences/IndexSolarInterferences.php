<?php

namespace App\Livewire\Admin\SolarInterferences;

use App\Models\SolarInterference;
use App\Models\SolarInterferenceUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class IndexSolarInterferences extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $status = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function toggleStatus($documentName)
    {
        $upload = SolarInterferenceUpload::where('document_name', $documentName)->firstOrFail();

        if (! Auth::user() || ! Gate::allows('update', $upload)) {
            abort(403);
        }

        $upload->update(['is_active' => ! $upload->is_active]);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => $upload->is_active
                ? __('Upload marked as active and now visible on the dashboard.')
                : __('Upload marked as inactive and hidden from the dashboard.'),
        ]);
    }

    public function deleteUpload($documentName)
    {
        $upload = SolarInterferenceUpload::where('document_name', $documentName)->firstOrFail();

        if (! Auth::user() || ! Gate::allows('delete', $upload)) {
            abort(403);
        }

        $deleted = SolarInterference::forDocument($documentName)->delete();
        $upload->delete();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => __(':count records removed from the upload.', ['count' => $deleted]),
        ]);
    }

    public function render()
    {
        $query = SolarInterferenceUpload::query()->orderByDesc('created_at');

        if ($this->search !== '') {
            $term = trim($this->search);
            $query->where('document_name', 'like', "%{$term}%");
        }

        if ($this->status === 'active') {
            $query->where('is_active', true);
        } elseif ($this->status === 'inactive') {
            $query->where('is_active', false);
        }

        $uploads = $query->paginate(20);

        $stats = [
            'total' => SolarInterferenceUpload::count(),
            'active' => SolarInterferenceUpload::where('is_active', true)->count(),
            'records' => SolarInterference::count(),
            'states' => SolarInterference::where('section', SolarInterference::SECTION_STATE)->distinct('region_name')->count('region_name'),
            'satellites' => SolarInterference::where('section', SolarInterference::SECTION_SATELLITE)->distinct('region_name')->count('region_name'),
        ];

        return view('livewire.admin.solar-interferences.index-solar-interferences', [
            'uploads' => $uploads,
            'stats' => $stats,
        ]);
    }
}
