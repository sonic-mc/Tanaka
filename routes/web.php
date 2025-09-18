<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CareLevelController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\ProgressReportController;
use App\Http\Controllers\DischargeController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\IncidentReportController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\BillingStatementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\DashboardController;



Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    return view('welcome');
});

Route::middleware(['auth'])->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile/view', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/profile', [ProfileController::class, 'update']);
});

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::resource('care-levels', CareLevelController::class);
    Route::resource('patients', PatientController::class);
    Route::resource('evaluations', EvaluationController::class);
    Route::resource('progress-reports', ProgressReportController::class);
    Route::resource('discharges', DischargeController::class);
    Route::resource('tasks', TaskController::class);
    Route::resource('incident-reports', IncidentReportController::class);
    Route::resource('payments', PaymentController::class);
    Route::resource('billing-statements', BillingStatementController::class);
    Route::resource('notifications', NotificationController::class);
    Route::resource('audit-logs', AuditLogController::class);
    Route::resource('backups', BackupController::class);
});


// Nurse & Psychiatrist Routes
Route::middleware(['auth', 'role:psychiatrist,nurse'])->group(function () {

    // Patients
    Route::get('/patients', [PatientController::class, 'index'])->name('patients.index');
    Route::get('/patients/{patient}', [PatientController::class, 'show'])->name('patients.show');

    // Evaluations
    Route::get('/evaluations', [EvaluationController::class, 'index'])->name('evaluations.index');
    Route::get('/evaluations/{evaluation}', [EvaluationController::class, 'show'])->name('evaluations.show');
    Route::get('/evaluations/create', [EvaluationController::class, 'create'])->name('evaluations.create');
    Route::post('/evaluations', [EvaluationController::class, 'store'])->name('evaluations.store');

    // Progress Reports
    Route::get('/progress-reports', [ProgressReportController::class, 'index'])->name('progress-reports.index');
    Route::get('/progress-reports/{report}', [ProgressReportController::class, 'show'])->name('progress-reports.show');
    Route::get('/progress-reports/create', [ProgressReportController::class, 'create'])->name('progress-reports.create');
    Route::post('/progress-reports', [ProgressReportController::class, 'store'])->name('progress-reports.store');

    // Therapy Sessions
    Route::get('/therapy-sessions', [TherapySessionController::class, 'index'])->name('therapy-sessions.index');
    Route::get('/therapy-sessions/{session}', [TherapySessionController::class, 'show'])->name('therapy-sessions.show');
    Route::get('/therapy-sessions/create', [TherapySessionController::class, 'create'])->name('therapy-sessions.create');
    Route::post('/therapy-sessions', [TherapySessionController::class, 'store'])->name('therapy-sessions.store');

    // Incidents
    Route::get('/incidents', [IncidentController::class, 'index'])->name('incidents.index');
    Route::get('/incidents/{incident}', [IncidentController::class, 'show'])->name('incidents.show');
    Route::get('/incidents/create', [IncidentController::class, 'create'])->name('incidents.create');
    Route::post('/incidents', [IncidentController::class, 'store'])->name('incidents.store');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-read/{notification}', [NotificationController::class, 'markRead'])->name('notifications.markRead');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('roles-permissions', [DashboardController::class, 'index'])->name('admin.roles-permissions');
    Route::post('roles', [DashboardController::class, 'storeRole'])->name('admin.roles.store');
    Route::post('permissions', [DashboardController::class, 'storePermission'])->name('admin.permissions.store');
    Route::post('roles/assign-permissions', [DashboardController::class, 'assignPermissions'])->name('admin.roles.assign-permissions');
    Route::post('users/assign-role', [DashboardController::class, 'assignRole'])->name('admin.users.assign-role');
});

