<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_rain_events', function (Blueprint $table) {
            $table->id();
            $table->string('site', 30);
            $table->timestamp('rain_started_at');
            $table->timestamp('rain_ended_at')->nullable();
            $table->decimal('peak_precipitation_mm', 8, 2)->nullable();
            $table->decimal('total_precipitation_mm', 8, 2)->nullable();
            $table->decimal('precipitation_hours', 6, 2)->nullable();
            $table->unsignedTinyInteger('max_probability')->nullable();
            $table->string('intensity', 20)->nullable();
            $table->string('condition_label', 80)->nullable();
            $table->decimal('temperature_c', 5, 2)->nullable();
            $table->decimal('humidity', 5, 2)->nullable();
            $table->decimal('wind_kmh', 5, 2)->nullable();
            $table->string('notification_started_status', 20)->default('pending');
            $table->string('notification_ended_status', 20)->default('pending');
            $table->timestamp('notified_started_at')->nullable();
            $table->timestamp('notified_ended_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->index(['site', 'rain_started_at']);
            $table->index(['site', 'rain_ended_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_rain_events');
    }
};