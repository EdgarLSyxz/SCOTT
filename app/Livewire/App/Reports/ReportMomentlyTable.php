<?php

namespace App\Livewire\App\Reports;

use App\Models\Report;
use App\Mail\Reports\ReportResolvedMail;
use App\Services\ReportSlaService;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class ReportMomentlyTable extends Component
{
    use WithPagination;

    public $search = '';
    public $order = 'desc';
    public $areaFilter = 'all';
    public $selectedReport = null;
    public $showModal = false;
    protected $queryString = ['search', 'areaFilter' => ['except' => 'all']];
    protected $listeners = [
        'reportCreated' => '$refresh',
        'refreshReportsSla' => '$refresh',
        'markAsSolvedFromModal',
        'closeReportDetailsFromModal'
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleAreaFilter()
    {
        $auth = auth()->user();

        if (! $auth || $auth->id !== 1) {
            return;
        }

        $options = ['all', 'DTH', 'OTT'];

        $currentIndex = array_search($this->areaFilter, $options, true);

        $this->areaFilter = $options[($currentIndex === false ? 0 : ($currentIndex + 1) % count($options))];
        $this->resetPage();
    }

    public function formatDate($date)
    {
        $carbonDate = Carbon::parse($date);
        if ($carbonDate->isToday()) {
            return __('It was reported today at ') . $carbonDate->format('H:i');
        } elseif ($carbonDate->isYesterday()) {
            return __('It was reported yesterday at ') . $carbonDate->format('H:i');
        } else {
            return __('It was reported ') . $carbonDate->diffForHumans();
        }
    }

    public function openReportDetails($reportId)
    {
        $this->selectedReport = Report::with(['reportDetails.channel', 'reportedBy'])->where('id', $reportId)->first();
        $this->showModal = true;
    }

    public function closeReportDetails()
    {
        $this->selectedReport = null;
        $this->showModal = false;
    }

    public function toggleOrder()
    {
        $this->order = $this->order === 'asc' ? 'desc' : 'asc';
        $this->resetPage();
    }

    public function markAsSolvedFromModal($reporteId)
    {
        $this->selectedReport = Report::with(['reportDetails.channel', 'reportedBy'])->find($reporteId);
        $this->markAsSolved();
    }

    public function closeReportDetailsFromModal()
    {
        $this->closeReportDetails();
    }

    public function markAsSolved()
    {
        if ($this->selectedReport) {
            $endTime = Carbon::now();
            $createdTime = Carbon::parse($this->selectedReport->created_at);
            $duration = $createdTime->diff($endTime)->format('%H:%I:%S');

            $this->selectedReport->update([
                'duration' => $duration,
                'attended_by' => auth()->user()->id,
                'status' => 'Resolved',
            ]);

            foreach ($this->selectedReport->reportDetails as $detail) {
                $detail->update(['status' => 'Resolved']);
            }

            $this->dispatch('reportUpdated');

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => __('Well done!'),
                'text' => __('The report and channels have been marked as resolved.'),
            ]);

            $emails = User::whereJsonContains('report_mail_preferences->report_resolved', true)
                ->pluck('email')
                ->toArray();

            Mail::to($emails)->send(new ReportResolvedMail(
                $this->selectedReport->load(['reportDetails.channel', 'reportedBy'])
            ));

            $this->showModal = false;
        }
    }

    public function render()
    {
        $query = Report::where('type', 'Momentary')
            ->where('status', 'Revision')
            ->where(function ($query) {
                $query->where('category', 'like', '%' . $this->search . '%')
                    ->orWhereHas('reportDetails.channel', function ($channelQuery) {
                        $channelQuery->where(function ($q) {
                            $q->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('number', 'like', '%' . $this->search . '%')
                                ->orWhereRaw("CONCAT(number, ' ', name) LIKE ?", ['%' . $this->search . '%']);
                        });
                    });
            })
            ->with(['reportDetails.channel', 'reportedBy', 'attendedBy'])
            ->withMax('comments', 'created_at')
            ->orderBy('created_at', $this->order)
        ;

        $auth = auth()->user();

        if ($auth && $auth->id === 1) {
            if (in_array($this->areaFilter, ['OTT', 'DTH'])) {
                $filter = $this->areaFilter;
                $query->where('area', $filter);
            }
        } else {
            if ($auth) {
                $viewerArea = $auth->area ?? null;
                if (in_array($viewerArea, ['OTT', 'DTH'])) {
                    $query->where('area', $viewerArea);
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $slaService = app(ReportSlaService::class);

        $reports = $query->paginate(5);

        $collection = collect($reports->items())->map(function ($report) use ($slaService) {
            $report->formatted_date = $this->formatDate($report->created_at);
            $report->channels_preview = $report->reportDetails->take(3)
                ->map(fn($detail) => $detail->channel->name)
                ->implode(', ') . ($report->reportDetails->count() > 3 ? '...' : '');
            $report->sla = $slaService->evaluate($report, $report->comments_max_created_at);
            return $report;
        });

        $reports->setCollection($collection);

        return view('livewire.app.reports.report-momently-table', [
            'reports' => $reports,
        ]);
    }
}
