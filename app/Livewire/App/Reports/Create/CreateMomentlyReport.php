<?php

namespace App\Livewire\App\Reports\Create;

use App\Enums\MediaIssue;
use App\Mail\Reports\ReportCreatedMail;
use Livewire\Component;
use App\Models\Report;
use App\Models\ReportDetail;
use App\Models\Stage;
use App\Models\Channel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class CreateMomentlyReport extends Component
{
    public $reportData;
    public $stages;
    public $protocols = ['HLS', 'DASH', 'HLS/DASH'];
    public $mediaOptions = [];

    public function mount()
    {
        $userArea = Auth::user()->area ?? Report::AREA_OTT;
        $stagesQuery = Stage::where('status', '1');
        if ($userArea) {
            $stagesQuery->where('area', $userArea);
        }
        $this->stages = $stagesQuery->orderBy('name')->get();
        $this->reportData = [
            'type' => 'Momentary',
            'category' => '',
            'reported_by' => '',
            'reviewed_by' => '',
            'channels' => [$this->initializeChannel()],
        ];

        $this->mediaOptions = MediaIssue::optionsWithColors();
    }

    public function addChannel()
    {
        $this->reportData['channels'][] = $this->initializeChannel();
    }

    public function removeChannel($index)
    {
        unset($this->reportData['channels'][$index]);
        $this->reportData['channels'] = array_values($this->reportData['channels']);
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

    public function saveReport()
    {
        try {
            $this->validateReportData();

            $userArea = Auth::user()->area ?? Report::AREA_OTT;

            foreach ($this->reportData['channels'] as $channel) {
                $existingChannel = ReportDetail::where('channel_id', $channel['channel_id'])
                    ->where('status', 'Revision')
                    ->whereHas('report', fn($q) => $q->where('area', $userArea))
                    ->exists();

                if ($existingChannel) {
                    throw ValidationException::withMessages([
                        'reportData.channels' => __('The channel is currently under review and cannot be added.')
                    ]);
                }
            }

            $channelIds = array_column($this->reportData['channels'], 'channel_id');

            $channelCounts = array_count_values($channelIds);

            foreach ($channelCounts as $channelId => $count) {
                if ($count > 1) {
                    throw ValidationException::withMessages([
                        'reportData.channels' => __('The channel ":channel" cannot be selected more than once.', [
                            'channel' => Channel::find($channelId)?->number ?? $channelId
                        ])
                    ]);
                }
            }

            $report = Report::create([
                'category' => $this->reportData['category'],
                'type' => $this->reportData['type'],
                'duration' => null,
                'reported_by' => Auth::user()->id,
                'reviewed_by' => $this->reportData['reviewed_by'],
                'attended_by' => null,
                'area' => Auth::user()->area ?? Report::AREA_OTT,
                'status' => 'Revision',
            ]);

            foreach ($this->reportData['channels'] as $channel) {
                ReportDetail::create([
                    'report_id' => $report->id,
                    'channel_id' => $channel['channel_id'],
                    'stage_id' => $channel['stage'],
                    'protocol' => $channel['protocol'],
                    'media' => $channel['media'],
                    'description' => $channel['description'],
                    'status' => 'Revision',
                ]);
            }

            $emails = User::whereJsonContains('report_mail_preferences->report_created', true)
                ->pluck('email')
                ->toArray();

            Mail::to($emails)->send(new ReportCreatedMail($report));

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => __('Well done!'),
                'text' => __('Momentary report created successfully.')
            ]);

            $this->dispatch('reportCreated');
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
                'html' => '<b>' . __('Your report log contains errors:') . '</b><br><br>' . $errorMessages,
            ]);
        }
    }

    protected function validateReportData()
    {
        $this->validate([
            'reportData.category' => 'required|string|max:255',
            'reportData.reviewed_by' => 'required|string|max:255',
            'reportData.channels' => 'required|array|min:1',
        ], [], [
            'reportData.category' => __('category name'),
            'reportData.reviewed_by' => __('reviewed by'),
            'reportData.channels' => __('channels')
        ]);

        $userArea = Auth::user()->area ?? Report::AREA_OTT;

        $baseStageRule = Rule::exists('stages', 'id')->where('status', '1');
        if ($userArea) {
            $baseStageRule = $baseStageRule->where('area', $userArea);
        }

        foreach ($this->reportData['channels'] as $index => $channel) {
            $protocolRule = $userArea === Report::AREA_DTH
                ? 'nullable'
                : 'required|in:' . implode(',', $this->protocols);

            $this->validate([
                "reportData.channels.$index.channel_id" => 'required|exists:channels,id',
                "reportData.channels.$index.stage" => ['required', $baseStageRule],
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
        $userArea = Auth::user()->area ?? Report::AREA_OTT;

        $channelsQuery = Channel::where('status', '1');

        if ($userArea === Report::AREA_OTT) {
            $channelsQuery->whereNotIn('category', ['Learning TV Channel', 'Radio TV Channel (DTH)']);
        } elseif ($userArea === Report::AREA_DTH) {
            $channelsQuery->where('category', '!=', 'FAST');
        }

        $channels = $channelsQuery->orderBy('number')->get();

        $stagesQuery = Stage::where('status', '1');
        if ($userArea) {
            $stagesQuery->where('area', $userArea);
        }
        $stages = $stagesQuery->orderBy('name')->get();

        return view('livewire.app.reports.create.create-momently-report', [
            'channels' => $channels,
            'stages' => $stages,
            'userArea' => $userArea,
        ]);
    }
}
