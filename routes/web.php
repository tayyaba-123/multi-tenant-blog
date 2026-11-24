<?php

use App\Http\Controllers\Central\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Central\TenantController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Each central domain registers the same route names. The last registration
// wins when generating URLs, so iterate in reverse to make the first
// configured domain (blogs.test) the canonical one.
foreach (array_reverse(config('tenancy.central_domains')) as $domain) {
    Route::domain($domain)->name('central.')->group(function () {
        Route::get('/', fn () => Inertia::render('Central/Home'))->name('home');

        Route::prefix('admin')->group(function () {
            Route::middleware('guest')->group(function () {
                Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
                Route::post('login', [AuthenticatedSessionController::class, 'store']);
            });

            Route::middleware('auth')->group(function () {
                Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

                Route::get('tenants', [TenantController::class, 'index'])->name('tenants.index');
                Route::get('tenants/create', [TenantController::class, 'create'])->name('tenants.create');
                Route::post('tenants', [TenantController::class, 'store'])->name('tenants.store');
            });
        });
    });
}
