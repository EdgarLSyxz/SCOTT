<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rack_ip_ranges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rack_id');
            $table->string('cidr_range', 64);
            $table->string('mask', 32);
            $table->unsignedSmallInteger('vlan')->nullable();
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('rack_id')
                ->references('id')
                ->on('racks')
                ->onDelete('cascade');

            $table->index(['rack_id', 'vlan']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rack_ip_ranges');
    }
};
