<?php

namespace App\Livewire\App\Reports\Create;

use App\Models\Channel;
use App\Models\ChannelTest;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class CreateProfileReport extends Component
{
    public $reportData;

    public function mount()
    {
        $preloadNumbers = [101, 102, 103, 105, 107, 154, 255, 302, 451, 501, 508];
        $preloadChannels = Channel::whereIn('number', $preloadNumbers)->pluck('id')->toArray();

        $this->reportData = [
            'title' => '',
            'reviewed_by' => '',
            'channels' => array_map(function ($channelId) {
                $channel = $this->initializeChannel();
                $channel['channel_id'] = $channelId;

                return $channel;
            }, $preloadChannels),
        ];
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
            'high' => \App\Enums\ChannelIssues::CORRECT->value,
            'medium' => \App\Enums\ChannelIssues::CORRECT->value,
            'low' => \App\Enums\ChannelIssues::CORRECT->value,
            'profiles' => [
                ['name' => 'HIGH', 'value' => ''],
                ['name' => 'MEDIUM', 'value' => ''],
                ['name' => 'LOW', 'value' => ''],
            ],
        ];
    }

    public function addProfile($channelIndex)
    {
        $this->reportData['channels'][$channelIndex]['profiles'][] = [
            'name' => '',
            'value' => '',
        ];
    }

    public function removeProfile($channelIndex, $profileIndex)
    {
        unset($this->reportData['channels'][$channelIndex]['profiles'][$profileIndex]);
        $this->reportData['channels'][$channelIndex]['profiles'] = array_values($this->reportData['channels'][$channelIndex]['profiles']);
    }

    public function saveReport()
    {
        try {
            $this->validateReportData();

            if (empty($this->reportData['channels']) || count($this->reportData['channels']) < 1) {
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => __('Error'),
                    'text' => __('You must select at least one channel to create a report.'),
                ]);

                return;
            }

            $channelIds = array_column($this->reportData['channels'], 'channel_id');
            $channelCounts = array_count_values($channelIds);
            $repeatedChannels = [];
            foreach ($channelCounts as $channelId => $count) {
                if ($count > 1) {
                    $repeatedChannels[] = Channel::find($channelId)?->number ?? $channelId;
                }
            }
            if (! empty($repeatedChannels)) {
                $errorMessages = '<ul style="text-align: center;">';
                foreach ($repeatedChannels as $channelNumber) {
                    $errorMessages .= '<li>• '.__('The channel ":channel" cannot be selected more than once.', ['channel' => $channelNumber]).'</li>';
                }
                $errorMessages .= '</ul>';
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => __('Error'),
                    'html' => '<b>'.__('Your report log contains errors:').'</b><br><br>'.$errorMessages,
                ]);

                return;
            }

            $report = Report::create([
                'title' => $this->reportData['title'],
                'type' => 'Functions',
                'category' => 'Speed Profiles',
                'duration' => null,
                'reported_by' => Auth::user()->id,
                'area' => Auth::user()->area ?? Report::AREA_OTT,
                'reviewed_by' => $this->reportData['reviewed_by'],
                'status' => 'Reported',
            ]);

            foreach ($this->reportData['channels'] as $channel) {
                $profiles = collect($channel['profiles'])->pluck('value', 'name')->toArray();

                ChannelTest::create([
                    'report_id' => $report->id,
                    'channel_id' => $channel['channel_id'],
                    'user_id' => Auth::user()->id,
                    'high' => $channel['high'] ?? ($profiles['HIGH'] ?? null),
                    'medium' => $channel['medium'] ?? ($profiles['MEDIUM'] ?? null),
                    'low' => $channel['low'] ?? ($profiles['LOW'] ?? null),
                ]);
            }

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => __('Well done!'),
                'text' => __('Video profile test report created successfully.'),
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
                'html' => '<b>'.__('Your report log contains errors:').'</b><br><br>'.$errorMessages,
            ]);
        }
    }

    protected function validateReportData()
    {
        $this->validate([
            'reportData.title' => 'required|string',
            'reportData.channels' => 'required|array|min:1',
        ], [], [
            'reportData.title' => __('title'),
            'reportData.channels' => __('channels'),
        ]);

        foreach ($this->reportData['channels'] as $cIndex => $channel) {
            $this->validate([
                "reportData.channels.$cIndex.channel_id" => 'required|exists:channels,id',
            ], [], [
                "reportData.channels.$cIndex.channel_id" => __('channel'),
            ]);

            foreach ($channel['profiles'] as $pIndex => $profile) {
                $this->validate([
                    "reportData.channels.$cIndex.profiles.$pIndex.name" => 'required|string|max:50',
                    "reportData.channels.$cIndex.profiles.$pIndex.value" => 'nullable|string|max:255',
                ], [], [
                    "reportData.channels.$cIndex.profiles.$pIndex.name" => __('profile name'),
                    "reportData.channels.$cIndex.profiles.$pIndex.value" => __('profile value'),
                ]);
            }
        }
    }

    public function render()
    {
        return view('livewire.app.reports.create.create-profile-report', [
            'channels' => Channel::where('status', '1')
                ->whereNotNull('profiles')
                ->where('profiles', '!=', '[]')
                ->orderBy('number')
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'number' => $c->number,
                    'name' => $c->name,
                    'image' => $c->image,
                    'profiles' => is_string($c->profiles)
                        ? (json_decode($c->profiles, true) ?? ['high' => null, 'medium' => null, 'low' => null])
                        : ($c->profiles ?? ['high' => null, 'medium' => null, 'low' => null]),
                ]),
        ]);
    }
}
