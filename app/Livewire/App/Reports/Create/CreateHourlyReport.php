<?php

namespace App\Livewire\App\Reports\Create;

use App\Mail\Reports\ReportGeneralCreatedMail;
use App\Models\Channel;
use App\Models\Report;
use App\Models\ReportDetail;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class CreateHourlyReport extends Component
{
    public $stages;

    public $categories = [];

    public $protocols = ['HLS', 'DASH', 'HLS/DASH'];

    public $mediaOptions = ['AUDIO', 'VIDEO', 'AUDIO/VIDEO'];

    protected $fixedCategories = ['CDN TELMEX', 'CDN CEF+', 'STINGRAY'];

    public function mount()
    {
        $this->stages = Stage::where('status', '1')->get();
        $this->initializeDefaultCategories();
    }

    protected function initializeDefaultCategories()
    {
        foreach ($this->fixedCategories as $category) {
            $this->categories[] = [
                'name' => $category,
                'channels' => [],
                'fixed' => true,
            ];
        }
    }

    public function addCategory()
    {
        $this->categories[] = [
            'name' => '',
            'channels' => [
                $this->initializeChannel(),
            ],
            'fixed' => false,
        ];
    }

    public function removeCategory($index)
    {
        if (! isset($this->categories[$index]['fixed']) || ! $this->categories[$index]['fixed']) {
            unset($this->categories[$index]);
            $this->categories = array_values($this->categories);
        }
    }

    public function addChannel($categoryIndex)
    {
        $categoryName = $this->categories[$categoryIndex]['name'];
        $this->categories[$categoryIndex]['channels'][] = $this->initializeChannel($categoryName);
    }

    public function removeChannel($categoryIndex, $channelIndex)
    {
        unset($this->categories[$categoryIndex]['channels'][$channelIndex]);
        $this->categories[$categoryIndex]['channels'] = array_values($this->categories[$categoryIndex]['channels']);
    }

    public function getChannelCount($categoryIndex)
    {
        if (! isset($this->categories[$categoryIndex])) {
            return 0;
        }

        return count($this->categories[$categoryIndex]['channels']);
    }

    protected function initializeChannel($category = '')
    {
        $defaultStage = '';
        if ($category === 'CDN TELMEX') {
            $defaultStage = Stage::where('name', 'CDN TELMEX')->first()->id ?? '';
        } elseif ($category === 'CDN CEF+') {
            $defaultStage = Stage::where('name', 'CDN CEF+')->first()->id ?? '';
        }

        return [
            'channel_id' => '',
            'stage' => $defaultStage,
            'protocol' => '',
            'media' => '',
            'description' => '',
        ];
    }

    public function updateCategoryName($index, $name)
    {
        if (isset($this->categories[$index])) {
            $this->categories[$index]['name'] = $name;
        }
    }

    public function saveReport()
    {
        try {
            $this->validateReportData();

            $categoryNames = implode(', ', array_column($this->categories, 'name'));

            $report = Report::create([
                'type' => 'Hourly',
                'category' => $categoryNames,
                'reported_by' => Auth::user()->id,
                'area' => Auth::user()->area ?? Report::AREA_OTT,
                'status' => 'Reported',
            ]);

            $categories = [];

            foreach ($this->categories as $category) {
                $categoryData = [
                    'name' => $category['name'],
                    'channels' => [],
                ];

                foreach ($category['channels'] as $channel) {
                    if (empty($channel['channel_id'])) {
                        continue;
                    }

                    $reportDetail = ReportDetail::create([
                        'report_id' => $report->id,
                        'subcategory' => $category['name'],
                        'channel_id' => $channel['channel_id'],
                        'protocol' => $channel['protocol'],
                        'stage_id' => $channel['stage'],
                        'media' => $channel['media'],
                        'description' => $channel['description'],
                    ]);

                    $channelData = Channel::find($reportDetail->channel_id);
                    $stageData = Stage::find($reportDetail->stage_id);

                    $categoryData['channels'][] = [
                        'channel_id' => $reportDetail->channel_id,
                        'number' => $channelData->number ?? 'N/A',
                        'name' => $channelData->name ?? 'Unknown',
                        'stage' => $stageData->name ?? 'Unknown',
                        'protocol' => $reportDetail->protocol,
                        'media' => $reportDetail->media,
                    ];
                }

                $categories[] = $categoryData;
            }

            $emails = User::whereJsonContains('report_mail_preferences->report_general_created', true)
                ->pluck('email')
                ->toArray();

            Mail::to($emails)->send(new ReportGeneralCreatedMail($report, $categories));

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => __('Well done!'),
                'text' => __('General report created successfully.'),
            ]);
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
        foreach ($this->categories as $index => $category) {
            $this->validate([
                "categories.$index.name" => 'required|string|max:255',
            ], [], [
                "categories.$index.name" => __('category name'),
            ]);

            if (! in_array($category['name'], $this->fixedCategories) && empty($category['channels'])) {
                throw ValidationException::withMessages([
                    "categories.$index.channels" => __('New categories must have at least one channel.'),
                ]);
            }

            if (! in_array($category['name'], $this->fixedCategories)) {
                foreach ($category['channels'] as $channelIndex => $channel) {
                    if (empty($channel['channel_id']) || empty($channel['stage']) || empty($channel['protocol']) || empty($channel['media']) || empty($channel['description'])) {
                        throw ValidationException::withMessages([
                            "categories.$index.channels.$channelIndex" => __('All fields are required for each channel in new categories.'),
                        ]);
                    }

                    $this->validate([
                        "categories.$index.channels.$channelIndex.channel_id" => 'required|exists:channels,id',
                        "categories.$index.channels.$channelIndex.stage" => 'required|exists:stages,id',
                        "categories.$index.channels.$channelIndex.protocol" => 'required|in:'.implode(',', $this->protocols),
                        "categories.$index.channels.$channelIndex.media" => 'required|in:'.implode(',', $this->mediaOptions),
                        "categories.$index.channels.$channelIndex.description" => 'required|string',
                    ], [], [
                        "categories.$index.channels.$channelIndex.channel_id" => __('channel'),
                        "categories.$index.channels.$channelIndex.stage" => __('stage'),
                        "categories.$index.channels.$channelIndex.protocol" => __('protocol'),
                        "categories.$index.channels.$channelIndex.media" => __('media'),
                        "categories.$index.channels.$channelIndex.description" => __('description'),
                    ]);
                }
            }
        }
    }

    public function render()
    {
        foreach ($this->categories as $index => $category) {
            if (! isset($this->categories[$index]['fixed'])) {
                $this->categories[$index]['fixed'] = in_array($category['name'], ['CDN TELMEX', 'CDN CEF+', 'STINGRAY']);
            }
        }

        return view('livewire.app.reports.create.create-hourly-report', [
            'channels' => Channel::where('status', '1')
                ->orderBy('number')
                ->get(),

            'stingrayChannels' => Channel::where('status', '1')
                ->where('category', 'Stingray Music')
                ->orderBy('number')
                ->get(),
        ]);
    }
}
