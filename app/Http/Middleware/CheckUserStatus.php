<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (! $user || $user->status != 1) {
            // Log out any unauthorised/inactive user and redirect to login with an explanatory flash.
            try {
                Auth::logout();
            } catch (\Exception $e) {
                // ignore logout failures
            }

            session()->flash('swal', [
                'icon' => 'error',
                'title' => __('Access Denied'),
                'text' => __('Your account is inactive or you do not have permission to access this area.'),
            ]);

            return redirect()->route('login');
        }

        return $next($request);
    }
}
