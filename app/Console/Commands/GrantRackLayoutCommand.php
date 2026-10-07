<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class GrantRackLayoutCommand extends Command
{
    protected $signature = 'perms:grant-rack-layout {user : ID del usuario, o "all" para todos} {--revoke : Quita los permisos en lugar de otorgarlos}';

    protected $description = 'Otorga o revoca los permisos de rack-layout (incluye data-centers.admin) a un usuario.';

    private const PERMISSIONS = [
        'data-centers.admin',
        'rack-layout.view',
        'rack-layout.create',
        'rack-layout.edit',
        'rack-layout.delete',
        'rack-ip-addressing.view',
        'rack-ip-addressing.create',
        'rack-ip-addressing.edit',
        'rack-ip-addressing.delete',
        'rack-map.view',
    ];

    public function handle(): int
    {
        foreach (self::PERMISSIONS as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $arg = (string) $this->argument('user');
        $revoke = (bool) $this->option('revoke');

        $query = User::query();
        if ($arg !== 'all') {
            $query->where('id', (int) $arg);
        }
        $users = $query->get();

        if ($users->isEmpty()) {
            $this->error('No se encontraron usuarios.');
            return self::FAILURE;
        }

        foreach ($users as $user) {
            foreach (self::PERMISSIONS as $name) {
                if ($revoke) {
                    if ($user->hasPermissionTo($name)) {
                        $user->revokePermissionTo($name);
                        $this->line("  - REVOKE {$name} -> user #{$user->id} ({$user->email})");
                    }
                } else {
                    $user->givePermissionTo($name);
                    $this->line("  + GRANT  {$name} -> user #{$user->id} ({$user->email})");
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->info('Caché de permisos limpiada. Listo.');
        return self::SUCCESS;
    }
}