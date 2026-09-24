<?php

namespace App\Enums;

enum ChannelIssues: string
{
    case CORRECT = 'correct';
    case LIGHT_PAUSES_AUDIO = 'light_pauses_audio';
    case LIGHT_PAUSES_VIDEO = 'light_pauses_video';
    case LIGHT_PAUSES_AV = 'light_pauses_av';
    case CONSTANT_PAUSES_AUDIO = 'constant_pauses_audio';
    case CONSTANT_PAUSES_VIDEO = 'constant_pauses_video';
    case CONSTANT_PAUSES_AV = 'constant_pauses_av';
    case SHORT_LOADING_TIME = 'short_loading_time';
    case LONG_LOADING_TIME = 'long_loading_time';
    case NO_SHOW_CONTENT = 'no_show_content';
}
