<?php

namespace App\Providers;

use App\Models\Device;
use App\Models\Radio;
use App\Models\SolarInterferenceUpload;
use App\Policies\DevicePolicy;
use App\Policies\RadioPolicy;
use App\Policies\SolarInterferencePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Radio::class => RadioPolicy::class,
        Device::class => DevicePolicy::class,
        SolarInterferenceUpload::class => SolarInterferencePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Additional gates/policies can be registered here if needed.
    }
}
