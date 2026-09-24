<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthenticatedSessionController extends Controller
{
    public function destroy(Request $request)
    {
        $user = $request->user();

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($user) {
            try {
                $user->setRememberToken(null);
                $user->save();
            } catch (\Throwable $e) {
            }

            if (config('session.driver') === 'database') {
                try {
                    DB::table(config('session.table', 'sessions'))
                        ->where('user_id', $user->id)
                        ->delete();
                } catch (\Throwable $e) {
                }
            }
        }

        return redirect()->route('login');
    }
}
