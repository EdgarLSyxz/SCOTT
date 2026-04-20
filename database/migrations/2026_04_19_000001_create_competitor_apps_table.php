<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competitor_apps', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('store_url')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('competitor_apps')->insert([
            'name' => 'STARTV STREAM',
            'store_url' => null,
            'is_primary' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('competitor_apps');
    }
};
