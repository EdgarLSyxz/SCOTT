<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('competitor_app_snapshots', 'snapshot_batch_at')) {
            Schema::table('competitor_app_snapshots', function (Blueprint $table) {
                $table->dateTime('snapshot_batch_at')->nullable()->after('snapshot_date');
            });
        }

        DB::statement("UPDATE competitor_app_snapshots SET snapshot_batch_at = COALESCE(created_at, CONCAT(snapshot_date, ' 00:00:00')) WHERE snapshot_batch_at IS NULL");

        if (! $this->indexExists('competitor_app_snapshots', 'competitor_app_snapshots_competitor_app_id_index')) {
            Schema::table('competitor_app_snapshots', function (Blueprint $table) {
                $table->index('competitor_app_id', 'competitor_app_snapshots_competitor_app_id_index');
            });
        }

        if ($this->indexExists('competitor_app_snapshots', 'competitor_app_snapshots_competitor_app_id_snapshot_date_unique')) {
            Schema::table('competitor_app_snapshots', function (Blueprint $table) {
                $table->dropUnique('competitor_app_snapshots_competitor_app_id_snapshot_date_unique');
            });
        }

        if (! $this->indexExists('competitor_app_snapshots', 'competitor_app_snapshots_app_date_batch_unique')) {
            Schema::table('competitor_app_snapshots', function (Blueprint $table) {
                $table->unique(
                    ['competitor_app_id', 'snapshot_date', 'snapshot_batch_at'],
                    'competitor_app_snapshots_app_date_batch_unique'
                );
            });
        }

        if (! $this->indexExists('competitor_app_snapshots', 'competitor_app_snapshots_date_batch_rank_idx')) {
            Schema::table('competitor_app_snapshots', function (Blueprint $table) {
                $table->index(['snapshot_date', 'snapshot_batch_at', 'rank_position'], 'competitor_app_snapshots_date_batch_rank_idx');
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('competitor_app_snapshots', 'competitor_app_snapshots_app_date_batch_unique')) {
            Schema::table('competitor_app_snapshots', function (Blueprint $table) {
                $table->dropUnique('competitor_app_snapshots_app_date_batch_unique');
            });
        }

        if ($this->indexExists('competitor_app_snapshots', 'competitor_app_snapshots_date_batch_rank_idx')) {
            Schema::table('competitor_app_snapshots', function (Blueprint $table) {
                $table->dropIndex('competitor_app_snapshots_date_batch_rank_idx');
            });
        }

        if (! $this->indexExists('competitor_app_snapshots', 'competitor_app_snapshots_competitor_app_id_snapshot_date_unique')) {
            Schema::table('competitor_app_snapshots', function (Blueprint $table) {
                $table->unique(['competitor_app_id', 'snapshot_date']);
            });
        }

        if (Schema::hasColumn('competitor_app_snapshots', 'snapshot_batch_at')) {
            Schema::table('competitor_app_snapshots', function (Blueprint $table) {
                $table->dropColumn('snapshot_batch_at');
            });
        }

        if ($this->indexExists('competitor_app_snapshots', 'competitor_app_snapshots_competitor_app_id_index')) {
            Schema::table('competitor_app_snapshots', function (Blueprint $table) {
                $table->dropIndex('competitor_app_snapshots_competitor_app_id_index');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }
};
