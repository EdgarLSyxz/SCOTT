<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transponder_state_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transponder_id')->constrained('transponders')->cascadeOnDelete();
            $table->string('from_site', 30)->nullable();
            $table->string('to_site', 30);
            $table->boolean('from_state_on')->default(false);
            $table->boolean('to_state_on')->default(true);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['transponder_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transponder_state_events');
    }
};
