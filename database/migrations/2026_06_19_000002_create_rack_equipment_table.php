<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rack_equipment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rack_id');
            $table->unsignedInteger('position');
            $table->string('equipment_name')->nullable();
            $table->string('equipment_model')->nullable();
            $table->string('equipment_role')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('mac_address')->nullable();
            $table->string('vendor')->nullable();
            $table->date('installation_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('color', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['rack_id', 'position']);
            $table->index(['rack_id', 'is_active']);
            $table->index('updated_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rack_equipment');
    }
};
