<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'channels.view',
            'channels.create',
            'channels.edit',
            'channels.delete',
            'stages.view',
            'stages.create',
            'stages.edit',
            'stages.delete',
            'radios.view',
            'radios.create',
            'radios.edit',
            'radios.delete',
            'devices.view',
            'devices.create',
            'devices.edit',
            'devices.delete',
            'rack-layout.view',
            'rack-layout.create',
            'rack-layout.edit',
            'rack-layout.delete',
            'rack-cables.view',
            'rack-cables.create',
            'rack-cables.edit',
            'rack-cables.delete',
            'rack-map.view',
            'grafana.view',
            'grafana.create',
            'grafana.edit',
            'grafana.delete',
            'roles.edit',
            'permissions.assign',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $master = Role::firstOrCreate(['name' => 'master', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        $master->syncPermissions(Permission::all());

        $user = User::find(1);
        if ($user) {
            $user->syncPermissions(Permission::pluck('name')->toArray());
        }
    }
}
