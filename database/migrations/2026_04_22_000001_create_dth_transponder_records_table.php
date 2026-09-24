<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dth_transponder_records', function (Blueprint $table) {
            $table->id();
            $table->string('up_link_site', 50);
            $table->string('transponders', 255);
            $table->text('description');
            $table->timestamp('recorded_at');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index('recorded_at');
            $table->index('up_link_site');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dth_transponder_records');
    }
};
