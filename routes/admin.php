<?php

use App\Http\Controllers\Admin\ChannelController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\DownloadExportController;
use App\Http\Controllers\Admin\GrafanaController;
use App\Http\Controllers\Admin\LogAnalyticsController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\RadioController;
use App\Http\Controllers\Admin\RackIpRangeController;
use App\Http\Controllers\Admin\RackLayoutController;
use App\Http\Controllers\Admin\ReportSlaController;
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

Route::resource('/devices', DeviceController::class)
    ->middleware(['auth', 'verified']);

Route::get('admin/devices/packages', [DeviceController::class, 'packages'])
    ->name('admin.devices.packages')
    ->middleware(['auth', 'verified']);

Route::post('admin/devices/process-pdf', [DeviceController::class, 'processPDF'])
    ->name('admin.devices.process-pdf')
    ->middleware(['auth', 'verified']);

Route::get('admin/devices/packages/data', [PackageController::class, 'apiList'])
    ->name('admin.devices.packages.data')
    ->middleware(['auth', 'verified']);

Route::get('admin/devices/packages/{id}/excel', [PackageController::class, 'exportExcel'])
    ->name('admin.devices.packages.excel')
    ->middleware(['auth', 'verified']);

Route::get('admin/devices/log-analytics', [DeviceController::class, 'logAnalytics'])
    ->name('admin.devices.log-analytics')
    ->middleware(['auth', 'verified']);

Route::post('admin/log-analytics/upload', [LogAnalyticsController::class, 'upload'])
    ->name('admin.log-analytics.upload')
    ->middleware(['auth', 'verified']);

Route::delete('admin/log-analytics/{id}', [LogAnalyticsController::class, 'delete'])
    ->name('admin.log-analytics.delete')
    ->middleware(['auth', 'verified']);

Route::get('admin/devices/monthly-downloads', [DeviceController::class, 'monthlyDownloads'])
    ->name('admin.devices.monthly-downloads')
    ->middleware(['auth', 'verified']);

Route::get('admin/devices/downloads/history.csv', [DownloadExportController::class, 'historyCSV'])
    ->name('admin.downloads.history.csv')
    ->middleware(['auth', 'verified']);

Route::post('admin/devices/downloads/history.pdf', [DownloadExportController::class, 'historyPDF'])
    ->name('admin.downloads.history.pdf')
    ->middleware(['auth', 'verified']);

Route::post('admin/devices/downloads/history.email', [DownloadExportController::class, 'historyEmail'])
    ->name('admin.downloads.history.email')
    ->middleware(['auth', 'verified']);

Route::get('admin/devices/downloads/history.data', [DownloadExportController::class, 'historyData'])
    ->name('admin.downloads.history.data')
    ->middleware(['auth', 'verified']);

Route::get('admin/devices/downloads/months', [DownloadExportController::class, 'getMonthsByYear'])
    ->name('admin.downloads.months')
    ->middleware(['auth', 'verified']);

Route::resource('/radios', RadioController::class)
    ->middleware(['auth', 'verified', 'can:viewAny,App\Models\Radio']);

Route::resource('/grafana', GrafanaController::class)
    ->middleware(['auth', 'verified', 'can:viewAny,App\Models\GrafanaPanel']);

Route::resource('admin/reports/sla', ReportSlaController::class)
    ->middleware(['auth', 'verified']);

Route::match(['get', 'post'], '/user/switch-area/{area}', [UserController::class, 'switchArea'])->name('user.switch-area')->middleware(['auth', 'verified']);

Route::prefix('admin/devices/rack-layout')->name('admin.rack-layout.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [RackLayoutController::class, 'index'])->name('index');
    Route::get('/create', [RackLayoutController::class, 'create'])->name('create');
    Route::post('/', [RackLayoutController::class, 'store'])->name('store');
    Route::get('/map', [RackLayoutController::class, 'map'])->name('map');
    Route::get('/{rack}/edit', [RackLayoutController::class, 'editRack'])->name('edit-rack');
    Route::put('/{rack}', [RackLayoutController::class, 'updateRack'])->name('update-rack');
    Route::get('/{rack}', [RackLayoutController::class, 'show'])->name('show');
    Route::delete('/{rack}', [RackLayoutController::class, 'destroyRack'])->name('destroy-rack');
    Route::get('/{rack}/export-pdf', [RackLayoutController::class, 'exportPdf'])->name('export-pdf');
    Route::get('/{rack}/history', [RackLayoutController::class, 'history'])->name('history');
    Route::get('/{rack}/position/{position}/edit', [RackLayoutController::class, 'edit'])->name('position.edit');
    Route::put('/{rack}/position/{position}', [RackLayoutController::class, 'update'])->name('position.update');
    Route::delete('/{rack}/position/{position}', [RackLayoutController::class, 'destroy'])->name('position.destroy');
    Route::get('/{rack}/position/{position}/history', [RackLayoutController::class, 'positionHistory'])->name('position.history');

    Route::prefix('{rack}/ip-addressing')->name('ip-addressing.')->group(function () {
        Route::get('/', [RackIpRangeController::class, 'index'])->name('index');
        Route::get('/create', [RackIpRangeController::class, 'create'])->name('create');
        Route::post('/', [RackIpRangeController::class, 'store'])->name('store');
        Route::get('/{ipRange}/edit', [RackIpRangeController::class, 'edit'])->name('edit');
        Route::put('/{ipRange}', [RackIpRangeController::class, 'update'])->name('update');
        Route::delete('/{ipRange}', [RackIpRangeController::class, 'destroy'])->name('destroy');
    });
});

Route::view('/modulators', 'admin.modulators.index')
    ->name('admin.modulators.index')
    ->middleware(['auth', 'verified'])
    ->middleware(\App\Http\Middleware\EnsureUserCanAccessModulators::class);

Route::view('/modulators', 'admin.modulators.index')
    ->name('modulators.index')
    ->middleware(['auth', 'verified'])
    ->middleware(\App\Http\Middleware\EnsureUserCanAccessModulators::class);

Route::get('admin/modulators/switches/export', [\App\Http\Controllers\Admin\ModulatorExportController::class, 'export'])
    ->name('admin.modulators.switches.export')
    ->middleware(['auth', 'verified', \App\Http\Middleware\EnsureUserCanAccessModulators::class]);
