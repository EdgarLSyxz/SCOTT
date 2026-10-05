<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserPermissions extends Component
{
    public User $user;

    public $role;

    public $permissions = [];

    public $forbiddenPermissions = [];

    public $allRoles;

    public $allPermissions;

    public $canEditRoles;

    public $canEditPermissions;

    public $reportMails = [];

    public $hasSwitchAdmin = false;

    public $hasDataCenterAdmin = false;

    protected $listeners = [
        'user-status-toggled' => 'onUserStatusToggled',
        'switches-admin-toggled' => 'onSwitchesAdminToggled',
        'data-centers-admin-toggled' => 'onDataCentersAdminToggled',
    ];

    public function onUserStatusToggled(int $userId, bool $status): void
    {
        if ((int) $this->user->id !== (int) $userId) {
            return;
        }

        $this->user->refresh();
        $this->user->status = (bool) $status;
    }

    public function onSwitchesAdminToggled(int $userId, bool $hasSwitchAdmin): void
    {
        if ((int) $this->user->id !== (int) $userId) {
            return;
        }

        $this->user->refresh();

        $perm = \Spatie\Permission\Models\Permission::where('name', 'switches.admin')->first();
        $this->hasSwitchAdmin = $perm ? $this->user->hasPermissionTo('switches.admin') : false;
    }

    public function onDataCentersAdminToggled(int $userId, bool $hasDataCenterAdmin): void
    {
        if ((int) $this->user->id !== (int) $userId) {
            return;
        }

        $this->user->refresh();

        $perm = \Spatie\Permission\Models\Permission::where('name', 'data-centers.admin')->first();
        $this->hasDataCenterAdmin = $perm ? $this->user->hasPermissionTo('data-centers.admin') : false;
    }

    public function mount(User $user)
    {
        $this->user = $user;

        $defaultPreferences = [
            'report_created' => false,
            'report_updated' => false,
            'report_resolved' => false,
            'report_functions_created' => false,
        ];

        $userPreferences = [];
        $rawPreferences = $user->report_mail_preferences;
        if (is_string($rawPreferences)) {
            $decoded = json_decode($rawPreferences, true);
            if (is_array($decoded)) {
                $userPreferences = $decoded;
            }
        } elseif (is_array($rawPreferences)) {
            $userPreferences = $rawPreferences;
        } elseif (is_object($rawPreferences)) {
            $userPreferences = (array) $rawPreferences;
        }

        $this->reportMails = array_merge($defaultPreferences, $userPreferences);

        $this->allRoles = Role::all();
        $this->allPermissions = Permission::all();

        $this->role = $user->roles->pluck('name')->first();
        if ($user->id === 1) {
            $this->permissions = $this->allPermissions->pluck('name')->toArray();
        } else {
            $this->permissions = $user->permissions->pluck('name')->toArray();
        }

        $auth = auth()->user();

        if (! $auth) {
            abort(403);
        }

        $isAuthMaster = $auth->hasRole('master');
        $isAuthAdmin = $auth->hasRole('admin');
        $isTargetAdmin = $user->hasRole('admin');
        $isTargetUser = $user->hasRole('user');
        $isSelf = $auth->id === $user->id;

        $this->canEditRoles = $isAuthMaster && ! $isSelf;
        $this->canEditPermissions = ($isAuthMaster && ! $isSelf) || ($isAuthAdmin && $isTargetUser && ! $isSelf);

        $switchPerm = Permission::where('name', 'switches.admin')->first();
        $this->hasSwitchAdmin = $switchPerm ? $user->hasPermissionTo('switches.admin') : false;

        $dataCenterPerm = Permission::where('name', 'data-centers.admin')->first();
        $this->hasDataCenterAdmin = $dataCenterPerm ? $user->hasPermissionTo('data-centers.admin') : false;

        $firstMasterId = User::role('master')->orderBy('id')->value('id');

        $isEditingFirstMaster = $user->id === $firstMasterId && $auth->id !== $firstMasterId;

        if ($isEditingFirstMaster) {
            $this->canEditRoles = false;
            $this->canEditPermissions = false;
        }
    }

    public function saveReportPreferences()
    {
        $auth = auth()->user();

        $isAuthMaster = $auth->hasRole('master');
        $isAuthAdmin = $auth->hasRole('admin');
        $isAuthUser = $auth->hasRole('user');

        $isTargetMaster = $this->user->hasRole('master');
        $isTargetAdmin = $this->user->hasRole('admin');
        $isTargetUser = $this->user->hasRole('user');

        $isSelf = $auth->id === $this->user->id;
        $isFirstMaster = $this->user->id === 1;

        $canEditPreferences =
            ($isFirstMaster && $isSelf) ||
            (! $isFirstMaster && (
                ($isAuthMaster) ||
                ($isAuthAdmin && ($isTargetUser || $isSelf)) ||
                ($isAuthUser && $isSelf)
            ));

        if (! $canEditPreferences) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => __('Access denied'),
                'text' => __('You are not authorized to update these preferences.'),
            ]);

            return;
        }

        $auth = auth()->user();

        $reportMailOptions = [
            'report_created',
            'report_updated',
            'report_resolved',
        ];

        if ($auth && ($auth->id === 1 || strtoupper(trim($auth->area ?? '')) === 'OTT')) {
            $reportMailOptions[] = 'report_functions_created';
        }

        $existingRaw = $this->user->report_mail_preferences;
        $existingPrefs = [];
        if (is_string($existingRaw)) {
            $decoded = json_decode($existingRaw, true);
            if (is_array($decoded)) {
                $existingPrefs = $decoded;
            }
        } elseif (is_array($existingRaw)) {
            $existingPrefs = $existingRaw;
        } elseif (is_object($existingRaw)) {
            $existingPrefs = (array) $existingRaw;
        }

        $finalPreferences = $existingPrefs;
        foreach ($reportMailOptions as $option) {
            $finalPreferences[$option] = $this->reportMails[$option] ?? false;
        }

        $this->user->report_mail_preferences = $finalPreferences;
        $this->user->save();

        $this->dispatch('preferences-saved');

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => __('Preferences Updated'),
            'text' => __('Mail preferences saved successfully.'),
        ]);
    }

    public function toggleStatus()
    {
        $auth = auth()->user();
        if (! ($auth->id === 1 && $auth->hasRole('master'))) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => __('Access denied'),
                'text' => __('You are not authorized to change this status.'),
            ]);

            return;
        }

        $this->user->status = ! $this->user->status;
        $this->user->remember_token = null;
        $this->user->save();

        try {
            if (Schema::hasTable('sessions')) {
                DB::table('sessions')->where('user_id', $this->user->id)->delete();
            }
        } catch (\Exception $e) {
        }

        $this->dispatch('user-status-toggled', userId: $this->user->id, status: (bool) $this->user->status);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => __('User status changed successfully.'),
        ]);
    }

    public function updatedRole($value)
    {
        if (! $this->canEditPermissions) {
            return;
        }

        $this->applyRoleBasedPermissions($value);
    }

    public function applyRoleBasedPermissions($role)
    {
        $forbidden = [
            'admin' => ['roles.edit'],
            'user' => ['roles.edit', 'permissions.assign'],
        ];

        $this->forbiddenPermissions = $forbidden[$role] ?? [];

        if ($role === 'master') {
            $this->permissions = $this->allPermissions->pluck('name')->toArray();
        } else {
            $allowed = $this->allPermissions->filter(function ($perm) {
                return ! in_array($perm->name, $this->forbiddenPermissions);
            });
            $this->permissions = $allowed->pluck('name')->toArray();
        }
        $auth = auth()->user();
        $authIsDthOrSuper = $auth && ($auth->id === 1 || (isset($auth->area) && strtolower(trim($auth->area)) === 'dth'));
        $authIsOttOrSuper = $auth && ($auth->id === 1 || (isset($auth->area) && strtolower(trim($auth->area)) === 'ott'));

        if (! $authIsDthOrSuper) {
            $this->permissions = collect($this->permissions)->reject(function ($p) {
                return substr($p, 0, 6) === 'radios.';
            })->values()->toArray();
        }

        if (! $authIsOttOrSuper) {
            $this->permissions = collect($this->permissions)->reject(function ($p) {
                return substr($p, 0, 8) === 'devices.';
            })->values()->toArray();
        }
    }

    public function onRoleChanged($value)
    {
        $this->role = $value;
        $this->updatedRole($value);
    }

    public function save()
    {
        $auth = auth()->user();

        if (! $this->canEditRoles && ! $this->canEditPermissions) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => __('Access denied'),
                'text' => __('You are not authorized to perform this action.'),
            ]);

            return;
        }

        $requestedPermissions = collect($this->permissions);
        $auth = auth()->user();
        $authIsDthOrSuper = $auth && ($auth->id === 1 || (isset($auth->area) && strtolower(trim($auth->area)) === 'DTH'));
        $authIsOttOrSuper = $auth && ($auth->id === 1 || (isset($auth->area) && strtolower(trim($auth->area)) === 'OTT'));

        if (! $authIsDthOrSuper) {
            $requestedPermissions = $requestedPermissions->reject(function ($p) {
                return substr($p, 0, 6) === 'radios.';
            })->values();
        }

        if (! $authIsOttOrSuper) {
            $requestedPermissions = $requestedPermissions->reject(function ($p) {
                return substr($p, 0, 8) === 'devices.';
            })->values();
        }
        $forbidden = [
            'admin' => ['roles.edit'],
            'user' => ['roles.edit', 'permissions.assign'],
        ];

        if ($auth->hasRole('master')) {
            if ($this->canEditRoles && $this->role) {
                $this->user->syncRoles([$this->role]);
            }

            if ($this->canEditPermissions) {
                $this->user->syncPermissions($requestedPermissions);
            }

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => __('Well done!'),
                'text' => __('Permissions updated successfully.'),
            ]);
        } elseif ($auth->hasRole('admin') && $this->user->hasRole('user')) {
            $blocked = collect($forbidden['user']);
            $filtered = $requestedPermissions->reject(fn ($perm) => $blocked->contains($perm));

            $this->user->syncPermissions($filtered);

            $message = $filtered->count() !== $requestedPermissions->count()
                ? ['icon' => 'warning', 'title' => __('Not allowed'), 'text' => __('Some permissions were not allowed and have been excluded.')]
                : ['icon' => 'success', 'title' => __('Well done!'), 'text' => __('Permissions updated successfully.')];

            $this->dispatch('swal', $message);
        }
    }

    public function sendResetPasswordEmail()
    {
        $auth = auth()->user();
        if ($auth->id !== 1 || $this->user->id === 1) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => __('Access denied'),
                'text' => __('You are not authorized to send a password reset email to this user.'),
            ]);

            return;
        }

        try {
            \Password::broker()->sendResetLink(['email' => $this->user->email]);
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => __('Email sent'),
                'text' => __('A password reset email has been sent to the user.'),
            ]);
        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => __('Error'),
                'text' => __('There was a problem sending the password reset email.'),
            ]);
        }
    }

    public function toggleArea()
    {
        $auth = auth()->user();

        if (! $auth || $auth->id !== 1) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => __('Access denied'),
                'text' => __('You are not authorized to change this user area.'),
            ]);

            return;
        }

        $current = $this->user->area ?? null;
        $new = $current === 'DTH' ? 'OTT' : 'DTH';

        $this->user->area = $new;
        $this->user->save();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => __('User area changed to').' '.$new.'.',
        ]);

        $this->dispatch('userAreaChanged', ['userId' => $this->user->id, 'area' => $new]);
    }

    public function toggleCanSwitch()
    {
        $auth = auth()->user();

        if (! ($this->canEditPermissions || ($auth && $auth->hasRole('master')))) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => __('Access denied'),
                'text' => __('You are not authorized to change this setting.'),
            ]);

            return;
        }

        $this->user->can_switch_area = ! ($this->user->can_switch_area ?? false);
        $this->user->save();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => $this->user->can_switch_area ? __('User can now switch area.') : __('User can no longer switch area.'),
        ]);
    }

    public function toggleSwitchAdmin()
    {
        $auth = auth()->user();

        if (! ($this->canEditPermissions || ($auth && $auth->hasRole('master')))) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => __('Access denied'),
                'text' => __('You are not authorized to change this setting.'),
            ]);

            return;
        }

        $permName = 'switches.admin';

        $perm = Permission::firstOrCreate([
            'name' => $permName,
            'guard_name' => 'web',
        ]);

        try {
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        } catch (\Throwable $e) {
        }

        $this->user->unsetRelation('permissions');
        $this->user->load('permissions');

        $hasPerm = false;
        try {
            $hasPerm = $this->user->hasPermissionTo($perm->id) || $this->user->hasPermissionTo($permName);
        } catch (\Throwable $e) {
            $hasPerm = $this->user->permissions->contains('id', $perm->id);
        }

        if ($hasPerm) {
            $this->user->revokePermissionTo($perm);
            $message = __('Switch administration permission revoked.');
        } else {
            $this->user->givePermissionTo($perm);
            $message = __('Switch administration permission granted.');
        }

        $this->user->unsetRelation('permissions');
        $this->user->load('permissions');

        try {
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        } catch (\Throwable $e) {
        }

        $this->hasSwitchAdmin = $this->user->permissions->contains('id', $perm->id);

        $this->dispatch('switches-admin-toggled', userId: $this->user->id, hasSwitchAdmin: (bool) $this->hasSwitchAdmin);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => $message,
        ]);
    }

    public function toggleDataCenterAdmin()
    {
        $auth = auth()->user();

        if (! ($this->canEditPermissions || ($auth && $auth->hasRole('master')))) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => __('Access denied'),
                'text' => __('You are not authorized to change this setting.'),
            ]);

            return;
        }

        $permName = 'data-centers.admin';

        $perm = Permission::firstOrCreate([
            'name' => $permName,
            'guard_name' => 'web',
        ]);

        try {
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        } catch (\Throwable $e) {
        }

        $this->user->unsetRelation('permissions');
        $this->user->load('permissions');

        $hasPerm = false;
        try {
            $hasPerm = $this->user->hasPermissionTo($perm->id) || $this->user->hasPermissionTo($permName);
        } catch (\Throwable $e) {
            $hasPerm = $this->user->permissions->contains('id', $perm->id);
        }

        if ($hasPerm) {
            $this->user->revokePermissionTo($perm);
            $message = __('Data center administration permission revoked.');
        } else {
            $this->user->givePermissionTo($perm);
            $message = __('Data center administration permission granted.');
        }

        $this->user->unsetRelation('permissions');
        $this->user->load('permissions');

        try {
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        } catch (\Throwable $e) {
        }

        $this->hasDataCenterAdmin = $this->user->permissions->contains('id', $perm->id);

        $this->dispatch('data-centers-admin-toggled', userId: $this->user->id, hasDataCenterAdmin: (bool) $this->hasDataCenterAdmin);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => __('Well done!'),
            'text' => $message,
        ]);
    }

    public function render()
    {
        return view('livewire.admin.users.user-permissions');
    }
}
