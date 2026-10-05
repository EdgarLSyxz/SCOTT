<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InspectPermissionsCommand extends Command
{
    protected $signature = 'perms:inspect';
    protected $description = 'Inspecciona permisos y asignaciones para diagnosticar acceso a conmutaciones';

    public function handle(): int
    {
        $perms = DB::table('permissions')->get(['id', 'name', 'guard_name'])->map(fn($r) => (array) $r)->all();
        $this->line('PERMISOS:');
        $this->line(json_encode($perms, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $sw = collect($perms)->firstWhere('name', 'switches.admin');
        if (! $sw) {
            $this->error('No existe el permiso switches.admin');
            return self::FAILURE;
        }

        $rows = DB::table('model_has_permissions')
            ->where('permission_id', $sw['id'])
            ->get(['permission_id', 'model_id', 'model_type']);

        $this->line('ASIGNACIONES model_has_permissions:');
        $this->line(json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $users = DB::table('users')
            ->join('model_has_permissions', 'model_has_permissions.model_id', '=', 'users.id')
            ->where('model_has_permissions.permission_id', $sw['id'])
            ->where('model_has_permissions.model_type', \App\Models\User::class)
            ->select('users.id', 'users.email')
            ->get();

        $this->line('USUARIOS con switches.admin:');
        $this->line(json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }
}