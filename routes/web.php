<?php

use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DiagnosisController;
use App\Http\Controllers\Admin\DressingController;
use App\Http\Controllers\Admin\MedicationController;
use App\Http\Controllers\Admin\PmhController;
use App\Http\Controllers\Admin\ProcedureController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WoundTypeController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\MockHospitalAuthController;
use App\Http\Controllers\DailyReviewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientMedicalRecordController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\RegistrationRequestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScheduledReviewController;
use App\Http\Controllers\WoundPhotoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Phase 3 Auth & RBAC Protected Web Routes
|--------------------------------------------------------------------------
*/

// Virtual Hospital SSO Routes (for local testing & dev)
Route::get('/mock-hospital-auth', [MockHospitalAuthController::class, 'index'])->name('mock_hospital_auth.index');
Route::post('/mock-hospital-auth/login', [MockHospitalAuthController::class, 'login'])->name('mock_hospital_auth.login');
Route::post('/mock-hospital-auth/logout', [MockHospitalAuthController::class, 'logout'])->name('mock_hospital_auth.logout');

Route::get('/login', function () {
    $loginUrl = config('services.hospital_auth.login_url', route('mock_hospital_auth.index'));

    if (str_starts_with($loginUrl, '/') || str_contains($loginUrl, request()->getHost())) {
        return redirect($loginUrl);
    }

    return redirect()->away($loginUrl);
})->name('login');

Route::post('/logout', LogoutController::class)->name('logout');

