<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transponders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('description')->nullable();
            $table->string('active_site', 30)->default('Zacatecas');
            $table->boolean('is_on')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('active_site');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transponders');
    }
};
