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
use App\Http\Controllers\TherapySessionController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\UsersController;


// Homepage
Route::get('/', function () {
    return Auth::check() ? view('welcome') : redirect()->route('login');
});

// Dashboard for all authenticated users
Route::middleware(['auth'])->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile/view', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/profile', [ProfileController::class, 'update']);
});

require __DIR__.'/auth.php';

// Routes accessible to all logged-in users
Route::middleware(['auth'])->group(function () {
    Route::resource('care-levels', CareLevelController::class);
    Route::resource('tasks', TaskController::class);
    Route::resource('payments', PaymentController::class);
    Route::resource('billing-statements', BillingStatementController::class);
    Route::resource('audit-logs', AuditLogController::class);
    Route::resource('backups', BackupController::class);
    Route::resource('notifications', NotificationController::class);
});

// Nurse & Psychiatrist routes using Spatie's role middleware
Route::middleware(['auth', 'role:psychiatrist|nurse'])->group(function () {
    Route::resource('patients', PatientController::class);
    Route::resource('evaluations', EvaluationController::class);
    Route::resource('progress-reports', ProgressReportController::class);
    Route::resource('therapy-sessions', TherapySessionController::class);
    Route::resource('incidents', IncidentReportController::class);
    Route::resource('therapy-sessions', TherapySessionController::class);


    Route::post('/notifications/mark-read/{notification}', [NotificationController::class, 'markRead'])
        ->name('notifications.markRead');
});

// Admin routes using Spatie's role middleware
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('roles-permissions', [DashboardController::class, 'index'])->name('admin.roles-permissions');
    Route::post('roles', [DashboardController::class, 'storeRole'])->name('admin.roles.store');
    Route::post('permissions', [DashboardController::class, 'storePermission'])->name('admin.permissions.store');
    Route::post('roles/assign-permissions', [DashboardController::class, 'assignPermissions'])->name('admin.roles.assign-permissions');
    Route::post('users/assign-role', [DashboardController::class, 'assignRole'])->name('admin.users.assign-role');
});



Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Main user management dashboard
    Route::get('users', [UserController::class, 'index'])->name('users.index');

    // Create user
    Route::post('users', [UserController::class, 'store'])->name('users.store');

    // Edit user
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');

    // Delete user
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Deactivate user
    Route::post('users/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');

    // Update role
    Route::post('users/update-role', [UserController::class, 'updateRole'])->name('users.updateRole');

    // Reset password
    Route::post('users/reset-password', [UserController::class, 'resetPassword'])->name('users.resetPassword');

    // Audit logs
    Route::get('users/audit-logs', [UserController::class, 'auditLogs'])->name('users.auditLogs');
});


Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('logs', [AuditLogController::class, 'index'])->name('logs.index');
    Route::get('logs/export', [AuditLogController::class, 'export'])->name('logs.export');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
    Route::post('backups', [BackupController::class, 'store'])->name('backups.store');
    Route::post('backups/{id}/restore', [BackupController::class, 'restore'])->name('backups.restore');
});


Route::middleware(['auth', 'role:admin|finance'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('billing', [BillingStatementController::class, 'index'])->name('billing.index');
    Route::get('billing/{invoice}', [BillingStatementController::class, 'show'])->name('billing.show');
    Route::post('billing/{invoice}/pay', [PaymentController::class, 'store'])->name('billing.pay');
});

Route::post('patients/{patient}/assign-nurse', [PatientController::class, 'assignNurse'])->name('patients.assign-nurse');

Route::get('patients/{patient}/admit', [PatientController::class, 'admit'])->name('patients.admit');
Route::get('patients/{patient}/discharge', [PatientController::class, 'discharge'])->name('patients.discharge');

Route::middleware(['auth', 'role:psychiatrist|nurse'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/export', [ReportController::class, 'export'])->name('reports.export');
});



Route::middleware(['auth'])->group(function () {
    Route::get('/admissions/create', [AdmissionController::class, 'create'])->name('admissions.create');
    Route::post('/admissions', [AdmissionController::class, 'store'])->name('admissions.store');
});


Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/users', [UsersController::class, 'index'])->name('users.index');
    Route::post('/users/{id}/assign-role', [UsersController::class, 'assignRole'])->name('users.assignRole');
});

