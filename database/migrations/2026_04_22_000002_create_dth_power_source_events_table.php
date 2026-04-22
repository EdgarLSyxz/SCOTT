<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dth_power_source_events', function (Blueprint $table) {
            $table->id();
            $table->string('power_source', 30);
            $table->text('notes')->nullable();
            $table->timestamp('changed_at');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index('changed_at');
            $table->index('power_source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dth_power_source_events');
    }
};
