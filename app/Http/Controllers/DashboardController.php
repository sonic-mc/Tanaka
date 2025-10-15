<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Patient;
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
use App\Models\Evaluation;
use App\Models\Staff;
use App\Models\Doctor;
use App\Models\PsychoSocialAssessment;
use App\Models\TherapySession;
use App\Models\Nurse;
use App\Models\Psychiatrist;
use App\Models\DoctorNote;
use App\Models\StaffSchedule;
use App\Models\Bed;
use App\Models\Ward;
use App\Models\Discharge;
use App\Models\VitalSign;
use App\Models\Allergy;
use App\Models\Immunization;
use App\Models\BillingStatement;
use App\Models\MedicalHistory;
use App\Models\LabResult;
use App\Models\RadiologyReport;
use App\Traits\AuditLogger;



class DashboardController extends Controller
{

    use AuditLogger;
    
    public function index()
    {
        $user = Auth::user();
    
        if (! $user) {
            return redirect()->route('login');
        }
    
        // Check if user has no assigned roles
        if ($user->roles->isEmpty()) {
            return $this->defaultDashboard();
        }
    
        // Otherwise, get the user's primary role
        $role = $user->roles->first()->name;
    
        switch ($role) {
            case 'admin':
                return $this->adminDashboard();
    
            case 'psychiatrist':
                return $this->psychiatristDashboard();
    
            case 'nurse':
                return $this->nurseDashboard();
    
            default:
                return $this->defaultDashboard();
        }
    }
    

    protected function adminDashboard()
    {
        // Core metrics
        $patientCount = Patient::count();
        $staffCount = User::whereIn('role', ['nurse', 'psychiatrist', 'doctor'])->count();
        $pendingTasks = Task::where('status', 'pending')->count();
        $criticalIncidents = IncidentReport::where('description', 'like', '%critical%')->count();
    
        // Patients
        $recentPatients = Patient::latest()->take(5)->get(); // last 5 registered
        $activePatients = Patient::where('status', 'active')->count();
    
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
    
        // Notifications & Alerts
        $unreadNotifications = Notification::whereNull('read_at')->count();
        $recentNotifications = Notification::latest()->take(5)->get();

        $therapySessionCount = TherapySession::count();

        $evaluationCount = Evaluation::count();

        $progressReportCount = ProgressReport::count();

        $dischargeCount = Discharge::count();

        $billingCount = BillingStatement::count();

        $paymentCount = Payment::count();

        $notificationCount = Auth::user()->unreadNotifications()->count();
    
        // Roles & permissions
        $roles = Role::all();
        $permissions = Permission::all();
        $users = User::with('roles', 'permissions')->get();
    
        return view('admin.dashboard', compact(
            'patientCount',
            'activePatients',
            'recentPatients',
            'staffCount',
            'recentStaff',
            'pendingTasks',
            'criticalIncidents',
            'recentIncidents',
            'upcomingAppointments',
            'todayAppointments',
            'activePrescriptions',
            'lowStockMedications',
            'unpaidInvoices',
            'recentPayments',
            'unreadNotifications',
            'recentNotifications',
            'therapySessionCount',
            'evaluationCount',
            'progressReportCount',
            'dischargeCount',
            'billingCount',
            'paymentCount',
            'notificationCount',
            'roles',
            'permissions',
            'users'
        ));
    }
    

    protected function psychiatristDashboard()
    {
        $assignedPatients = \App\Models\Patient::whereHas('evaluations', function ($q) {
            $q->where('evaluated_by', Auth::id());
        })->count();

        $patientCount = Patient::count();
        $therapySessionCount = TherapySession::count();
        $evaluationCount = Evaluation::count();

        $progressReportCount = ProgressReport::count();

        $dischargeCount = Discharge::count();

        $billingCount = BillingStatement::count();

        $paymentCount = Payment::count();

        $notificationCount = Auth::user()->unreadNotifications()->count();

        $upcomingEvaluations = \App\Models\Evaluation::where('evaluated_by', Auth::id())
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->count();

        $incidentsCount = IncidentReport::count();
        // $criticalIncidents = IncidentReport::where('severity', 'critical')->count();

        return view('psychiatrist.dashboard', compact('assignedPatients',
         'upcomingEvaluations',
          'patientCount',
           'therapySessionCount',
            'evaluationCount',
            'progressReportCount',
            'dischargeCount',
             'billingCount',
              'paymentCount',
                'incidentsCount',
                    // 'criticalIncidents',
               'notificationCount'
            ));
    }

    protected function nurseDashboard()
    {
        $user = Auth::user();
    
        // ✅ Count only patients assigned to this nurse
        $assignedPatients = \App\Models\Patient::where('assigned_nurse_id', $user->id)
            ->count();
    
        // You can also use your relationship (same result, cleaner):
        $patientsCount = $user->assignedPatients()
            ->count();
    
        $today = now()->toDateString();
    
        $reportsCount = \App\Models\ProgressReport::whereDate('created_at', $today)
            ->where('reported_by', $user->id)
            ->count();
    
        $incidentsCount = \App\Models\IncidentReport::where('reported_by', $user->id)
            ->count();
    
        $pendingReports = \App\Models\ProgressReport::where('reported_by', $user->id)
            ->whereDate('created_at', '<', now()->subDays(30))
            ->count();
    
        return view('nurse.dashboard', compact(
            'assignedPatients',
            'pendingReports',
            'patientsCount',
            'reportsCount',
            'incidentsCount'
        ));
    }
    

    protected function defaultDashboard()
    {
        return view('dashboard.default');
    }
}
