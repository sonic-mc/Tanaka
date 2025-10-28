<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\PatientDetail;
use App\Models\Admission;
use App\Models\PatientEvaluation;
use App\Models\Appointment;
use App\Models\PatientProgressReport;
use App\Models\IncidentReport;
use App\Models\User;
use App\Models\Task;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Prescription;
use App\Models\Medication;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Notification;
use App\Models\BillingStatement;
use App\Models\DischargedPatient;
use App\Traits\AuditLogger;
use Carbon\Carbon;
use App\Models\AuditLog;
use App\Models\TherapySession;

class DashboardController extends Controller
{
    use AuditLogger;

    public function index(Request $request)
    {
        $user = auth()->user();
    
        if (! $user) {
            return redirect()->route('login');
        }
    
        // If user has no role assigned, show default dashboard
        if (empty($user->role)) {
            return $this->defaultDashboard();
        }
    
        return match ($user->role) {
            'admin'       => $this->adminDashboard(),
            'psychiatrist'=> $this->psychiatristDashboard($request),
            'nurse'       => $this->nurseDashboard(),
            'clinician'   => $this->clinicianDashboard(),
            default       => $this->defaultDashboard(),
        };
    }
    

    public function adminDashboard()
{
    // Patients
    $patientCount = PatientDetail::count();
    $recentPatients = PatientDetail::latest()->take(5)->get();
    $activePatients = Admission::where('status', 'active')->distinct()->count('patient_id');

    // Staff
    $staffRoles = ['nurse', 'psychiatrist', 'clinician'];
    $staffCount = User::whereIn('role', $staffRoles)->count();
    $recentStaff = User::whereIn('role', $staffRoles)->latest()->take(5)->get();

    // Tasks & Incidents
    $pendingTasks = Task::where('status', 'pending')->count();
    $criticalIncidents = IncidentReport::where('description', 'like', '%critical%')->count();
    $recentIncidents = IncidentReport::latest()->take(5)->get();

    // Appointments
    $upcomingAppointments = Appointment::whereDate('date', '>=', now())->orderBy('date')->take(5)->get();
    $todayAppointments = Appointment::whereDate('date', today())->count();

    // Medications & Prescriptions
    $activePrescriptions = Prescription::where('status', 'active')->count();
    $lowStockMedications = Medication::where('quantity', '<=', 10)->get();

    // Billing & Payments
    $unpaidInvoices = Invoice::where('status', 'unpaid')->count();
    $recentPayments = InvoicePayment::latest()->take(5)->get();
    $paymentCount = InvoicePayment::whereNotNull('paid_at')->count();
    $totalRevenue = (float) InvoicePayment::whereNotNull('paid_at')->sum('amount');

    // Notifications
    $unreadNotifications = Notification::whereNull('read_at')->count();
    $recentNotifications = Notification::latest()->take(5)->get();

    // Clinical Metrics
    $therapySessionCount = TherapySession::count();
    $progressReportCount = PatientProgressReport::count();
    $dischargeCount = DischargedPatient::count();
    $billingCount = BillingStatement::count();

    // Users without roles (null or empty string)
    $usersNoRoles = User::whereNull('role')->orWhere('role', '')->get();
    $noRoleCount = $usersNoRoles->count();

    // Chart Data: Last 6 months
    $months = collect(range(0, 5))->map(fn ($i) => Carbon::now()->startOfMonth()->subMonths(5 - $i));
    $startDate = $months->first()->toDateString();
    $chartKeys = $months->map(fn ($d) => $d->format('Y-m'))->all();
    $chartLabels = $months->map(fn ($d) => $d->format('M Y'))->all();

    $invoiceMonthly = Invoice::selectRaw("DATE_FORMAT(issue_date, '%Y-%m') as ym, SUM(amount) as total")
        ->whereDate('issue_date', '>=', $startDate)
        ->groupBy('ym')->orderBy('ym')->pluck('total', 'ym');

    $paymentMonthly = InvoicePayment::selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as ym, SUM(amount) as total")
        ->whereNotNull('paid_at')->whereDate('paid_at', '>=', $startDate)
        ->groupBy('ym')->orderBy('ym')->pluck('total', 'ym');

    $chart = [
        'labels'   => $chartLabels,
        'invoices' => array_map(fn ($ym) => (float) ($invoiceMonthly[$ym] ?? 0), $chartKeys),
        'payments' => array_map(fn ($ym) => (float) ($paymentMonthly[$ym] ?? 0), $chartKeys),
    ];

    // Audit Logs
    $auditLogs = AuditLog::with('user')->orderByDesc('timestamp')->limit(5)->get();
    $notificationCount = AuditLog::where('timestamp', '>=', now()->subDay())->count();

    return view('admin.dashboard', compact(
        'patientCount', 'activePatients', 'recentPatients',
        'staffCount', 'recentStaff', 'pendingTasks', 'criticalIncidents', 'recentIncidents',
        'upcomingAppointments', 'todayAppointments',
        'activePrescriptions', 'lowStockMedications',
        'unpaidInvoices', 'recentPayments', 'paymentCount', 'totalRevenue',
        'unreadNotifications', 'recentNotifications',
        'therapySessionCount', 'progressReportCount', 'dischargeCount', 'billingCount',
        'noRoleCount', 'chart', 'auditLogs', 'notificationCount'
    ));
}


