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
    Route::resource('incidents', IncidentController::class);

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
