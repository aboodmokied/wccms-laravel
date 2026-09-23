<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\RegistrationRequestController;
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

    // Registration Requests Routes
    Route::get('/requests', [RegistrationRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/my', [RegistrationRequestController::class, 'myRequests'])->name('requests.my');
    Route::get('/requests/create', [RegistrationRequestController::class, 'create'])->name('requests.create');
    Route::get('/requests/search-patient', [RegistrationRequestController::class, 'searchPatient'])->name('requests.search_patient');
    Route::post('/requests', [RegistrationRequestController::class, 'store'])->name('requests.store');
    Route::get('/requests/{requestModel}', [RegistrationRequestController::class, 'show'])->name('requests.show');
    Route::post('/requests/{requestModel}/visit', [RegistrationRequestController::class, 'startVisit'])->name('requests.visit');
    Route::post('/requests/{requestModel}/accept', [RegistrationRequestController::class, 'accept'])->name('requests.accept');
    Route::post('/requests/{requestModel}/ineligible', [RegistrationRequestController::class, 'markIneligible'])->name('requests.ineligible');
    Route::post('/requests/{requestModel}/cancel', [RegistrationRequestController::class, 'cancel'])->name('requests.cancel');

    // Registrations Routes
    Route::get('/registrations', function () {
        return view('registrations.index');
    })->name('registrations.index');

    Route::get('/registrations/create', function () {
        return view('registrations.create');
    })->name('registrations.create');

    // Daily & Scheduled Reviews
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
