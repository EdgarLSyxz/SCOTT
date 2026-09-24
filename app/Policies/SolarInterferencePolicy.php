<?php

namespace App\Policies;

use App\Models\SolarInterferenceUpload;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SolarInterferencePolicy
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
        return (bool) $user->can('solar-interferences.view');
    }

    public function view(User $user, SolarInterferenceUpload $upload)
    {
        return (bool) $user->can('solar-interferences.view');
    }

    public function create(User $user)
    {
        return (bool) $user->can('solar-interferences.create');
    }

    public function update(User $user, SolarInterferenceUpload $upload)
    {
        return (bool) $user->can('solar-interferences.edit');
    }

    public function delete(User $user, SolarInterferenceUpload $upload)
    {
        return (bool) $user->can('solar-interferences.delete');
    }
}
