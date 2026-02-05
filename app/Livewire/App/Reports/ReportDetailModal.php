<?php

namespace App\Livewire\App\Reports;

use Livewire\Component;
use App\Models\Report;

class ReportDetailModal extends Component
{
    public $reporteId;
    public $selectedReport;

    public function mount($reporteId)
    {
        $this->reporteId = $reporteId;
        $this->selectedReport = Report::with(['reportDetails.channel', 'reportedBy'])->find($reporteId);
    }

    public function markAsSolved()
    {
        $this->dispatch('markAsSolvedFromModal', $this->reporteId);
    }

    public function closeReportDetails()
    {
        $this->dispatch('closeReportDetailsFromModal');
    }

    public function render()
    {
        return view('livewire.app.reports.report-detail-modal');
    }
}
