<?php

use App\Http\Controllers\Admin\Utils\LanguageController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\ReportController;
use App\Http\Middleware\CheckUserStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/google-auth/redirect', [GoogleAuthController::class, 'redirectToGoogle'])->name('redirectToGoogle');

Route::get('/google-auth/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('handleGoogleCallback');

Route::get('/verification/status', function () {
    return response()->json([
        'verified' => Auth::user()?->email_verified_at !== null,
    ]);
})->middleware(['auth'])->name('verification.status');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified', CheckUserStatus::class,
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::resource('/reports', ReportController::class)->middleware(['auth', CheckUserStatus::class]);

Route::post('/language/switch', [LanguageController::class, 'switchLanguage'])
    ->middleware([CheckUserStatus::class])
    ->name('language.switch');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
