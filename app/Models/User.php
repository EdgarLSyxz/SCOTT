<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasRoles;

    use HasFactory;

    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'report_mail_preferences',
        'status',
        'area',
        'can_switch_area',
        'default_area',
        'is_conmutaciones_manager',
    ];

    public const AREA_OTT = 'OTT';

    public const AREA_DTH = 'DTH';

    public static function getAreas(): array
    {
        return [self::AREA_OTT, self::AREA_DTH];
    }

    public function setAreaAttribute($value)
    {
        if (! in_array($value, self::getAreas())) {
            throw new \InvalidArgumentException("Invalid area value: $value");
        }
        $this->attributes['area'] = $value;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'status' => 'boolean',
        'can_switch_area' => 'boolean',
        'is_conmutaciones_manager' => 'boolean',
    ];

    public function reports()
    {
        return $this->hasMany(Report::class, 'reported_by');
    }

    protected static function booted()
    {
        static::created(function ($user) {
            if (! $user->roles()->exists()) {
                $user->assignRole('user');
            }
        });
    }

    public function before(User $user, $ability)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    public function lastReport()
    {
        return $this->hasOne(Report::class, 'reported_by')->latestOfMany();
    }

    public function canAccessModulators(): bool
    {
        $isMaster = (int) $this->id === 1 || $this->hasRole('master');
        $isDth = strtoupper(trim((string) ($this->area ?? ''))) === 'DTH';
        $isConmutacionesManager = (bool) ($this->is_conmutaciones_manager ?? false);

        $hasSwitchesAdminPermission = false;
        try {
            $permission = \Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => 'switches.admin',
                'guard_name' => 'web',
            ]);
            $hasSwitchesAdminPermission = $permission && $this->hasPermissionTo('switches.admin');
        } catch (\Throwable $e) {
            $hasSwitchesAdminPermission = false;
        }

        return $isMaster || $isDth || $isConmutacionesManager || $hasSwitchesAdminPermission;
    }
}
