<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rack_equipment_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rack_id');
            $table->unsignedInteger('position');
            $table->unsignedBigInteger('rack_equipment_id')->nullable();

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

            $table->string('change_type', 20);
            $table->json('changes')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamp('changed_at')->useCurrent();

            $table->index(['rack_id', 'position']);
            $table->index(['rack_id', 'changed_at']);
            $table->index('change_type');
            $table->index('rack_equipment_id');
            $table->index('changed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rack_equipment_history');
    }
};
