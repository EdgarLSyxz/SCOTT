<?php

namespace App\Livewire\App\Reports\Create;

use App\Mail\Reports\ReportFunctionsCreatedMail;
use App\Models\Channel;
use App\Models\Report;
use App\Models\ReportContentLoss;
use App\Models\ReportDetail;
use App\Models\Stage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class CreateFunctionsReport extends Component
{
    public $stages;

    public $categories = [];

    public $protocols = ['HLS', 'DASH', 'HLS/DASH'];

    public $mediaOptions = ['AUDIO', 'VIDEO', 'AUDIO/VIDEO'];

    public $allowedStages = ['CDN BPK', 'CDN TELMEX', 'CDN BPK/TELMEX'];

    public function mount()
    {
        $this->stages = Stage::whereIn('name', $this->allowedStages)
            ->orderBy('name')
            ->pluck('id', 'name')
            ->toArray();
        $this->initializeDefaultCategories();
    }

    protected function initializeDefaultCategories()
    {
        $this->categories = [
            [
                'name' => __('RESTART'),
                'channels' => [],
            ],
            [
                'name' => __('CUTV'),
                'channels' => [],
            ],
            [
                'name' => __('EPG'),
                'channels' => [],
            ],
            [
                'name' => __('PC'),
                'channels' => [],
            ],
        ];
    }

    protected function getChannelsByCategory()
    {
        $allChannels = Channel::where('status', '1')
            ->orderBy('number')
            ->get();

        $channelsByCategory = [];

        foreach ($this->categories as $category) {
            $categoryName = $category['name'];

            if (in_array($categoryName, ['RESTART', 'CUTV'])) {
                $channelsByCategory[$categoryName] = $allChannels->filter(function ($channel) {
                    return $channel->category === 'RESTART/CUTV';
                });
            } else {
                $channelsByCategory[$categoryName] = $allChannels;
            }
        }

        return $channelsByCategory;
    }

    public function addChannel($categoryIndex)
    {
        $this->categories[$categoryIndex]['channels'][] = $this->initializeChannel();
    }

    public function removeChannel($categoryIndex, $channelIndex)
    {
        unset($this->categories[$categoryIndex]['channels'][$channelIndex]);
        $this->categories[$categoryIndex]['channels'] = array_values($this->categories[$categoryIndex]['channels']);
    }

    protected function initializeChannel()
    {
        return [
            'channel_id' => '',
            'number' => '',
            'name' => '',
            'stage' => '',
            'protocol' => '',
            'media' => '',
            'description' => '',
            'loss_periods' => [],
        ];
    }

    public function getPeriodsCount($categoryIndex)
    {
        $category = $this->categories[$categoryIndex] ?? null;

        if (! $category || $category['name'] !== 'CUTV') {
            return 0;
        }

        $totalPeriods = 0;

        foreach ($category['channels'] as $channel) {
            if (! isset($channel['loss_periods']) || ! is_array($channel['loss_periods'])) {
                continue;
            }
            $totalPeriods += count($channel['loss_periods']);
        }

        return $totalPeriods;
    }

    public function addPeriod($categoryIndex, $channelIndex)
    {
        $this->categories[$categoryIndex]['channels'][$channelIndex]['loss_periods'][] = [
            'start_time' => '',
            'end_time' => '',
        ];
    }

    public function removePeriod($categoryIndex, $channelIndex, $periodIndex)
    {
        unset($this->categories[$categoryIndex]['channels'][$channelIndex]['loss_periods'][$periodIndex]);
        $this->categories[$categoryIndex]['channels'][$channelIndex]['loss_periods'] = array_values(
            $this->categories[$categoryIndex]['channels'][$channelIndex]['loss_periods']
        );
    }

    public function saveReport()
    {
        try {
            foreach ($this->categories as $categoryIndex => $category) {
                if (empty($category['channels'])) {
                    continue;
                }

                foreach ($category['channels'] as $channelIndex => $channel) {
                    $rules = [];
                    $messages = [];

                    switch ($category['name']) {
                        case 'RESTART':
                            $rules = [
                                "categories.$categoryIndex.channels.$channelIndex.channel_id" => 'required|exists:channels,id',
                                "categories.$categoryIndex.channels.$channelIndex.stage" => 'required|exists:stages,id',
                                "categories.$categoryIndex.channels.$channelIndex.protocol" => 'required|in:HLS,DASH,HLS/DASH',
                                "categories.$categoryIndex.channels.$channelIndex.media" => 'required|in:AUDIO,VIDEO,AUDIO/VIDEO',
                                "categories.$categoryIndex.channels.$channelIndex.description" => 'required|string',
                            ];
                            break;

                        case 'CUTV':
                            $rules = [
                                "categories.$categoryIndex.channels.$channelIndex.channel_id" => 'required|exists:channels,id',
                                "categories.$categoryIndex.channels.$channelIndex.stage" => 'required|exists:stages,id',
                                "categories.$categoryIndex.channels.$channelIndex.protocol" => 'required|in:HLS,DASH,HLS/DASH',
                                "categories.$categoryIndex.channels.$channelIndex.media" => 'required|in:AUDIO,VIDEO,AUDIO/VIDEO',
                                "categories.$categoryIndex.channels.$channelIndex.loss_periods" => 'required|array|min:1',
                            ];

                            if (! empty($channel['loss_periods'])) {
                                foreach ($channel['loss_periods'] as $periodIndex => $period) {
                                    $rules["categories.$categoryIndex.channels.$channelIndex.loss_periods.$periodIndex.start_time"] = 'required|date|before_or_equal:now';
                                    $rules["categories.$categoryIndex.channels.$channelIndex.loss_periods.$periodIndex.end_time"] = 'required|date|after:categories.'.$categoryIndex.'.channels.'.$channelIndex.'.loss_periods.'.$periodIndex.'.start_time|before_or_equal:now';
                                }
                            }
                            break;

                        case 'EPG':
                            $rules = [
                                "categories.$categoryIndex.channels.$channelIndex.channel_id" => 'required|exists:channels,id',
                                "categories.$categoryIndex.channels.$channelIndex.stage" => 'required|exists:stages,id',
                                "categories.$categoryIndex.channels.$channelIndex.protocol" => 'required|in:HLS,DASH,HLS/DASH',
                                "categories.$categoryIndex.channels.$channelIndex.description" => 'required|string',
                            ];
                            break;

                        case 'PC':
                            $rules = [
                                "categories.$categoryIndex.channels.$channelIndex.channel_id" => 'required|exists:channels,id',
                                "categories.$categoryIndex.channels.$channelIndex.stage" => 'required|exists:stages,id',
                                "categories.$categoryIndex.channels.$channelIndex.protocol" => 'required|in:HLS,DASH,HLS/DASH',
                                "categories.$categoryIndex.channels.$channelIndex.description" => 'required|string',
                            ];
                            break;
                    }

                    $messages = [
                        "categories.$categoryIndex.channels.$channelIndex.channel_id.required" => __('The channel is required.'),
                        "categories.$categoryIndex.channels.$channelIndex.stage.required" => __('The stage is required.'),
                        "categories.$categoryIndex.channels.$channelIndex.protocol.required" => __('The protocol is required.'),
                        "categories.$categoryIndex.channels.$channelIndex.media.required" => __('The media type is required.'),
                        "categories.$categoryIndex.channels.$channelIndex.description.required" => __('The description is required.'),
                        "categories.$categoryIndex.channels.$channelIndex.description.min" => __('The description is required.'),
                    ];

                    if ($category['name'] === 'CUTV') {
                        $messages["categories.$categoryIndex.channels.$channelIndex.loss_periods.required"] = __('Each channel in CUTV must have at least one loss period.');
                        $messages["categories.$categoryIndex.channels.$channelIndex.loss_periods.min"] = __('Each channel in CUTV must have at least one loss period.');
                        foreach ($channel['loss_periods'] as $periodIndex => $period) {
                            $messages["categories.$categoryIndex.channels.$channelIndex.loss_periods.$periodIndex.start_time.required"] = __('The start time is required.');
                            $messages["categories.$categoryIndex.channels.$channelIndex.loss_periods.$periodIndex.end_time.required"] = __('The end time is required.');
                        }
                    }

                    $this->validate($rules, $messages);

                    $channelModel = Channel::find($channel['channel_id']);
                    if ($channelModel) {
                        $this->categories[$categoryIndex]['channels'][$channelIndex]['number'] = $channelModel->number;
                        $this->categories[$categoryIndex]['channels'][$channelIndex]['name'] = $channelModel->name;
                    }
                }
            }

            $userArea = Auth::user()->area ?? null;
            if (empty($userArea)) {
                throw ValidationException::withMessages(['area' => __('Unable to determine user area. Please set your area before creating reports.')]);
            }

            $report = Report::create([
                'type' => 'Functions',
                'category' => implode(', ', array_column($this->categories, 'name')),
                'reported_by' => Auth::user()->id,
                'area' => $userArea,
                'status' => 'Reported',
            ]);

            foreach ($this->categories as $category) {
                if (empty($category['channels'])) {
                    continue;
                }

                foreach ($category['channels'] as $channel) {
                    $reportDetail = ReportDetail::create([
                        'report_id' => $report->id,
                        'subcategory' => $category['name'],
                        'channel_id' => $channel['channel_id'],
                        'protocol' => $channel['protocol'] ?? null,
                        'stage_id' => $channel['stage'],
                        'media' => $channel['media'] ?? null,
                        'description' => $channel['description'] ?? null,
                    ]);

                    if ($category['name'] === 'CUTV' && ! empty($channel['loss_periods'])) {
                        foreach ($channel['loss_periods'] as $period) {
                            ReportContentLoss::create([
                                'report_detail_id' => $reportDetail->id,
                                'start_time' => Carbon::parse($period['start_time']),
                                'end_time' => Carbon::parse($period['end_time']),
                                'duration' => Carbon::parse($period['start_time'])->diffInMinutes(Carbon::parse($period['end_time'])),
                            ]);
                        }
                    }
                }
            }

            $emails = User::whereJsonContains('report_mail_preferences->report_functions_created', true)
                ->pluck('email')
                ->toArray();

            Mail::to($emails)->send(new ReportFunctionsCreatedMail($report, $this->categories));

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => __('Well done!'),
                'text' => __('Function report created successfully.'),
            ]);
        } catch (ValidationException $e) {
            $groupedErrors = [];

            foreach ($e->errors() as $field => $messages) {
                preg_match('/categories\.(\d+)\./', $field, $matches);
                $categoryIndex = $matches[1] ?? null;

                if ($categoryIndex !== null && isset($this->categories[$categoryIndex]['name'])) {
                    $categoryName = $this->categories[$categoryIndex]['name'];
                    if (! isset($groupedErrors[$categoryName])) {
                        $groupedErrors[$categoryName] = [];
                    }

                    foreach ($messages as $message) {
                        if (str_contains($message, 'end time field must be a date after')) {
                            $message = __('The end time must be later than the start time.');
                        } elseif (str_contains($message, 'before_or_equal:now')) {
                            $message = __('The selected time must not be in the future.');
                        } elseif (str_contains($message, 'must be a date')) {
                            $message = __('Please enter a valid date and time.');
                        }

                        $groupedErrors[$categoryName][] = $message;
                    }
                }
            }

            $errorHtml = '<b>'.__('Your report log contains errors:').'</b><br><br>';

            foreach ($groupedErrors as $categoryName => $errors) {
                $errorHtml .= '<b>'.__('Category').': '.e($categoryName).'</b><ul>';
                foreach ($errors as $error) {
                    $errorHtml .= '<li>• '.e($error).'</li>';
                }
                $errorHtml .= '</ul><br>';
            }

            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => __('Error'),
                'html' => $errorHtml,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.app.reports.create.create-functions-report', [
            'channelsByCategory' => $this->getChannelsByCategory(),
        ]);
    }
}
