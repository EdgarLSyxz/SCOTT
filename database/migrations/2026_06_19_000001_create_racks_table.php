<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('racks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->unsignedInteger('total_units')->default(42);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('is_active');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('racks');
    }
};
