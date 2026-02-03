<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('downloads', function (Blueprint $table) {
            $table->unsignedTinyInteger('day')->default(1)->after('month');
            $table->dropIndex(['year', 'month']);
            $table->index(['year', 'month', 'day']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('downloads', function (Blueprint $table) {
            $table->dropIndex(['year', 'month', 'day']);
            $table->index(['year', 'month']);
            $table->dropColumn('day');
        });
    }
};
