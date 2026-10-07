<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
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
            'rack-ip-addressing.view',
            'rack-ip-addressing.create',
            'rack-ip-addressing.edit',
            'rack-ip-addressing.delete',
            'rack-map.view',
            'grafana.view',
            'grafana.create',
            'grafana.edit',
            'grafana.delete',
            'solar-interferences.view',
            'solar-interferences.create',
            'solar-interferences.edit',
            'solar-interferences.delete',
            'roles.edit',
            'permissions.assign',
            'data-centers.admin',
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

        $masterUsers = User::role('master')->get();
        $solarPermissions = [
            'solar-interferences.view',
            'solar-interferences.create',
            'solar-interferences.edit',
            'solar-interferences.delete',
        ];

        foreach ($masterUsers as $masterUser) {
            if ($masterUser->id === 1) {
                continue;
            }
            foreach ($solarPermissions as $permission) {
                if (! $masterUser->hasPermissionTo($permission)) {
                    $masterUser->givePermissionTo($permission);
                }
            }
        }
    }
}
