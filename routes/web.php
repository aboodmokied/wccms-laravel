<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Phase 2 UI Foundation Skeleton Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/login', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/requests', function () {
    return view('requests.index');
});

Route::get('/registrations', function () {
    return view('registrations.index');
});

Route::get('/registrations/create', function () {
    return view('registrations.create');
});

Route::get('/reviews/daily', function () {
    return view('reviews.daily');
});

Route::get('/reviews/scheduled', function () {
    return view('reviews.scheduled');
});

Route::get('/admin', function () {
    return view('admin.index');
});

Route::get('/reports/history', function () {
    return view('reports.history');
});
