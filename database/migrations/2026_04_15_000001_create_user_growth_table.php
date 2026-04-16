<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_growth', function (Blueprint $table) {
            $table->id();
            $table->date('recorded_at')->unique();
            $table->unsignedInteger('customers');
            $table->unsignedInteger('devices');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_growth');
    }
};
