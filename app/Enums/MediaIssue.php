<?php

namespace App\Enums;

enum MediaIssue: string
{
    case NO_AUDIO = 'NO AUDIO';
    case NO_VIDEO = 'NO VIDEO';
    case AUDIO_ONLY = 'AUDIO ONLY';
    case NO_AV = 'NO A/V';
    case AUDIO_SATURATED = 'AUDIO SATURATED';
    case AUDIO_DISTORTED = 'AUDIO DISTORTED';
    case AUDIO_DOLBY = 'AUDIO DOLBY';
    case AUDIO_IN_ENGLISH = 'AUDIO IN ENGLISH';
    case NO_SUBTITLES = 'NO SUBTITLES';
    case AUDIO_LAG = 'AUDIO LAG';
    case LYP_SYNC = 'LYP SYNC';
    case INTERMITTENCY = 'INTERMITTENCY';
    case VIDEO_LAG = 'VIDEO LAG';
    case FREEZING = 'FREEZING';
    case PIXELATION = 'PIXELATION';
    case FLICKERING = 'FLICKERING';
    case IMAGE_IN_BLACKS = 'IMAGE IN BLACKS';
    case DIGITIZED_IMAGE = 'DIGITIZED IMAGE';
    case PHASE_GAP_IN_EPG = 'PHASE GAP IN EPG';
    case WRONG_EPG = 'WRONG EPG';
    case NO_EPG = 'NO EPG';
    case WRONG_CHANNEL_LOGO = 'WRONG CHANNEL LOGO';
    case NO_CHANNEL_LOGO = 'NO CHANNEL LOGO';
    case NO_EPG_AND_CHANNEL_ICON = 'NO EPG AND CHANNEL ICON';
    case OUT_OF_SERVICE = 'OUT OF SERVICE';
    case NO_RESTART = 'NO RESTART';
    case NO_CUTV = 'NO CUTV';
    case NO_RESTART_CUTV = 'NO RESTART/CUTV';
    case DOES_NOT_HAVE_RESTART_CUTV_DISTINCTIVE = 'DOES NOT HAVE THE RESTART/CUTV DISTINCTIVE';
    case LOSS_BECAUSE_EPG_DOES_NOT_COINCIDE = 'LOSS BECAUSE THE EPG DOES NOT COINCIDE';

    public static function values(): array
    {
        return array_map(fn (MediaIssue $m) => $m->value, self::cases());
    }

    public static function optionsWithColors(): array
    {
        $config = [
            self::NO_AUDIO->value => ['color' => 'amber', 'group' => 'audio'],
            self::AUDIO_ONLY->value => ['color' => 'amber', 'group' => 'audio'],
            self::AUDIO_SATURATED->value => ['color' => 'amber', 'group' => 'audio'],
            self::AUDIO_DISTORTED->value => ['color' => 'amber', 'group' => 'audio'],
            self::AUDIO_DOLBY->value => ['color' => 'amber', 'group' => 'audio'],
            self::AUDIO_IN_ENGLISH->value => ['color' => 'amber', 'group' => 'audio'],
            self::NO_SUBTITLES->value => ['color' => 'amber', 'group' => 'audio'],
            self::AUDIO_LAG->value => ['color' => 'amber', 'group' => 'audio'],
            self::LYP_SYNC->value => ['color' => 'amber', 'group' => 'audio'],

            self::NO_VIDEO->value => ['color' => 'blue', 'group' => 'video'],
            self::VIDEO_LAG->value => ['color' => 'blue', 'group' => 'video'],
            self::FREEZING->value => ['color' => 'blue', 'group' => 'video'],
            self::PIXELATION->value => ['color' => 'blue', 'group' => 'video'],
            self::FLICKERING->value => ['color' => 'blue', 'group' => 'video'],
            self::IMAGE_IN_BLACKS->value => ['color' => 'blue', 'group' => 'video'],
            self::DIGITIZED_IMAGE->value => ['color' => 'blue', 'group' => 'video'],

            self::WRONG_CHANNEL_LOGO->value => ['color' => 'sky', 'group' => 'ui'],
            self::NO_CHANNEL_LOGO->value => ['color' => 'sky', 'group' => 'ui'],

            self::PHASE_GAP_IN_EPG->value => ['color' => 'emerald', 'group' => 'epg'],
            self::WRONG_EPG->value => ['color' => 'emerald', 'group' => 'epg'],
            self::NO_EPG->value => ['color' => 'emerald', 'group' => 'epg'],
            self::NO_EPG_AND_CHANNEL_ICON->value => ['color' => 'emerald', 'group' => 'epg'],

            self::NO_RESTART->value => ['color' => 'teal', 'group' => 'restart'],
            self::NO_CUTV->value => ['color' => 'teal', 'group' => 'restart'],
            self::NO_RESTART_CUTV->value => ['color' => 'teal', 'group' => 'restart'],
            self::DOES_NOT_HAVE_RESTART_CUTV_DISTINCTIVE->value => ['color' => 'teal', 'group' => 'restart'],
            self::LOSS_BECAUSE_EPG_DOES_NOT_COINCIDE->value => ['color' => 'teal', 'group' => 'restart'],

            self::INTERMITTENCY->value => ['color' => 'rose', 'group' => 'other'],
            self::NO_AV->value => ['color' => 'rose', 'group' => 'other'],
            self::OUT_OF_SERVICE->value => ['color' => 'rose', 'group' => 'other'],
        ];

        $options = [];
        foreach (self::cases() as $case) {
            $value = $case->value;
            $cfg = $config[$value] ?? ['color' => 'gray', 'group' => 'other'];
            $options[$value] = [
                'label' => $value,
                'color' => $cfg['color'],
                'group' => $cfg['group'],
            ];
        }

        return $options;
    }

    public static function optionsForSelect(): array
    {
        $vals = self::values();

        return array_combine($vals, $vals);
    }
}
