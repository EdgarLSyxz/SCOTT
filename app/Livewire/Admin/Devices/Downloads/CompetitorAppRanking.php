<?php

namespace App\Livewire\Admin\Devices\Downloads;

use App\Models\CompetitorApp;
use App\Models\CompetitorAppSnapshot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CompetitorAppRanking extends Component
{
    public string $snapshotDate;

    public array $rows = [];

    public bool $showFormModal = false;

    public ?int $traceAppId = null;

    public function mount(): void
    {
        $allowedIds = [1, 2, 3, 5, 7, 8];
        if (! (Auth::id() && in_array((int) Auth::id(), $allowedIds, true))) {
            abort(403);
        }

        $latest = CompetitorAppSnapshot::max('snapshot_date');
        $this->snapshotDate = $latest ? \Carbon\Carbon::parse($latest)->toDateString() : now()->toDateString();
        $this->loadRowsForDate();

        $primaryId = CompetitorApp::where('is_primary', true)->value('id');
        $this->traceAppId = $primaryId ?: CompetitorApp::orderBy('name')->value('id');
    }

    public function updatedSnapshotDate(): void
    {
        $this->loadRowsForDate();
    }

    public function addRow(): void
    {
        if (! $this->showFormModal) {
            $this->showFormModal = true;
        }

        $this->rows[] = $this->makeEmptyRow();
    }

    public function openFormModal(): void
    {
        $this->loadRowsForDate();

        if (count($this->rows) === 0) {
            $this->rows[] = $this->makeEmptyRow();
        }

        $this->showFormModal = true;
    }

    public function closeFormModal(): void
    {
        $this->showFormModal = false;
        $this->loadRowsForDate();
    }

    public function removeDraftRow(int $index): void
    {
        if (! isset($this->rows[$index])) {
            return;
        }

        if (! empty($this->rows[$index]['competitor_app_id'])) {
            return;
        }

        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
    }

    public function deleteApp(int $index): void
    {
        if (! isset($this->rows[$index])) {
            return;
        }

        $appId = (int) ($this->rows[$index]['competitor_app_id'] ?? 0);

        if ($appId) {
            DB::transaction(function () use ($appId) {
                CompetitorAppSnapshot::where('competitor_app_id', $appId)->delete();
                CompetitorApp::where('id', $appId)->delete();
            });

            if ($this->traceAppId === $appId) {
                $this->traceAppId = CompetitorApp::where('is_active', true)->orderBy('name')->value('id');
            }
        }

        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => __('App deleted'),
            'text' => __('The competitor app and its history were deleted.'),
        ]);
    }

    public function saveRows(): void
    {
        $this->validate([
            'snapshotDate' => 'required|date',
            'rows' => 'required|array|min:1',
        ]);

        $snapshotBatchAt = now()->format('Y-m-d H:i:s');

        DB::transaction(function () use ($snapshotBatchAt) {
            foreach ($this->rows as $index => $row) {
                $this->validate([
                    "rows.$index.name" => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('competitor_apps', 'name')->ignore((int) ($row['competitor_app_id'] ?? 0)),
                    ],
                    "rows.$index.rating" => 'nullable|numeric|min:0|max:5',
                    "rows.$index.downloads_label" => 'nullable|string|max:100',
                    "rows.$index.reviews_label" => 'nullable|string|max:100',
                    "rows.$index.release_date" => 'nullable|date',
                    "rows.$index.store_url" => 'nullable|url|max:1000',
                ]);

                $app = null;
                if (! empty($row['competitor_app_id'])) {
                    $app = CompetitorApp::find((int) $row['competitor_app_id']);
                }

                if ($app) {
                    $app->update([
                        'name' => trim((string) $row['name']),
                        'store_url' => ! empty($row['store_url']) ? trim((string) $row['store_url']) : null,
                    ]);
                } else {
                    $app = CompetitorApp::create([
                        'name' => trim((string) $row['name']),
                        'store_url' => ! empty($row['store_url']) ? trim((string) $row['store_url']) : null,
                        'is_primary' => false,
                        'is_active' => true,
                    ]);
                }

                if (! $this->traceAppId) {
                    $this->traceAppId = $app->id;
                }

                $hasSnapshotData = $row['rating'] !== null
                    && $row['rating'] !== '';

                $hasSnapshotData = $hasSnapshotData
                    || ! empty($row['downloads_label'])
                    || ! empty($row['reviews_label'])
                    || ! empty($row['release_date']);

                if ($hasSnapshotData) {
                    CompetitorAppSnapshot::create([
                        'competitor_app_id' => $app->id,
                        'snapshot_date' => $this->snapshotDate,
                        'snapshot_batch_at' => $snapshotBatchAt,
                        'rating' => $row['rating'] !== null && $row['rating'] !== '' ? (float) $row['rating'] : 0,
                        'downloads_label' => ! empty($row['downloads_label']) ? trim((string) $row['downloads_label']) : null,
                        'reviews_label' => ! empty($row['reviews_label']) ? trim((string) $row['reviews_label']) : null,
                        'release_date' => $row['release_date'] ?: null,
                    ]);
                }
            }
        });

        $this->recalculateRanking();
        $this->loadRowsForDate();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => __('Data saved'),
            'text' => __('Competitor app data was saved successfully.'),
        ]);

        $this->dispatch('competitor-ranking-saved');
    }

    public function recalculateRanking(): void
    {
        $this->validate([
            'snapshotDate' => 'required|date',
        ]);

        $latestBatchAt = $this->getLatestBatchTimestampForDate($this->snapshotDate);
        $hasSnapshots = ! empty($latestBatchAt);

        if (! $hasSnapshots) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => __('No data to rank'),
                'text' => __('Save at least one app with rating data before recalculating the ranking.'),
            ]);

            return;
        }

        $this->recalculateRankingForBatch($this->snapshotDate, $latestBatchAt);
        $this->loadRowsForDate();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => __('Ranking recalculated'),
            'text' => __('The competitor ranking was recalculated successfully.'),
        ]);
    }

    private function recalculateRankingForBatch(string $date, string $snapshotBatchAt): void
    {
        $snapshots = CompetitorAppSnapshot::with('app')
            ->whereDate('snapshot_date', $date)
            ->where('snapshot_batch_at', $snapshotBatchAt)
            ->orderByRaw('rating DESC, (SELECT name FROM competitor_apps WHERE id = competitor_app_snapshots.competitor_app_id) ASC')
            ->get();

        $rank = 1;

        foreach ($snapshots as $snapshot) {
            /** @var CompetitorAppSnapshot $snapshot */
            $previous = CompetitorAppSnapshot::where('competitor_app_id', $snapshot->competitor_app_id)
                ->where('snapshot_batch_at', '<', $snapshotBatchAt)
                ->orderByDesc('snapshot_batch_at')
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
        $latestBatchAt = $this->getLatestBatchTimestampForDate($this->snapshotDate);

        $apps = CompetitorApp::where('is_active', true)
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->get();

        $this->rows = $apps->map(function (CompetitorApp $app) use ($latestBatchAt) {
            $snapshot = $app->snapshots()
                ->whereDate('snapshot_date', $this->snapshotDate)
                ->when($latestBatchAt, function ($query) use ($latestBatchAt) {
                    $query->where('snapshot_batch_at', $latestBatchAt);
                })
                ->first();

            return [
                'competitor_app_id' => $app->id,
                'name' => $app->name,
                'is_primary' => (bool) $app->is_primary,
                'store_url' => $app->store_url ?? '',
                'rating' => $snapshot && $snapshot->rating !== null
                    ? number_format((float) $snapshot->rating, 1, '.', '')
                    : null,
                'downloads_label' => $snapshot?->downloads_label ?? '',
                'reviews_label' => $snapshot?->reviews_label ?? '',
                'release_date' => $snapshot?->release_date?->toDateString() ?? '',
                'rank_position' => $snapshot?->rank_position,
                'rank_delta' => $snapshot?->rank_delta,
                'movement' => $snapshot?->movement,
            ];
        })->toArray();
    }

    private function getLatestBatchTimestampForDate(string $date): ?string
    {
        return CompetitorAppSnapshot::whereDate('snapshot_date', $date)->max('snapshot_batch_at');
    }

    private function makeEmptyRow(): array
    {
        return [
            'competitor_app_id' => null,
            'name' => '',
            'is_primary' => false,
            'store_url' => '',
            'rating' => null,
            'downloads_label' => '',
            'reviews_label' => '',
            'release_date' => '',
            'rank_position' => null,
            'rank_delta' => null,
            'movement' => null,
        ];
    }

    public function render()
    {
        $latestBatchAt = $this->getLatestBatchTimestampForDate($this->snapshotDate);

        $currentSnapshot = CompetitorAppSnapshot::with('app')
            ->whereDate('snapshot_date', $this->snapshotDate)
            ->when($latestBatchAt, function ($query) use ($latestBatchAt) {
                $query->where('snapshot_batch_at', $latestBatchAt);
            }, function ($query) {
                $query->whereRaw('1 = 0');
            })
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
            $latestBatchAt = CompetitorAppSnapshot::whereDate('snapshot_date', $date)->max('snapshot_batch_at');
            if (! $latestBatchAt) {
                return [];
            }

            $snapshots = CompetitorAppSnapshot::with('app')
                ->whereDate('snapshot_date', $date)
                ->where('snapshot_batch_at', $latestBatchAt)
                ->orderBy('rank_position')
                ->take(10)
                ->get()
                ->map(function (CompetitorAppSnapshot $snapshot) {
                    return [
                        'rank' => $snapshot->rank_position ?? '—',
                        'app' => $snapshot->app?->name ?? 'Unknown',
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
