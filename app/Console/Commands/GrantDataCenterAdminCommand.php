<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class GrantDataCenterAdminCommand extends Command
{
    protected $signature = 'perms:grant-data-centers-admin {user : ID del usuario, o "all" para todos} {--revoke : Quita el permiso en lugar de otorgarlo}';
    protected $description = 'Otorga o revoca el permiso data-centers.admin a un usuario (diagnóstico de centros de datos).';

    public function handle(): int
    {
        $perm = Permission::firstOrCreate([
            'name' => 'data-centers.admin',
            'guard_name' => 'web',
        ]);

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
            if ($revoke) {
                $user->revokePermissionTo($perm);
                $this->line("  - REVOKE data-centers.admin -> user #{$user->id} ({$user->email})");
            } else {
                $user->givePermissionTo($perm);
                $this->line("  + GRANT  data-centers.admin -> user #{$user->id} ({$user->email})");
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->info('Caché de permisos limpiada. Listo.');
        return self::SUCCESS;
    }
}