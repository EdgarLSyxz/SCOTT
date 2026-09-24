<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $user_google = Socialite::driver('google')->user();
            $email = $user_google->getEmail();

            if (! str_ends_with($email, '@stargroup.com.mx')) {
                return "<script>
                    window.opener.postMessage({ error: 'Only emails with the @stargroup.com.mx domain are allowed.' }, '*');
                    window.close();
                </script>";
            }

            $user = User::updateOrCreate(
                ['google_id' => $user_google->getId()],
                [
                    'name' => $user_google->getName(),
                    'email' => $email,
                ]
            );

            if (
                $user instanceof MustVerifyEmail &&
                ! $user->hasVerifiedEmail()
            ) {
                $user->sendEmailVerificationNotification();
            }

            Auth::login($user);

            return "<script>
                window.opener.postMessage({ success: true }, '*');
                window.close();
            </script>";
        } catch (\Exception $e) {
            return "<script>
                window.opener.postMessage({ error: 'An error occurred during Google authentication: ".$e->getMessage()."' }, '*');
                window.close();
            </script>";
        }
    }
}
