<?php

namespace Database\Seeders;

use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            ChannelsSeeder::class,
            StagesSeeder::class,
            TransponderSeeder::class,
        ]);

        User::flushEventListeners();

        $master = User::firstOrCreate(
            ['email' => 'ecuevas@stargroup.com.mx'],
            [
                'name' => 'Edgar Leonel Acevedo Cuevas',
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
            ]
        );

        $master->assignRole('master');

        User::observe(UserObserver::class);
    }
}
