<?php

namespace App\Livewire\App\Reports\Edit;

use App\Mail\Reports\ReportUpdatedMail;
use App\Models\Report;
use App\Models\ReportDetail;
use App\Models\Stage;
use App\Models\Channel;
use Livewire\Component;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Enums\MediaIssue;

class EditMomentlyReport extends Component
{
    public $report;
    public $reportData = [];
    public $stages;
    public $protocols = ['HLS', 'DASH', 'HLS/DASH'];
    public $mediaOptions = [];

    public function mount(Report $report)
    {
        $currentUser = auth()->user();
        try {
            if (empty($currentUser) || ! $report->canBeEditedBy($currentUser)) {
                abort(403);
            }
        } catch (\Throwable $e) {
            \Log::error('EditMomentlyReport mount authorization failed: '.$e->getMessage(), ['report_id' => $report->id]);
            abort(403);
        }

        $this->report = $report;
        $userArea = $report->area ?? Report::AREA_OTT;

        $this->stages = Stage::where('status', '1')
            ->when($userArea, fn($q) => $q->where('area', $userArea))
            ->get();

        $this->reportData = [
            'category' => $report->category,
            'reviewed_by' => $report->reviewed_by,
            'channels' => $report->reportDetails->map(function ($detail) {
                $channel = $detail->channel;
                return [
                    'channel_id' => $detail->channel_id,
                    'stage' => $detail->stage_id,
                    'protocol' => $detail->protocol,
                    'media' => $detail->media,
                    'description' => $detail->description,
                    'image' => $channel->image,
                    'number' => $channel->number,
                    'name' => $channel->name,
                ];
            })->toArray(),
        ];

        // Populate media options from enum with colors/groups
        $this->mediaOptions = MediaIssue::optionsWithColors();
    }

    public function addChannel()
    {
        $this->reportData['channels'][] = $this->initializeChannel();
    }

    public function removeChannel($channelIndex)
    {
        unset($this->reportData['channels'][$channelIndex]);
    }

    protected function initializeChannel()
    {
        return [
            'channel_id' => '',
            'stage' => '',
            'protocol' => '',
            'media' => '',
            'description' => '',
        ];
    }

    public function updateReport()
    {
        try {
            $this->validateReportData();

            $this->report->category = $this->reportData['category'];
            $this->report->reviewed_by = $this->reportData['reviewed_by'];
            $this->report->updated_at = now();
            $this->report->save();

            $this->report->reportDetails()->delete();

            foreach ($this->reportData['channels'] as $channel) {
                ReportDetail::create([
                    'report_id' => $this->report->id,
                    'channel_id' => $channel['channel_id'],
                    'stage_id' => $channel['stage'],
                    'protocol' => $channel['protocol'],
                    'media' => $channel['media'],
                    'description' => $channel['description'],
                    'status' => 'Revision',
                ]);
            }

            $emails = User::whereJsonContains('report_mail_preferences->report_updated', true)
                ->pluck('email')
                ->toArray();

            Mail::to($emails)->send(new ReportUpdatedMail($this->report));

            session()->flash('swal', [
                'icon' => 'success',
                'title' => __('Updated!'),
                'text' => __('The momentary report was successfully updated.'),
            ]);

            $this->dispatch('reportUpdated');

            return redirect()->route('reports.show', $this->report->id);
        } catch (ValidationException $e) {
            $errorMessages = '<ul style="text-align: center;">';

            foreach ($e->errors() as $errorMessagesArray) {
                foreach ($errorMessagesArray as $message) {
                    $errorMessages .= "<li>• $message</li>";
                }
            }

            $errorMessages .= '</ul>';

            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => __('Error'),
                'html' => '<b>' . __('Your report update contains errors:') . '</b><br><br>' . $errorMessages,
            ]);
        }
    }

    protected function validateReportData()
    {
        $channelIds = array_column($this->reportData['channels'], 'channel_id');
        if (count($channelIds) !== count(array_unique($channelIds))) {
            throw ValidationException::withMessages([
                'reportData.channels' => [__('A channel cannot be selected more than once.')],
            ]);
        }

        $userArea = $this->report->area ?? Report::AREA_OTT;

        $conflictingChannels = ReportDetail::where('status', 'Revision')
            ->whereNot('report_id', $this->report->id)
            ->whereIn('channel_id', $channelIds)
            ->whereHas('report', fn($q) => $q->where('area', $userArea))
            ->pluck('channel_id')
            ->unique();

        if ($conflictingChannels->isNotEmpty()) {
            $conflictNames = Channel::whereIn('id', $conflictingChannels)->pluck('name')->implode(', ');
            throw ValidationException::withMessages([
                'reportData.channels' => [__('These channels are already in revision: ') . $conflictNames],
            ]);
        }

        $this->validate([
            'reportData.category' => 'required|string|max:255',
            'reportData.reviewed_by' => 'required|string|max:255',
            'reportData.channels' => 'required|array|min:1',
        ], [], [
            'reportData.category' => __('category name'),
            'reportData.reviewed_by' => __('reviewed by'),
            'reportData.channels' => __('channels')
        ]);

        $userArea = $this->report->area ?? Report::AREA_OTT;

        foreach ($this->reportData['channels'] as $index => $channel) {
            $protocolRule = $userArea === Report::AREA_DTH
                ? 'nullable'
                : 'required|in:' . implode(',', $this->protocols);

            $this->validate([
                "reportData.channels.$index.channel_id" => 'required|exists:channels,id',
                "reportData.channels.$index.stage" => [
                    'required',
                    Rule::exists('stages', 'id')->where(function ($query) use ($userArea) {
                        $query->where('status', '1')->when($userArea, fn($q) => $q->where('area', $userArea));
                    }),
                ],
                "reportData.channels.$index.protocol" => $protocolRule,
                "reportData.channels.$index.media" => 'required|in:' . implode(',', array_keys($this->mediaOptions)),
                "reportData.channels.$index.description" => 'required|string',
            ], [], [
                "reportData.channels.$index.channel_id" => __('channel'),
                "reportData.channels.$index.stage" => __('stage'),
                "reportData.channels.$index.protocol" => __('protocol'),
                "reportData.channels.$index.media" => __('media'),
                "reportData.channels.$index.description" => __('description'),
            ]);
        }
    }

    public function getChannelCount()
    {
        return count($this->reportData['channels']);
    }

    public function render()
    {
        $userArea = $this->report->area ?? Report::AREA_OTT;

        return view('livewire.app.reports.edit.edit-momently-report', [
            'channels' => Channel::where('status', '1')->orderBy('number')->get(),
            'stages' => $this->stages,
            'report' => $this->report,
            'userArea' => $userArea,
        ]);
    }
}
