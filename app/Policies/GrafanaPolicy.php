<?php

namespace App\Policies;

use App\Models\GrafanaPanel;
use App\Models\User;

class GrafanaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            'grafana.view',
            'grafana.create',
            'grafana.edit',
            'grafana.delete',
        ]) || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, GrafanaPanel $grafana): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('grafana.create') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can edit the model.
     */
    public function edit(User $user, GrafanaPanel $grafana): bool
    {
        return $user->hasPermissionTo('grafana.edit') || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GrafanaPanel $grafana): bool
    {
        return $user->hasPermissionTo('grafana.delete') || $user->hasRole('admin');
    }

    public function restore(User $user, GrafanaPanel $grafana): bool
    {
        return false;
    }

    public function forceDelete(User $user, GrafanaPanel $grafana): bool
    {
        return false;
    }
}
