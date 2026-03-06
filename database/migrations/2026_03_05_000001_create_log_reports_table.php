<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date');
            $table->json('data')->comment('JSON structure with all report categories');
            $table->timestamps();
            $table->index('report_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_reports');
    }
};