    public function psychiatristDashboard(Request $request)
    {
        $user = Auth::user();
       

        $patientCount = PatientDetail::count();
        $therapySessionCount = \App\Models\TherapySession::count();
        $progressReportCount = PatientProgressReport::count();
        $dischargeCount = DischargedPatient::count();
        $billingCount = BillingStatement::count();
       
        $incidentsCount = IncidentReport::count();
        $evaluationCount = PatientEvaluation::count();
        $admissionsCount = Admission::count();

        $progressDistribution = app(\App\Services\ProgressAnalyticsService::class)->distributionForUser($user, 30);
            // Get IDs of evaluated patients
        $evaluatedPatientIds = PatientEvaluation::pluck('patient_id')->unique();

        // Count patients who haven't been evaluated
        $unevaluatedCount = PatientDetail::whereNotIn('id', $evaluatedPatientIds)->count();

         // Define how "newly registered" is determined (e.g., last 7 days)
         $windowDays = (int) ($request->get('new_patients_days', 7));
         $windowStart = now()->subDays($windowDays);
         
         // Recently registered but *not yet evaluated* patients
         $newPatients = PatientDetail::query()
             ->where('created_at', '>=', $windowStart)
             ->whereDoesntHave('evaluations')  // no evaluations yet
             ->orderByDesc('created_at')
             ->limit(10)
             ->get();
         
         // Total count of unevaluated patients (you can also filter by window if preferred)
         $totalUnevaluatedCount = PatientDetail::whereDoesntHave('evaluations')->count();
         
         $notifications = [
             'new_patients' => $newPatients,
             'pending_unevaluated_count' => $totalUnevaluatedCount,
             'window_days' => $windowDays,
         ];
         
 

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

        

        $incidentsCount = IncidentReport::where('reported_by', $user->id)->count();
        $progressreportCount = PatientProgressReport::count();
        $therapySessionsCount = TherapySession::count();

        

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
            'patientsCount',
            'progressreportCount',
            'therapySessionsCount',
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

         // Fetch the 2 most recently created patients from patient_details table
         $recentPatients = PatientDetail::orderByDesc('created_at')->take(2)->get();

          // Count invoices where status is "unpaid" or "partially_paid"
        $pendingPaymentsToday = Invoice::whereIn('status', ['unpaid', 'partially_paid'])->count();
        $therapySessionsCount = TherapySession::count();

        $recentAdmissions = Admission::with('patient')
        ->where('status', 'active')
        ->orderByDesc('admission_date')
        ->take(2)
        ->get();

        return view('clinician.dashboard', compact(
            'newPatientsToday',
            'activeAdmissions',
            'dischargesToday',
            'pendingEvaluations',
            'recentAdmissions',
            'recentPatients',
            'pendingPaymentsToday',
            'therapySessionsCount'
        ));
    }

    protected function defaultDashboard()
    {
        return view('dashboard.default');
    }
}
