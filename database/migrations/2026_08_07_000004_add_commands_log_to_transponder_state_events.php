<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transponder_state_events', function (Blueprint $table) {
            $table->text('commands_log')->nullable()->after('previous_duration_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('transponder_state_events', function (Blueprint $table) {
            $table->dropColumn('commands_log');
        });
    }
};