Route::middleware(['hospital.jwt'])->group(function () {

    Route::get('/', [DashboardController::class, 'index']);

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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

    // Registrations Routes (Phase 5 & 6)
    // Patients (Medical Records)
    Route::get('/patients', [PatientMedicalRecordController::class, 'index'])->name('patients.index');
    Route::get('/patients/{patient}', [PatientMedicalRecordController::class, 'show'])->name('patients.show');
    Route::get('/registrations', [RegistrationController::class, 'index'])->name('registrations.index');
    Route::get('/registrations/{registration}', [RegistrationController::class, 'show'])->name('registrations.show');
    Route::put('/registrations/{registration}', [RegistrationController::class, 'update'])->name('registrations.update');
    Route::post('/registrations/{registration}/diagnoses', [RegistrationController::class, 'storeDiagnosis'])->name('registrations.diagnoses.store');
    Route::post('/registrations/{registration}/discharge', [RegistrationController::class, 'discharge'])->name('registrations.discharge');

    // Daily Review Routes (Phase 6)
    // Daily Review Routes (Workspace & Forms)
    Route::get('/registrations/{registration}/daily-reviews', [DailyReviewController::class, 'index'])->name('registrations.daily-reviews.index');
    Route::get('/registrations/{registration}/daily-reviews/create', [DailyReviewController::class, 'create'])->name('registrations.daily-reviews.create');
    Route::post('/registrations/{registration}/daily-reviews', [DailyReviewController::class, 'store'])->name('registrations.daily-reviews.store');

    // Daily & Scheduled Reviews
    Route::get('/reviews/daily', function () {
        return redirect()->route('reviews.daily.pending');
    })->name('reviews.daily');
    Route::get('/reviews/daily/pending', [DailyReviewController::class, 'pendingIndex'])->name('reviews.daily.pending');
    Route::get('/reviews/daily/completed', [DailyReviewController::class, 'completedIndex'])->name('reviews.daily.completed');

    Route::get('/reviews/scheduled', [ScheduledReviewController::class, 'index'])->name('reviews.scheduled');
    Route::post('/reviews/scheduled/{scheduledReview}/fulfill', [ScheduledReviewController::class, 'startFulfillment'])->name('reviews.scheduled.fulfill');
    Route::put('/reviews/scheduled/{scheduledReview}/reschedule', [ScheduledReviewController::class, 'reschedule'])->name('reviews.scheduled.reschedule');
    Route::post('/reviews/scheduled/{scheduledReview}/cancel', [ScheduledReviewController::class, 'cancel'])->name('reviews.scheduled.cancel');

    Route::get('/wound-photos/{photo}', [WoundPhotoController::class, 'show'])->name('wound_photos.show');

    Route::get('/reports/history', [ReportController::class, 'history'])->name('reports.history');
    Route::get('/reports/history/{patient}/export', [ReportController::class, 'exportCsv'])->name('reports.history.export');

    // Admin-Only Routes
    Route::middleware(['role:ADMIN'])->group(function () {
        Route::get('/admin', function () {
            return view('admin.index');
        })->name('admin.index');

        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');

        // Master Data Administration Routes
        Route::get('/admin/departments', [DepartmentController::class, 'index'])->name('admin.departments.index');
        Route::post('/admin/departments', [DepartmentController::class, 'store'])->name('admin.departments.store');
        Route::put('/admin/departments/{department}', [DepartmentController::class, 'update'])->name('admin.departments.update');
        Route::patch('/admin/departments/{department}/toggle', [DepartmentController::class, 'toggleActive'])->name('admin.departments.toggle');

        Route::get('/admin/pmh', [PmhController::class, 'index'])->name('admin.pmh.index');
        Route::post('/admin/pmh', [PmhController::class, 'store'])->name('admin.pmh.store');
        Route::put('/admin/pmh/{pmh}', [PmhController::class, 'update'])->name('admin.pmh.update');
        Route::patch('/admin/pmh/{pmh}/toggle', [PmhController::class, 'toggleActive'])->name('admin.pmh.toggle');

        Route::get('/admin/diagnoses', [DiagnosisController::class, 'index'])->name('admin.diagnoses.index');
        Route::post('/admin/diagnoses', [DiagnosisController::class, 'store'])->name('admin.diagnoses.store');
        Route::put('/admin/diagnoses/{diagnosis}', [DiagnosisController::class, 'update'])->name('admin.diagnoses.update');
        Route::patch('/admin/diagnoses/{diagnosis}/toggle', [DiagnosisController::class, 'toggleActive'])->name('admin.diagnoses.toggle');

        Route::get('/admin/wound-types', [WoundTypeController::class, 'index'])->name('admin.wound_types.index');
        Route::post('/admin/wound-types', [WoundTypeController::class, 'store'])->name('admin.wound_types.store');
        Route::put('/admin/wound-types/{woundType}', [WoundTypeController::class, 'update'])->name('admin.wound_types.update');
        Route::patch('/admin/wound-types/{woundType}/toggle', [WoundTypeController::class, 'toggleActive'])->name('admin.wound_types.toggle');

        Route::get('/admin/dressings', [DressingController::class, 'index'])->name('admin.dressings.index');
        Route::post('/admin/dressings', [DressingController::class, 'store'])->name('admin.dressings.store');
        Route::put('/admin/dressings/{dressing}', [DressingController::class, 'update'])->name('admin.dressings.update');
        Route::patch('/admin/dressings/{dressing}/toggle', [DressingController::class, 'toggleActive'])->name('admin.dressings.toggle');

        Route::get('/admin/medications', [MedicationController::class, 'index'])->name('admin.medications.index');
        Route::post('/admin/medications', [MedicationController::class, 'store'])->name('admin.medications.store');
        Route::put('/admin/medications/{medication}', [MedicationController::class, 'update'])->name('admin.medications.update');
        Route::patch('/admin/medications/{medication}/toggle', [MedicationController::class, 'toggleActive'])->name('admin.medications.toggle');

        Route::get('/admin/procedures', [ProcedureController::class, 'index'])->name('admin.procedures.index');
        Route::post('/admin/procedures', [ProcedureController::class, 'store'])->name('admin.procedures.store');
        Route::put('/admin/procedures/{procedure}', [ProcedureController::class, 'update'])->name('admin.procedures.update');
        Route::patch('/admin/procedures/{procedure}/toggle', [ProcedureController::class, 'toggleActive'])->name('admin.procedures.toggle');
    });
});
