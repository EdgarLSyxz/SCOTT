<?php

use App\Http\Controllers\Admin\ChannelController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\GrafanaController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\RadioController;
use App\Http\Controllers\Admin\DownloadExportController;
use App\Http\Controllers\Admin\StageController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('admin.dashboard');
})->name('dashboard');

Route::resource('/users', UserController::class)->middleware(['auth', 'verified']);

Route::put('/users/{user}/permissions', [UserController::class, 'updatePermissions'])->name('users.permissions');

Route::resource('/channels', ChannelController::class)
    ->middleware(['auth', 'verified', 'can:viewAny,App\Models\Channel']);

Route::resource('/stages', StageController::class)
    ->middleware(['auth', 'verified', 'can:viewAny,App\Models\Stage']);

Route::get('devices/packages', [DeviceController::class, 'packages'])
    ->name('admin.devices.packages')
    ->middleware(['auth', 'verified', 'can:viewAny,App\Models\Device']);

Route::post('devices/process-pdf', [DeviceController::class, 'processPDF'])
    ->name('admin.devices.process-pdf')
    ->middleware(['auth', 'verified', 'can:viewAny,App\Models\Device']);

Route::resource('/packages', PackageController::class)
    ->middleware(['auth', 'verified', 'can:viewAny,App\Models\Device']);

Route::get('devices/monthly-downloads', [DeviceController::class, 'monthlyDownloads'])
    ->name('admin.devices.monthly-downloads')
    ->middleware(['auth', 'verified', 'can:viewAny,App\Models\Device']);

Route::get('devices/downloads/history.csv', [DownloadExportController::class, 'historyCSV'])
    ->name('admin.downloads.history.csv')
    ->middleware(['auth', 'verified', 'can:viewAny,App\Models\Device']);

Route::post('devices/downloads/history.pdf', [DownloadExportController::class, 'historyPDF'])
    ->name('admin.downloads.history.pdf')
    ->middleware(['auth', 'verified', 'can:viewAny,App\Models\Device']);

Route::post('devices/downloads/history.email', [DownloadExportController::class, 'historyEmail'])
    ->name('admin.downloads.history.email')
    ->middleware(['auth', 'verified', 'can:viewAny,App\Models\Device']);

Route::get('devices/downloads/history.data', [DownloadExportController::class, 'historyData'])
    ->name('admin.downloads.history.data')
    ->middleware(['auth', 'verified', 'can:viewAny,App\Models\Device']);

Route::resource('/devices', DeviceController::class)
    ->middleware(['auth', 'verified', 'can:viewAny,App\Models\Device']);

Route::resource('/radios', RadioController::class)
    ->middleware(['auth', 'verified', 'can:viewAny,App\Models\Radio']);

Route::resource('/grafana', GrafanaController::class)
    ->middleware(['auth', 'verified', 'can:viewAny,App\Models\GrafanaPanel']);

Route::match(['get', 'post'], '/user/switch-area/{area}', [UserController::class, 'switchArea'])->name('user.switch-area')->middleware(['auth', 'verified']);
