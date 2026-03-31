<?php

namespace App\Policies;

use App\Models\Device;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DevicePolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->id === 1) {
            return true;
        }
    }

    public function viewAny(User $user)
    {
        return $user->can('devices.view');
    }

    public function view(User $user, ?Device $device = null)
    {
        if (! $user->can('devices.view')) {
            return false;
        }

        return true;
    }

    public function create(User $user)
    {
        if (! $user->can('devices.create')) {
            return false;
        }
        return strtolower(trim($user->area ?? '')) === 'ott';
    }

    public function update(User $user, Device $device)
    {
        if (! $user->can('devices.edit')) {
            return false;
        }
        return strtolower(trim($user->area ?? '')) === 'ott';
    }

    public function delete(User $user, Device $device)
    {
        if (! $user->can('devices.delete')) {
            return false;
        }
        return strtolower(trim($user->area ?? '')) === 'ott';
    }
}
