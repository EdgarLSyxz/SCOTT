<?php

namespace App\Livewire\App\Logs;

use Livewire\Component;
use App\Models\Issue;
use App\Models\Channel;

class LatestLogs extends Component
{
    public $logs = [];
    public $latestIssueId = 0;

    public function mount()
    {
        $this->fetchLogs();
    }

    public function fetchLogs()
    {
        $userArea = auth()->user()?->area;
        $issues = Issue::orderByDesc('created_at')->limit(28)->get()->reverse();
        $this->latestIssueId = Issue::max('id') ?? 0;
        $channelNumbers = collect($issues)->map(function ($issue) {
            $originalChannel = $issue->channel ?? '';
            if (is_string($originalChannel) && preg_match('/^(\d+)/', $originalChannel, $matches)) {
                return $matches[1];
            }
            return null;
        })->filter()->unique()->values()->all();

        $channels = Channel::whereIn('number', $channelNumbers)->get()->keyBy('number');

        $this->logs = $issues->map(function ($issue) use ($channels) {
            $date = $issue->created_at ? $issue->created_at->format('d/m/Y H:i:s') : '';
            $channelNumber = null;
            $originalChannel = $issue->channel ?? '';
            $channelName = $originalChannel;
            if (is_string($originalChannel) && preg_match('/^(\d+)/', $originalChannel, $matches)) {
                $channelNumber = $matches[1];
            }
            $channelImage = null;
            if ($channelNumber && $channels->has($channelNumber)) {
                $channel = $channels->get($channelNumber);
                if (!empty($channel->image)) {
                    $channelImage = $channel->image;
                }
                if (!empty($channel->name)) {
                    $channelName = $channel->name;
                }
            }

            return [
                'date' => $date,
                'channel' => $channelName ?? '',
                'channel_number' => $channelNumber,
                'type' => $issue->issueType ?? '',
                'description' => $issue->issueDescription ?? '',
                'tag' => $issue->tag ?? '',
                'channel_image' => $channelImage,
            ];
        })->filter(function ($log) use ($userArea) {
            if (strtolower($userArea ?? '') === 'dth') {
                $type = strtoupper($log['type'] ?? '');
                if (in_array($type, ['CUTV_EU', 'CUTV_OR'], true)) {
                    return false;
                }
            }
            return true;
        })->values()->toArray();
    }

    public function render()
    {
        return view('livewire.app.logs.latest-logs', [
            'logs' => $this->logs,
            'latestIssueId' => $this->latestIssueId,
        ]);
    }
}
