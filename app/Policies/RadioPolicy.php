<?php

namespace App\Policies;

use App\Models\Radio;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RadioPolicy
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
        if (! $user->can('radios.view')) {
            return false;
        }

        return strtolower(trim($user->area ?? '')) === 'DTH';
    }

    public function view(User $user, Radio $radio)
    {
        if (! $user->can('radios.view')) {
            return false;
        }

        return strtolower(trim($user->area ?? '')) === 'DTH';
    }

    public function create(User $user)
    {
        if (! $user->can('radios.create')) {
            return false;
        }

        return strtolower(trim($user->area ?? '')) === 'DTH';
    }

    public function update(User $user, Radio $radio)
    {
        if (! $user->can('radios.edit')) {
            return false;
        }

        return strtolower(trim($user->area ?? '')) === 'DTH';
    }

    public function delete(User $user, Radio $radio)
    {
        if (! $user->can('radios.delete')) {
            return false;
        }

        return strtolower(trim($user->area ?? '')) === 'DTH';
    }
}
