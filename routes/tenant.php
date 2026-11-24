<?php

declare(strict_types=1);

use App\Http\Controllers\BlogController;
use App\Http\Controllers\Dashboard\PostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Every route in this file runs inside tenant context: the tenant is
| resolved from the request domain and the database, cache, filesystem
| and queue are switched to that tenant before the route executes.
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('home');
    Route::get('/posts/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::redirect('/dashboard', '/dashboard/posts')->name('dashboard');

        Route::resource('dashboard/posts', PostController::class)
            ->except('show')
            ->names('dashboard.posts');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    require __DIR__.'/auth.php';
});
