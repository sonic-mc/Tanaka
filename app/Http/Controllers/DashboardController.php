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



class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        switch ($user->role) {
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
        $staffCount = User::whereIn('role', ['nurse', 'psychiatrist'])->count();
        $pendingTasks = Task::where('status', 'pending')->count();
        $criticalIncidents = IncidentReport::where('description', 'like', '%critical%')->count();
    
        // Role & permission data
        $roles = Role::all();
        $permissions = Permission::all();
        $users = User::with('roles', 'permissions')->get();
    
        return view('admin.dashboard', compact(
            'patientCount',
            'staffCount',
            'pendingTasks',
            'criticalIncidents',
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

        $upcomingEvaluations = \App\Models\Evaluation::where('evaluated_by', Auth::id())
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->count();

        return view('psychiatrist.dashboard', compact('assignedPatients', 'upcomingEvaluations'));
    }

    protected function nurseDashboard()
    {
        $assignedPatients = \App\Models\Patient::where('status', 'active')->count();
        
        $patientsCount = Patient::count();

        $today = now()->toDateString();
        // $appointmentsCount = Appointment::whereDate('date', $today)->count();

        $reportsCount = ProgressReport::whereDate('created_at', $today)->count();

       $incidentsCount = IncidentReport::count();
        $pendingReports = \App\Models\ProgressReport::where('reported_by', Auth::id())
            ->whereDate('created_at', '<', now()->subDays(30))
            ->count();

        return view('nurse.dashboard', compact('assignedPatients', 'pendingReports', 'patientsCount', 'reportsCount', 'incidentsCount'));
    }

    protected function defaultDashboard()
    {
        return view('dashboard.default');
    }
}
