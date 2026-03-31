<?php

namespace App\Livewire\App\Grafana;

use App\Models\GrafanaPanel;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class GrafanaRadios extends Component
{
    public ?int $dashboardId = null;
    public string $mode = 'relative';
    public string $preset = '1h';
    public ?string $absoluteFrom = null;
    public ?string $absoluteTo = null;
    public string $theme = 'dark';
    public int $iframeRefreshKey = 0;

    public function mount(?int $dashboardId = null): void
    {
        $this->dashboardId = $dashboardId;
    }

    #[On('setTheme')]
    public function setTheme(string $theme): void
    {
        $this->theme = $theme;
        $this->iframeRefreshKey++;
    }

    public function render()
    {
        $grafanaPanel = $this->resolvePanel();

        return view('livewire.app.grafana.grafana-radios', compact('grafanaPanel'));
    }

    public function getGrafanaUrlProperty(): string
    {
        [$from, $to] = $this->resolveTimeParams();

        $urlWithoutQuery = 'http://172.16.126.169:3000/d-solo/adjqd2b/radio-stations';

        $params = [
            'orgId'    => 3,
            'from'     => $from,
            'to'       => $to,
            'timezone' => 'browser',
            'refresh'  => '5s',
            'panelId'  => 'panel-1',
            'theme'    => $this->theme,
            '__feature.dashboardSceneSolo' => 'true',
        ];

        $params['_k'] = substr(md5(json_encode([$from, $to, $this->theme])), 0, 10);

        return $urlWithoutQuery . '?' . http_build_query($params);
    }

    public function getIframeKeyProperty(): string
    {
        return 'grafana-radios-' . substr(md5($this->grafanaUrl), 0, 12) . '-' . $this->iframeRefreshKey;
    }

    public function updatedPreset(): void
    {
        $this->mode = 'relative';
    }

    protected function resolveTimeParams(): array
    {
        if ($this->mode === 'relative') {
            return ["now-{$this->preset}", 'now'];
        }

        $fromMs = $this->toMillis($this->absoluteFrom) ?? now()->subHour()->getTimestampMs();
        $toMs = $this->toMillis($this->absoluteTo) ?? now()->getTimestampMs();

        return [$fromMs, $toMs];
    }

    protected function toMillis(?string $dt): ?int
    {
        if (!$dt) {
            return null;
        }

        try {
            return Carbon::parse($dt)->getTimestampMs();
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function resolvePanel(): ?GrafanaPanel
    {
        if ($this->dashboardId) {
            return GrafanaPanel::find($this->dashboardId);
        }

        $userArea = auth()->user()?->area;

        $query = GrafanaPanel::query();
        if (in_array($userArea, ['OTT', 'DTH'], true)) {
            $query->where('area', $userArea);
        }

        return $query
            ->whereRaw('LOWER(name) LIKE ?', ['%radio%'])
            ->orderBy('id')
            ->first();
    }

}
