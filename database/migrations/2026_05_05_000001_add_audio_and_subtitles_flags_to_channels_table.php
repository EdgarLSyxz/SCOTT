<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->boolean('audio_spanish_enabled')->nullable()->after('category');
            $table->boolean('audio_english_enabled')->nullable()->after('audio_spanish_enabled');
            $table->boolean('subtitles_enabled')->nullable()->after('audio_english_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->dropColumn([
                'audio_spanish_enabled',
                'audio_english_enabled',
                'subtitles_enabled',
            ]);
        });
    }
};
