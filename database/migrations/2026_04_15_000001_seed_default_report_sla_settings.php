<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        DB::table('report_sla_settings')->updateOrInsert(
            ['area' => 'OTT'],
            [
                'is_active' => true,
                'level_1_minutes' => 30,
                'level_2_minutes' => 90,
                'level_3_minutes' => 180,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('report_sla_settings')->updateOrInsert(
            ['area' => 'DTH'],
            [
                'is_active' => true,
                'level_1_minutes' => 30,
                'level_2_minutes' => 90,
                'level_3_minutes' => 180,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('report_sla_settings')
            ->whereIn('area', ['OTT', 'DTH'])
            ->delete();
    }
};
