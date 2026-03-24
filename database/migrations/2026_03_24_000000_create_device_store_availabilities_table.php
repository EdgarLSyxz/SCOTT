<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_store_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_available_in_store')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['report_id', 'device_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_store_availabilities');
    }
};
