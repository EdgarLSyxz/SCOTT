<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        if (!Schema::hasTable('video_profile_tests')) return;

        try { DB::statement('ALTER TABLE `video_profile_tests` DROP FOREIGN KEY `video_profile_tests_report_id_foreign`'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE `video_profile_tests` DROP FOREIGN KEY `video_profile_tests_channel_id_foreign`'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE `video_profile_tests` DROP FOREIGN KEY `video_profile_tests_user_id_foreign`'); } catch (\Exception $e) {}

        $statements = [
            "ALTER TABLE `video_profile_tests` MODIFY `report_id` BIGINT UNSIGNED NULL",
            "ALTER TABLE `video_profile_tests` MODIFY `channel_id` BIGINT UNSIGNED NULL",
            "ALTER TABLE `video_profile_tests` MODIFY `user_id` BIGINT UNSIGNED NULL",
            "ALTER TABLE `video_profile_tests` MODIFY `high` VARCHAR(255) NULL",
            "ALTER TABLE `video_profile_tests` MODIFY `medium` VARCHAR(255) NULL",
            "ALTER TABLE `video_profile_tests` MODIFY `low` VARCHAR(255) NULL",
            "ALTER TABLE `video_profile_tests` MODIFY `profile_data` LONGTEXT NULL",
        ];

        foreach ($statements as $sql) {
            try {
                DB::statement($sql);
            } catch (\Exception $e) {
                info('Could not run statement: ' . $sql . ' -> ' . $e->getMessage());
            }
        }

        try { DB::statement('ALTER TABLE `video_profile_tests` ADD CONSTRAINT `video_profile_tests_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports`(`id`) ON DELETE CASCADE'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE `video_profile_tests` ADD CONSTRAINT `video_profile_tests_channel_id_foreign` FOREIGN KEY (`channel_id`) REFERENCES `channels`(`id`) ON DELETE CASCADE'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE `video_profile_tests` ADD CONSTRAINT `video_profile_tests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE'); } catch (\Exception $e) {}
    }

    public function down(): void
    {
        if (!Schema::hasTable('video_profile_tests')) return;

        try { DB::statement('ALTER TABLE `video_profile_tests` DROP FOREIGN KEY `video_profile_tests_report_id_foreign`'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE `video_profile_tests` DROP FOREIGN KEY `video_profile_tests_channel_id_foreign`'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE `video_profile_tests` DROP FOREIGN KEY `video_profile_tests_user_id_foreign`'); } catch (\Exception $e) {}

        $statements = [
            "ALTER TABLE `video_profile_tests` MODIFY `report_id` BIGINT UNSIGNED NOT NULL",
            "ALTER TABLE `video_profile_tests` MODIFY `channel_id` BIGINT UNSIGNED NOT NULL",
            "ALTER TABLE `video_profile_tests` MODIFY `user_id` BIGINT UNSIGNED NOT NULL",
            "ALTER TABLE `video_profile_tests` MODIFY `high` VARCHAR(255) NULL",
            "ALTER TABLE `video_profile_tests` MODIFY `medium` VARCHAR(255) NULL",
            "ALTER TABLE `video_profile_tests` MODIFY `low` VARCHAR(255) NULL",
            "ALTER TABLE `video_profile_tests` MODIFY `profile_data` LONGTEXT NULL",
        ];

        foreach ($statements as $sql) {
            try { DB::statement($sql); } catch (\Exception $e) { info('Could not run statement: ' . $sql . ' -> ' . $e->getMessage()); }
        }

        try { DB::statement('ALTER TABLE `video_profile_tests` ADD CONSTRAINT `video_profile_tests_report_id_foreign` FOREIGN KEY (`report_id`) REFERENCES `reports`(`id`) ON DELETE CASCADE'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE `video_profile_tests` ADD CONSTRAINT `video_profile_tests_channel_id_foreign` FOREIGN KEY (`channel_id`) REFERENCES `channels`(`id`) ON DELETE CASCADE'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE `video_profile_tests` ADD CONSTRAINT `video_profile_tests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE'); } catch (\Exception $e) {}
    }
};
