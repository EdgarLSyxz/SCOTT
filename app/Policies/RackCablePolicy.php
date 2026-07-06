<?php

namespace App\Policies;

use App\Models\RackCable;
use App\Models\User;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class RackCablePolicy
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
        return $this->hasPerm($user, 'rack-cables.view');
    }

    public function view(User $user, RackCable $cable): bool
    {
        return $this->hasPerm($user, 'rack-cables.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPerm($user, 'rack-cables.create');
    }

    public function update(User $user, RackCable $cable): bool
    {
        return $this->hasPerm($user, 'rack-cables.edit');
    }

    public function delete(User $user, RackCable $cable): bool
    {
        return $this->hasPerm($user, 'rack-cables.delete');
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
