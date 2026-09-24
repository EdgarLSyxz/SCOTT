<?php

use App\Models\User;

if (! function_exists('isMasterAdmin')) {
    function isMasterAdmin(User $user): bool
    {
        return $user->id === 1 && $user->hasRole('master');
    }
}
