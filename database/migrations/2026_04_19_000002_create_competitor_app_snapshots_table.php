<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competitor_app_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competitor_app_id')->constrained('competitor_apps')->cascadeOnDelete();
            $table->date('snapshot_date');
            $table->decimal('rating', 3, 2)->default(0);
            $table->string('downloads_label')->nullable();
            $table->string('reviews_label')->nullable();
            $table->date('release_date')->nullable();
            $table->unsignedInteger('rank_position')->nullable();
            $table->integer('rank_delta')->nullable();
            $table->string('movement', 10)->nullable();
            $table->timestamps();

            $table->unique(['competitor_app_id', 'snapshot_date']);
            $table->index(['snapshot_date', 'rank_position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitor_app_snapshots');
    }
};
