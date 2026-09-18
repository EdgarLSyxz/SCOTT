<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class ModulatorSetting extends Model
{
    protected $fillable = [
        'switch_pin_hash',
        'updated_by',
    ];

    /**
     * Get the single settings row, creating it with the config default if missing.
     */
    public static function current(): self
    {
        $setting = static::query()->firstOrCreate([], [
            'switch_pin_hash' => Hash::make((string) config('modulators.switch_pin', '1234')),
        ]);

        if ($setting->switch_pin_hash !== null
            && preg_match('/^\d+$/', (string) $setting->switch_pin_hash)
        ) {
            $setting->forceFill([
                'switch_pin_hash' => Hash::make((string) $setting->switch_pin_hash),
            ])->save();
        }

        return $setting;
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
