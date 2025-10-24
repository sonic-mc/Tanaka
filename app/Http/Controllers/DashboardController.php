<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\PatientDetail;
use App\Models\Admission;
use App\Models\PatientEvaluation;
use App\Models\Appointment;
use App\Models\ProgressReport;
use App\Models\IncidentReport;
use App\Models\User;
use App\Models\Task;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Prescription;
use App\Models\Medication;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Notification;
use App\Models\BillingStatement;
use App\Models\Discharge;
use App\Traits\AuditLogger;
use Carbon\Carbon;
use App\Models\AuditLog;

class DashboardController extends Controller
{
    use AuditLogger;

    public function index()
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        // If user has no roles, default dashboard
        if ($user->roles->isEmpty()) {
            return $this->defaultDashboard();
        }

        $role = $user->roles->first()->name;

        switch ($role) {
            case 'admin':
                return $this->adminDashboard();
            case 'psychiatrist':
                return $this->psychiatristDashboard();
            case 'nurse':
                return $this->nurseDashboard();
            case 'clinician':
                return $this->clinicianDashboard();
            default:
                return $this->defaultDashboard();
        }
    }

    protected function adminDashboard()
    {
        // Patients now come from patient_details
        $patientCount = PatientDetail::count();

        // Staff count by role
        $staffCount = User::whereIn('role', ['nurse', 'psychiatrist', 'doctor'])->count();

        $pendingTasks = Task::where('status', 'pending')->count();
        $criticalIncidents = IncidentReport::where('description', 'like', '%critical%')->count();

        // Recent patients (from patient_details)
        $recentPatients = PatientDetail::latest()->take(5)->get();

        // "Active patients" -> patients with active admissions (distinct patients)
        $activePatients = Admission::where('status', 'active')->distinct('patient_id')->count();

        // Staff
        $recentStaff = User::whereIn('role', ['nurse', 'psychiatrist', 'doctor'])
            ->latest()
            ->take(5)
            ->get();

        // Appointments
        $upcomingAppointments = Appointment::whereDate('date', '>=', now())
            ->orderBy('date', 'asc')
            ->take(5)
            ->get();

        $todayAppointments = Appointment::whereDate('date', today())->count();

        // Medications / Prescriptions
        $activePrescriptions = Prescription::where('status', 'active')->count();
        $lowStockMedications = Medication::where('quantity', '<=', 10)->get();

        // Incidents
        $recentIncidents = IncidentReport::latest()->take(5)->get();

        // Billing & Payments
        $unpaidInvoices = Invoice::where('status', 'unpaid')->count();
        $recentPayments = Payment::latest()->take(5)->get();

        // Notifications
        $unreadNotifications = Notification::whereNull('read_at')->count();
        $recentNotifications = Notification::latest()->take(5)->get();

        $therapySessionCount = \App\Models\TherapySession::count();
        $progressReportCount = ProgressReport::count();
        $dischargeCount = Discharge::count();
        $billingCount = BillingStatement::count();

        // Count of payments received
        $receivedPaymentsCount = Payment::whereNotNull('paid_at')->count();

        // Roles & permissions and users
        $roles = Role::all();
        $permissions = Permission::all();

        // Users with roles/permissions (full set)
        $usersWithRoles = User::with('roles', 'permissions')->get();

        // Users without any roles
        $usersNoRoles = User::doesntHave('roles')->get();
        $noRoleCount = $usersNoRoles->count();

        // Financial KPIs
        $totalRevenue = Payment::whereNotNull('paid_at')->sum('amount');

        // Last 6 months series
        $months = collect(range(0, 5))
            ->map(fn ($i) => Carbon::now()->startOfMonth()->subMonths(5 - $i));

        $invoiceMonthly = Invoice::selectRaw("DATE_FORMAT(issue_date, '%Y-%m') as ym, SUM(amount) as total")
            ->whereDate('issue_date', '>=', $months->first()->toDateString())
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym');

        $paymentMonthly = Payment::selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as ym, SUM(amount) as total")
            ->whereNotNull('paid_at')
            ->whereDate('paid_at', '>=', $months->first()->toDateString())
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym');

        $chartLabels = $months->map(fn ($d) => $d->format('M Y'))->all();
        $chartKeys   = $months->map(fn ($d) => $d->format('Y-m'))->all();

        $invoicesSeries = array_map(fn ($ym) => (float) ($invoiceMonthly[$ym] ?? 0), $chartKeys);
        $paymentsSeries = array_map(fn ($ym) => (float) ($paymentMonthly[$ym] ?? 0), $chartKeys);

        $chart = [
            'labels'   => $chartLabels,
            'invoices' => $invoicesSeries,
            'payments' => $paymentsSeries,
        ];

        // Recent audit logs
        $auditLogs = AuditLog::with('user')
            ->orderByDesc('timestamp')
            ->limit(5)
            ->get();

        // Badge count: new audit entries in last 24h
        $notificationCount = AuditLog::where('timestamp', '>=', now()->subDay())->count();

        return view('admin.dashboard', [
            'patientCount' => $patientCount,
            'activePatients' => $activePatients,
            'recentPatients' => $recentPatients,
            'staffCount' => $staffCount,
            'recentStaff' => $recentStaff,
            'pendingTasks' => $pendingTasks,
            'criticalIncidents' => $criticalIncidents,
            'recentIncidents' => $recentIncidents,
            'upcomingAppointments' => $upcomingAppointments,
            'todayAppointments' => $todayAppointments,
            'activePrescriptions' => $activePrescriptions,
            'lowStockMedications' => $lowStockMedications,
            'unpaidInvoices' => $unpaidInvoices,
            'recentPayments' => $recentPayments,
            'unreadNotifications' => $unreadNotifications,
            'recentNotifications' => $recentNotifications,
            'therapySessionCount' => $therapySessionCount,
            'progressReportCount' => $progressReportCount,
            'dischargeCount' => $dischargeCount,
            'billingCount' => $billingCount,
            'paymentCount' => $receivedPaymentsCount,
            'notificationCount' => $notificationCount,
            'roles' => $roles,
            'permissions' => $permissions,
            'users' => $usersWithRoles,
            'noRoleCount' => $noRoleCount,
            'totalRevenue' => $totalRevenue,
            'chart' => $chart,
            'auditLogs' => $auditLogs,
        ]);
    }

    protected function psychiatristDashboard()
    {
        $user = Auth::user();
        $notificationService = app(\App\Services\DashboardNotificationService::class);

        $patientCount = PatientDetail::count();
        $therapySessionCount = \App\Models\TherapySession::count();
        $progressReportCount = ProgressReport::count();
        $dischargeCount = Discharge::count();
        $billingCount = BillingStatement::count();
       

        $notificationCount = $user->unreadNotifications()->count();
        $notifications = $notificationService->getForUser($user, 10);

        $incidentsCount = IncidentReport::count();
        $evaluationCount = PatientEvaluation::count();
        $admissionsCount = Admission::count();

        $progressDistribution = app(\App\Services\ProgressAnalyticsService::class)->distributionForUser($user, 30);
            // Get IDs of evaluated patients
        $evaluatedPatientIds = PatientEvaluation::pluck('patient_id')->unique();

        // Count patients who haven't been evaluated
        $unevaluatedCount = PatientDetail::whereNotIn('id', $evaluatedPatientIds)->count();

        return view('psychiatrist.dashboard', compact(
            'patientCount',
            'therapySessionCount',
            'progressReportCount',
            'dischargeCount',
            'billingCount',
            'unevaluatedCount',
            'incidentsCount',
            'evaluationCount',
            'admissionsCount',
            'notificationCount',
            'notifications',
            'progressDistribution'
        ));
    }

    protected function nurseDashboard()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        // Count distinct patients this nurse is assigned to through active admissions
        $assignedPatients = Admission::where('status', 'active')
            ->whereHas('nurseAssignments', fn($q) => $q->where('nurse_id', $user->id))
            ->distinct('patient_id')
            ->count();

        $patientsCount = $assignedPatients;
        $admissionsCount = \App\Models\Admission::count();

        $reportsCount = ProgressReport::whereDate('created_at', $today)
            ->where('reported_by', $user->id)
            ->count();

        $incidentsCount = IncidentReport::where('reported_by', $user->id)->count();

        $pendingReports = ProgressReport::where('reported_by', $user->id)
            ->whereDate('created_at', '<', now()->subDays(30))
            ->count();

        // Last 5 assigned admissions with patient details
        $assignedAdmissions = Admission::where('status', 'active')
            ->whereHas('nurseAssignments', fn($q) => $q->where('nurse_id', $user->id))
            ->with('patient')
            ->orderByDesc('admission_date')
            ->limit(5)
            ->get();

        // Optional calendar events array (pass if you want to light up your calendar)
        $calendarEvents = []; // ['2025-10-03' => ['Workshop']]

        return view('nurse.dashboard', compact(
            'assignedPatients',
            'pendingReports',
            'patientsCount',
            'reportsCount',
            'incidentsCount',
            'assignedAdmissions',
            'calendarEvents',
            'admissionsCount'

        ));
    }

    public function clinicianDashboard()
    {
        $today = Carbon::today();

        $newPatientsToday = PatientDetail::whereDate('created_at', $today)->count();
        $activeAdmissions = Admission::where('status', 'active')->with('patient')->get();
        $dischargesToday = Admission::where('status', 'discharged')->whereDate('updated_at', $today)->with('patient')->get();
        $pendingEvaluations = PatientEvaluation::whereDate('evaluation_date', $today)->with('patient')->get();

        return view('clinician.dashboard', compact(
            'newPatientsToday',
            'activeAdmissions',
            'dischargesToday',
            'pendingEvaluations'
        ));
    }

    protected function defaultDashboard()
    {
        return view('dashboard.default');
    }
}
