<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CareLevelController;
use App\Http\Controllers\PatientController;

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
use App\Http\Controllers\AdmissionsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\DashboardNotificationController;

use App\Http\Controllers\PatientEvaluationController;
use App\Http\Controllers\NurseAssignmentController;
use App\Http\Controllers\ConsultationFeeController;
use App\Http\Controllers\GradingController;
use App\Http\Controllers\FeedbackController;

// Note: The 'role' middleware alias is bound to App\Http\Middleware\RoleMiddleware
// in AppServiceProvider::boot using $router->aliasMiddleware('role', ...).

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
    Route::resource('audit-logs', AuditLogController::class);
    Route::resource('backups', BackupController::class);
    Route::resource('notifications', NotificationController::class);
});

// Nurse & Psychiatrist routes using custom role middleware
Route::middleware(['auth', 'role:psychiatrist|nurse'])->group(function () {
    Route::resource('progress-reports', ProgressReportController::class);
    Route::resource('therapy-sessions', TherapySessionController::class);
    Route::resource('incidents', IncidentReportController::class);

    Route::post('/notifications/mark-read/{notification}', [NotificationController::class, 'markRead'])
        ->name('notifications.markRead');
});

// Admin routes using custom role middleware
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('roles-permissions', [DashboardController::class, 'index'])->name('admin.roles-permissions');
    Route::post('roles', [DashboardController::class, 'storeRole'])->name('admin.roles.store');
    Route::post('permissions', [DashboardController::class, 'storePermission'])->name('admin.permissions.store');
    Route::post('roles/assign-permissions', [DashboardController::class, 'assignPermissions'])->name('admin.roles.assign-permissions');
    Route::post('users/assign-role', [DashboardController::class, 'assignRole'])->name('admin.users.assign-role');
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
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

// Database notifications actions
Route::post('/dashboard/notifications/{notification}/read', [DashboardNotificationController::class, 'markAsRead'])
    ->name('dashboard.notifications.read');
Route::post('/dashboard/notifications/read-all', [DashboardNotificationController::class, 'markAllAsRead'])
    ->name('dashboard.notifications.markAll');

Route::post('patients/{patient}/assign-nurse', [PatientController::class, 'assignNurse'])->name('patients.assign-nurse');
Route::get('patients/{patient}/admit', [PatientController::class, 'admit'])->name('patients.admit');

Route::middleware(['auth', 'role:psychiatrist|nurse'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/export', [ReportController::class, 'export'])->name('reports.export');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/users', [UsersController::class, 'index'])->name('users.index');
    Route::post('/users/{id}/assign-role', [UsersController::class, 'assignRole'])->name('users.assignRole');
});

Route::resource('evaluations', PatientEvaluationController::class);
Route::post('evaluations/{id}/restore', [PatientEvaluationController::class, 'restore'])->name('evaluations.restore');
Route::delete('evaluations/{id}/force-delete', [PatientEvaluationController::class, 'forceDelete'])->name('evaluations.force-delete');

// Patient lookup endpoint (AJAX for evaluation form)
Route::get('patients/lookup', [PatientController::class, 'lookup'])->name('patients.lookup');

Route::resource('admissions', AdmissionsController::class);
Route::patch('admissions/{admission}/discharge', [AdmissionsController::class, 'discharge'])->name('admissions.discharge');

Route::resource('patients', PatientController::class);
// Additional routes for soft deletes
Route::post('patients/{id}/restore', [PatientController::class, 'restore'])->name('patients.restore');
Route::delete('patients/{id}/force-delete', [PatientController::class, 'forceDelete'])->name('patients.force-delete');

Route::get('/grading', [GradingController::class, 'index'])->name('grading.index');
Route::get('/grading/{evaluation}', [GradingController::class, 'show'])->name('grading.show');
Route::post('/grading/{evaluation}/recalculate', [GradingController::class, 'recalculate'])->name('grading.recalculate');

Route::get('nurse-assignments', [NurseAssignmentController::class, 'index'])->name('nurse-assignments.index');
Route::get('nurse-assignments/create', [NurseAssignmentController::class, 'create'])->name('nurse-assignments.create');
Route::get('nurse-assignments/{id}/edit', [NurseAssignmentController::class, 'edit'])->name('nurse-assignments.edit');
Route::post('nurse-assignments', [NurseAssignmentController::class, 'store'])->name('nurse-assignments.store');
Route::delete('nurse-assignments/{id}', [NurseAssignmentController::class, 'destroy'])->name('nurse-assignments.destroy');

Route::prefix('admin')->name('admin.')->group(function () {
    // If you intended BillingStatementController, adjust accordingly
    // Route::resource('billings', BillingStatementController::class);
    // Otherwise, ensure BillingController is imported at the top if it exists.
    Route::resource('billings', \App\Http\Controllers\BillingController::class);
});

// Progress reports (admin prefixed, no role guard here; relies on controller for checks)
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    // resourceful routes
    Route::resource('progress-reports', \App\Http\Controllers\ProgressReportController::class)
        ->parameters(['progress-reports' => 'id'])
        ->except(['create']); // we expose create separately if desired

    // specialized endpoints
    Route::get('progress-reports/create', [\App\Http\Controllers\ProgressReportController::class, 'create'])->name('progress-reports.create');
    Route::get('progress-reports/{patientId}/compare', [\App\Http\Controllers\ProgressReportController::class, 'compare'])->name('progress-reports.compare');
    Route::get('progress-reports/{patientId}/export', [\App\Http\Controllers\ProgressReportController::class, 'exportCsv'])->name('progress-reports.export');
});

Route::resource('care_levels', CareLevelController::class);

// Create/store discharge for a specific admission
Route::get('/admissions/{admission}/discharge', [DischargeController::class, 'create'])
    ->name('admissions.discharge.create');
Route::post('/admissions/{admission}/discharge', [DischargeController::class, 'store'])
    ->name('admissions.discharge.store');

// Manage discharges
Route::resource('discharges', DischargeController::class)->except(['create', 'store']);

Route::middleware('auth')->group(function () {
    Route::get('/feedback/create', [FeedbackController::class, 'create'])->name('feedback.create');
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');

    // Admin listing — guarded inside controller by role check
    Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
});

Route::resource('consultation_fees', ConsultationFeeController::class);
// download by invoice id -> will look for storage/app/public/invoices/invoice_{invoice_number}.pdf
Route::get('invoices/{invoice}/download', function ($invoiceId) {
    $invoice = \App\Models\Invoice::findOrFail($invoiceId);

    $filename = 'invoice_' . $invoice->invoice_number . '.pdf';
    $path = storage_path('app/public/invoices/' . $filename);

    if (!file_exists($path)) {
        abort(404, 'Invoice file not found.');
    }

    return response()->download($path, $filename, [
        'Content-Type' => 'application/pdf',
    ]);
})->name('invoices.download')->middleware('auth');

// Include billing routes
require __DIR__ . '/billing.php';

require __DIR__ . '/admin.php';
