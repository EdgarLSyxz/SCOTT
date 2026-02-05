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

    public static function values(): array
    {
        return array_map(fn(MediaIssue $m) => $m->value, self::cases());
    }

    public static function optionsForSelect(): array
    {
        $vals = self::values();
        return array_combine($vals, $vals);
    }
}
