<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Phase 3 Auth & RBAC Protected Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/login', function () {
    $loginUrl = config('services.hospital_auth.login_url', 'http://hospital.local/login');

    return redirect()->away($loginUrl);
})->name('login');

Route::post('/logout', LogoutController::class)->name('logout');

Route::middleware(['hospital.jwt'])->group(function () {

    Route::get('/', function () {
        return view('dashboard');
    });

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/requests', function () {
        return view('requests.index');
    })->name('requests.index');

    Route::get('/registrations', function () {
        return view('registrations.index');
    })->name('registrations.index');

    Route::get('/registrations/create', function () {
        return view('registrations.create');
    })->name('registrations.create');

    Route::get('/reviews/daily', function () {
        return view('reviews.daily');
    })->name('reviews.daily');

    Route::get('/reviews/scheduled', function () {
        return view('reviews.scheduled');
    })->name('reviews.scheduled');

    Route::get('/reports/history', function () {
        return view('reports.history');
    })->name('reports.history');

    // Admin-Only Routes
    Route::middleware(['role:ADMIN'])->group(function () {
        Route::get('/admin', function () {
            return view('admin.index');
        })->name('admin.index');

        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    });
});
