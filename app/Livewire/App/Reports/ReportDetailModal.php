<?php

namespace App\Livewire\App\Reports;

use Livewire\Component;
use App\Models\Report;
use App\Services\ReportSlaService;

class ReportDetailModal extends Component
{
    public $reporteId;
    public $selectedReport;
    public $sla = null;

    public function mount($reporteId)
    {
        $this->reporteId = $reporteId;
        $this->selectedReport = Report::with(['reportDetails.channel', 'reportedBy'])
            ->withMax('comments', 'created_at')
            ->find($reporteId);

        if ($this->selectedReport) {
            $this->sla = app(ReportSlaService::class)->evaluate(
                $this->selectedReport,
                $this->selectedReport->comments_max_created_at
            );
        }
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
