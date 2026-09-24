<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grafana_panels', function (Blueprint $table) {
            $table->string('area')->nullable()->after('name');
        });
        DB::table('grafana_panels')->whereNull('area')->update(['area' => 'OTT']);
    }

    public function down(): void
    {
        Schema::table('grafana_panels', function (Blueprint $table) {
            $table->dropColumn('area');
        });
    }
};
