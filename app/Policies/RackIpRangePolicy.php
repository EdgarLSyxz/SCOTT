<?php

namespace App\Policies;

use App\Models\RackIpRange;
use App\Models\User;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class RackIpRangePolicy
{
    public function before(User $user, $ability): ?bool
    {
        $allowedIds = [1, 2, 3, 5, 7, 8];
        if (in_array((int) $user->id, $allowedIds, true)) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $this->hasPerm($user, 'rack-networking.view');
    }

    public function view(User $user, RackIpRange $ipRange): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->hasPerm($user, 'rack-networking.create');
    }

    public function update(User $user, RackIpRange $ipRange): bool
    {
        return $this->hasPerm($user, 'rack-networking.edit');
    }

    public function delete(User $user, RackIpRange $ipRange): bool
    {
        return $this->hasPerm($user, 'rack-networking.delete');
    }

    private function hasPerm(User $user, string $perm): bool
    {
        try {
            return (bool) $user->hasPermissionTo($perm);
        } catch (PermissionDoesNotExist $e) {
            return false;
        }
    }
}
