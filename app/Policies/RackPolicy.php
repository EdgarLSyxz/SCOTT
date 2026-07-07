<?php

namespace App\Policies;

use App\Models\Rack;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RackPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        $allowedIds = [1, 2, 3, 5, 7, 8];

        if (in_array($user->id, $allowedIds)) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return $this->userHasPermission($user, 'rack-layout.view');
    }

    public function view(User $user, Rack $rack): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->userHasPermission($user, 'rack-layout.create');
    }

    public function edit(User $user, Rack $rack): bool
    {
        return $this->userHasPermission($user, 'rack-layout.edit');
    }

    public function update(User $user, Rack $rack): bool
    {
        return $this->edit($user, $rack);
    }

    public function delete(User $user, Rack $rack): bool
    {
        return $this->userHasPermission($user, 'rack-layout.delete');
    }

    public function viewMap(User $user): bool
    {
        return $this->userHasPermission($user, 'rack-map.view');
    }

    public function manageIpAddressing(User $user, Rack $rack): bool
    {
        return $this->userHasPermission($user, 'rack-ip-addressing.view');
    }

    private function userHasPermission(User $user, string $permission): bool
    {
        try {
            return $user->hasPermissionTo($permission);
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
            return false;
        }
    }
}
