<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('report_sla_settings', function (Blueprint $table) {
            $table->id();
            $table->string('area', 10)->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('level_1_minutes')->default(30);
            $table->unsignedInteger('level_2_minutes')->default(90);
            $table->unsignedInteger('level_3_minutes')->default(180);
            $table->timestamps();
        });

        DB::table('report_sla_settings')->insert([
            [
                'area' => 'OTT',
                'is_active' => true,
                'level_1_minutes' => 30,
                'level_2_minutes' => 90,
                'level_3_minutes' => 180,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'area' => 'DTH',
                'is_active' => true,
                'level_1_minutes' => 30,
                'level_2_minutes' => 90,
                'level_3_minutes' => 180,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_sla_settings');
    }
};
