<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\ResetsUserPasswords;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ResetsUserPasswords::class, ResetUserPassword::class);
        $this->app->singleton(CreatesNewUsers::class, CreateNewUser::class);
        $this->app->singleton(UpdatesUserProfileInformation::class, UpdateUserProfileInformation::class);
        $this->app->singleton(UpdatesUserPasswords::class, UpdateUserPassword::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::authenticateUsing(function (Request $request) {
            $credentials = $request->only('email', 'password');

            $validator = Validator::make($credentials, [
                'email' => ['required', 'email', 'exists:users,email'],
                'password' => ['required', 'string'],
            ]);

            if ($validator->fails()) {
                $errors = implode(' ', $validator->errors()->all());

                session()->flash('swal', [
                    'icon' => 'error',
                    'title' => '¡Error!',
                    'text' => $errors,
                ]);

                throw ValidationException::withMessages($validator->errors()->toArray());
            }

            $user = User::where('email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                session()->flash('swal', [
                    'icon' => 'error',
                    'title' => __('Error!'),
                    'text' => __('The provided credentials are incorrect.'),
                ]);
                throw ValidationException::withMessages([
                    'email' => __('The provided credentials are incorrect.'),
                ]);
            }

            if (! $user->status) {
                session()->flash('swal', [
                    'icon' => 'error',
                    'title' => __('Inactive user!'),
                    'text' => __('You cannot log in because your user is inactive. Please contact the administrator.'),
                ]);
                throw ValidationException::withMessages([
                    'email' => __('You cannot log in because your user is inactive. Please contact the administrator.'),
                ]);
            }

            return $user;
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
