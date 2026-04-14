<?php

namespace App\Services;

use App\Models\Report;
use App\Models\ReportSlaSetting;
use Carbon\Carbon;

class ReportSlaService
{
    public function getSettings(string $area): array
    {
        $normalizedArea = strtoupper(trim($area));

        $defaults = [
            'area' => $normalizedArea,
            'is_active' => true,
            'level_1_minutes' => 30,
            'level_2_minutes' => 90,
            'level_3_minutes' => 180,
        ];

        $setting = ReportSlaSetting::where('area', $normalizedArea)->first();

        if (! $setting) {
            return $defaults;
        }

        return [
            'area' => $normalizedArea,
            'is_active' => (bool) $setting->is_active,
            'level_1_minutes' => (int) $setting->level_1_minutes,
            'level_2_minutes' => (int) $setting->level_2_minutes,
            'level_3_minutes' => (int) $setting->level_3_minutes,
        ];
    }

    public function evaluate(Report $report, $lastCommentAt = null): array
    {
        $area = strtoupper(trim((string) ($report->area ?? Report::AREA_OTT)));
        $settings = $this->getSettings($area);

        $createdAt = $report->created_at ? Carbon::parse($report->created_at) : now();
        $lastComment = $lastCommentAt ? Carbon::parse($lastCommentAt) : null;
        $lastActivityAt = $lastComment && $lastComment->greaterThan($createdAt) ? $lastComment : $createdAt;

        $elapsedMinutes = max(0, $lastActivityAt->diffInMinutes(now()));

        if (! $settings['is_active']) {
            return [
                'enabled' => false,
                'level' => 0,
                'label' => __('SLA disabled'),
                'badge' => 'text-gray-700 bg-gray-200 dark:text-gray-200 dark:bg-gray-700',
                'elapsed_minutes' => $elapsedMinutes,
                'elapsed_human' => $lastActivityAt->diffForHumans(),
                'last_activity_at' => $lastActivityAt,
            ];
        }

        $level = 0;
        $label = __('Normal');
        $badge = 'text-emerald-800 bg-emerald-200 dark:text-emerald-200 dark:bg-emerald-800';

        if ($elapsedMinutes >= $settings['level_3_minutes']) {
            $level = 3;
            $label = __('Level 3 - Urgent review');
            $badge = 'text-red-800 bg-red-200 dark:text-red-200 dark:bg-red-800 animate-pulse';
        } elseif ($elapsedMinutes >= $settings['level_2_minutes']) {
            $level = 2;
            $label = __('Level 2 - Warning');
            $badge = 'text-orange-800 bg-orange-200 dark:text-orange-200 dark:bg-orange-800';
        } elseif ($elapsedMinutes >= $settings['level_1_minutes']) {
            $level = 1;
            $label = __('Level 1 - Follow up');
            $badge = 'text-amber-800 bg-amber-200 dark:text-amber-200 dark:bg-amber-800';
        }

        return [
            'enabled' => true,
            'level' => $level,
            'label' => $label,
            'badge' => $badge,
            'elapsed_minutes' => $elapsedMinutes,
            'elapsed_human' => $lastActivityAt->diffForHumans(),
            'last_activity_at' => $lastActivityAt,
        ];
    }
}
