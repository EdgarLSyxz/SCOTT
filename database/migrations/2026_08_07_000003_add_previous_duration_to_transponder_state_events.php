<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transponder_state_events', function (Blueprint $table) {
            $table->unsignedInteger('previous_duration_seconds')->nullable()->after('to_state_on');
        });
    }

    public function down(): void
    {
        Schema::table('transponder_state_events', function (Blueprint $table) {
            $table->dropColumn('previous_duration_seconds');
        });
    }
};
