<?php

namespace App\Livewire\App\Reports\Create;

use App\Models\Chromecast;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class CreateChromecastReport extends Component
{
    public $reportData;

    public function mount()
    {
        $this->reportData = [
            'reviewed_by' => '',
            'chromecast' => [
                'status' => true,
                'description' => '',
            ],
        ];
    }

    public function saveReport()
    {
        $this->validate([
            'reportData.chromecast.description' => 'nullable|string|max:2000',
        ]);

        try {
            $report = Report::create([
                'category' => 'Chromecast',
                'type' => 'Functions',
                'reported_by' => Auth::id(),
                'area' => Auth::user()->area ?? Report::AREA_OTT,
                'status' => 'Reported',
            ]);

            Chromecast::create([
                'report_id' => $report->id,
                'description' => $this->reportData['chromecast']['description'],
            ]);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => __('Well done!'),
                'text' => __('Chromecast report created successfully.'),
            ]);

            $this->dispatch('reportCreated');

        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.app.reports.create.create-chromecast-report');
    }
}
