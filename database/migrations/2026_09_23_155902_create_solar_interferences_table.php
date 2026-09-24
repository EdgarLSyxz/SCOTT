<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solar_interferences', function (Blueprint $table) {
            $table->id();
            $table->string('document_name');
            $table->string('section', 50);
            $table->string('region_name', 191);
            $table->date('event_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->json('channels')->nullable();
            $table->unsignedSmallInteger('affected_channels_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'event_date']);
            $table->index(['section', 'region_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solar_interferences');
    }
};
