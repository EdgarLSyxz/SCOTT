<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rack_equipment', function (Blueprint $table) {
            $table->unsignedSmallInteger('size_u')
                ->default(1)
                ->after('position');

            $table->index(['rack_id', 'position', 'size_u']);
        });

        \DB::statement('UPDATE rack_equipment SET size_u = 1 WHERE size_u IS NULL OR size_u < 1');
    }

    public function down(): void
    {
        Schema::table('rack_equipment', function (Blueprint $table) {
            $table->dropIndex(['rack_id', 'position', 'size_u']);
            $table->dropColumn('size_u');
        });
    }
};
