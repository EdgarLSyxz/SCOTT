<?php
namespace App\Livewire\App\Grafana;

use Livewire\Component;
use App\Models\Channel;
use App\Models\GrafanaPanel;
use Carbon\Carbon;

class GrafanaCutv extends Component
{
    public $channels = [];
    public $selectedChannel = null;
    public $mode = 'relative';
    public $preset = '1h';
    public $absoluteFrom = null;
    public $absoluteTo = null;
    public $theme = 'dark';
    public $iframeRefreshKey = 0;
    public $channelPanelIds = [];

    public function mount()
    {
        $grafanaPanel = GrafanaPanel::find(2);
        $apiUrl = $grafanaPanel ? $grafanaPanel->endpoint : null;
        $numbers = [];
        $panelIds = [];
        try {
            $response = @file_get_contents($apiUrl);
            if ($response !== false) {
                $json = json_decode($response, true);
                if (is_array($json)) {
                    foreach ($json as $item) {
                        $num = (string) ($item['number'] ?? '');
                        if ($num !== '') {
                            $numbers[] = $num;
                            $panelIds[$num] = $item['id'] ?? null;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
        }

        $this->channels = Channel::whereIn('number', $numbers)
            ->where('category', 'RESTART/CUTV')
            ->orderByRaw('CAST(number AS UNSIGNED) ASC')
            ->get();
        $this->channelPanelIds = $panelIds;

        if ($this->channels->isNotEmpty()) {
            $default = $this->channels->firstWhere('number', '101') ?? $this->channels->first();
            $this->selectedChannel = $default->id;
        }
    }

    #[On('setTheme')]
    public function setTheme($theme)
    {
        $this->theme = $theme;
        $this->iframeRefreshKey++;
    }

    public function render()
    {
        $grafanaPanel = GrafanaPanel::find(2);

        return view('livewire.app.grafana.grafana-cutv', compact('grafanaPanel'));
    }

    public function getGrafanaUrlProperty()
    {
        $grafanaPanel = $grafanaPanel ?? GrafanaPanel::find(2);
        $base = $grafanaPanel ? $grafanaPanel->url : null;

        [$from, $to] = $this->resolveTimeParams();

        $panelId = 1;
        if ($this->selectedChannel) {
            $channel = $this->channels->firstWhere('id', $this->selectedChannel);
            if ($channel && isset($this->channelPanelIds[$channel->number])) {
                $panelId = $this->channelPanelIds[$channel->number];
            }
        }

        $params = [
            "orgId" => 3,
            "timezone" => "browser",
            "refresh" => "5s",
            "theme" => $this->theme,
            "panelId" => $panelId,
            "from" => $from,
            "to" => $to,
            "__feature.dashboardSceneSolo" => "true",
        ];

        if ($this->selectedChannel) {
            $params["var-canal"] = $this->selectedChannel;
        }

        $params["_k"] = substr(md5(json_encode([$from, $to, $this->selectedChannel, $panelId])), 0, 10);

        return $base . "?" . http_build_query($params);
    }

    public function getIframeKeyProperty()
    {
        return 'grafana-' . substr(md5($this->grafanaUrl), 0, 12) . '-' . $this->iframeRefreshKey;
    }

    public function updatedPreset()
    {
        $this->mode = 'relative';
    }

    protected function resolveTimeParams(): array
    {
        if ($this->mode === 'relative') {
            return ["now-{$this->preset}", "now"];
        }

        $fromMs = $this->toMillis($this->absoluteFrom) ?? now()->subHour()->getTimestampMs();
        $toMs = $this->toMillis($this->absoluteTo) ?? now()->getTimestampMs();

        return [$fromMs, $toMs];
    }

    protected function toMillis(?string $dt): ?int
    {
        if (!$dt)
            return null;
        try {
            return Carbon::parse($dt)->getTimestampMs();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
