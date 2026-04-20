<?php

namespace App\Livewire\Admin\Devices\Downloads;

use App\Models\CompetitorApp;
use App\Models\CompetitorAppSnapshot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CompetitorAppRanking extends Component
{
    public string $snapshotDate;
    public array $rows = [];

    public string $newAppName = '';
    public string $newAppStoreUrl = '';

    public ?int $traceAppId = null;

    public function mount(): void
    {
        $allowedIds = [1, 2, 3, 5, 7, 8];
        if (! (Auth::id() && in_array((int) Auth::id(), $allowedIds, true))) {
            abort(403);
        }

        $this->snapshotDate = now()->toDateString();
        $this->loadRowsForDate();

        $primaryId = CompetitorApp::where('is_primary', true)->value('id');
        $this->traceAppId = $primaryId ?: CompetitorApp::orderBy('name')->value('id');
    }

    public function updatedSnapshotDate(): void
    {
        $this->loadRowsForDate();
    }

    public function addApp(): void
    {
        $this->validate([
            'newAppName' => 'required|string|max:255|unique:competitor_apps,name',
            'newAppStoreUrl' => 'nullable|url|max:1000',
        ]);

        $app = CompetitorApp::create([
            'name' => trim($this->newAppName),
            'store_url' => trim($this->newAppStoreUrl) ?: null,
            'is_primary' => false,
            'is_active' => true,
        ]);

        $this->newAppName = '';
        $this->newAppStoreUrl = '';

        if (! $this->traceAppId) {
            $this->traceAppId = $app->id;
        }

        $this->loadRowsForDate();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Aplicación agregada',
            'text' => 'La app competidora se dio de alta correctamente.',
        ]);
    }

    public function saveSnapshot(): void
    {
        $this->validate([
            'snapshotDate' => 'required|date',
            'rows' => 'required|array|min:1',
        ]);

        DB::transaction(function () {
            foreach ($this->rows as $index => $row) {
                $this->validate([
                    "rows.$index.competitor_app_id" => 'required|exists:competitor_apps,id',
                    "rows.$index.rating" => 'required|numeric|min:0|max:5',
                    "rows.$index.downloads_label" => 'nullable|string|max:100',
                    "rows.$index.reviews_label" => 'nullable|string|max:100',
                    "rows.$index.release_date" => 'nullable|date',
                    "rows.$index.store_url" => 'nullable|url|max:1000',
                ]);

                CompetitorApp::whereKey((int) $row['competitor_app_id'])->update([
                    'store_url' => ! empty($row['store_url']) ? trim((string) $row['store_url']) : null,
                ]);

                CompetitorAppSnapshot::updateOrCreate(
                    [
                        'competitor_app_id' => (int) $row['competitor_app_id'],
                        'snapshot_date' => $this->snapshotDate,
                    ],
                    [
                        'rating' => (float) $row['rating'],
                        'downloads_label' => $row['downloads_label'] ?? null,
                        'reviews_label' => $row['reviews_label'] ?? null,
                        'release_date' => $row['release_date'] ?: null,
                    ]
                );
            }

            $this->recalculateRankingForDate($this->snapshotDate);
        });

        $this->loadRowsForDate();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Actualizacion guardada',
            'text' => 'Se guardo el snapshot y se recalculo el ranking.',
        ]);
    }

    private function recalculateRankingForDate(string $date): void
    {
        $snapshots = CompetitorAppSnapshot::with('app')
            ->whereDate('snapshot_date', $date)
            ->orderByRaw('rating DESC, (SELECT name FROM competitor_apps WHERE id = competitor_app_snapshots.competitor_app_id) ASC')
            ->get();

        $rank = 1;

        foreach ($snapshots as $snapshot) {
            /** @var CompetitorAppSnapshot $snapshot */
            $previous = CompetitorAppSnapshot::where('competitor_app_id', $snapshot->competitor_app_id)
                ->whereDate('snapshot_date', '<', $date)
                ->orderByDesc('snapshot_date')
                ->first();

            $previousRank = $previous?->rank_position;
            $delta = $previousRank === null ? null : ($previousRank - $rank);

            $movement = 'new';
            if ($previousRank !== null) {
                if ($delta > 0) {
                    $movement = 'up';
                } elseif ($delta < 0) {
                    $movement = 'down';
                } else {
                    $movement = 'same';
                }
            }

            $snapshot->update([
                'rank_position' => $rank,
                'rank_delta' => $delta,
                'movement' => $movement,
            ]);

            $rank++;
        }
    }

    private function loadRowsForDate(): void
    {
        $apps = CompetitorApp::where('is_active', true)
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->get();

        $this->rows = $apps->map(function (CompetitorApp $app) {
            $snapshot = $app->snapshots()->whereDate('snapshot_date', $this->snapshotDate)->first();

            return [
                'competitor_app_id' => $app->id,
                'name' => $app->name,
                'is_primary' => (bool) $app->is_primary,
                'store_url' => $app->store_url ?? '',
                'rating' => $snapshot?->rating ?? null,
                'downloads_label' => $snapshot?->downloads_label ?? '',
                'reviews_label' => $snapshot?->reviews_label ?? '',
                'release_date' => $snapshot?->release_date?->toDateString() ?? '',
            ];
        })->toArray();
    }

    public function render()
    {
        $currentSnapshot = CompetitorAppSnapshot::with('app')
            ->whereDate('snapshot_date', $this->snapshotDate)
            ->orderBy('rank_position')
            ->get();

        $topTen = $currentSnapshot->take(10);
        $primaryAppRow = $currentSnapshot->first(fn ($item) => (bool) optional($item->app)->is_primary);

        $traceApps = CompetitorApp::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        $traceHistory = collect();
        if ($this->traceAppId) {
            $traceHistory = CompetitorAppSnapshot::with('app')
                ->where('competitor_app_id', $this->traceAppId)
                ->orderByDesc('snapshot_date')
                ->limit(20)
                ->get();
        }

        return view('livewire.admin.devices.downloads.competitor-app-ranking', [
            'rows' => collect($this->rows),
            'primaryAppRow' => $primaryAppRow,
            'topTen' => $topTen,
            'traceApps' => $traceApps,
            'traceHistory' => $traceHistory,
            'hasSnapshot' => $currentSnapshot->isNotEmpty(),
        ]);
    }

    public static function getCompetitorRankingForDate(string $date): array
    {
        try {
            $snapshots = CompetitorAppSnapshot::with('app')
                ->whereDate('snapshot_date', $date)
                ->orderBy('rank_position')
                ->take(10)
                ->get()
                ->map(function (CompetitorAppSnapshot $snapshot) {
                    return [
                        'rank' => $snapshot->rank_position ?? '—',
                        'app' => $snapshot->app?->name ?? 'Desconocido',
                        'rating' => number_format((float) $snapshot->rating, 1),
                        'downloads' => $snapshot->downloads_label ?? '—',
                        'reviews' => $snapshot->reviews_label ?? '—',
                        'movement' => $snapshot->movement ?? 'new',
                        'movement_delta' => $snapshot->rank_delta ?? 0,
                        'is_primary' => (bool) optional($snapshot->app)->is_primary,
                    ];
                })
                ->toArray();

            return $snapshots;
        } catch (\Exception) {
            return [];
        }
    }
}
