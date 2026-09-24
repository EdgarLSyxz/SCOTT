<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('video_profile_tests')) {
            return;
        }

        if (! Schema::hasColumn('video_profile_tests', 'profile_data')) {
            Schema::table('video_profile_tests', function (Blueprint $table) {
                $table->json('profile_data')->nullable()->after('low');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('video_profile_tests')) {
            return;
        }

        Schema::table('video_profile_tests', function (Blueprint $table) {
            if (Schema::hasColumn('video_profile_tests', 'profile_data')) {
                $table->dropColumn('profile_data');
            }
        });
    }
};
