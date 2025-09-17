<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


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
        // Example metrics — replace with real queries
        $patientCount = \App\Models\Patient::count();
        $staffCount = \App\Models\User::whereIn('role', ['nurse', 'psychiatrist'])->count();
        $pendingTasks = \App\Models\Task::where('status', 'pending')->count();
        $criticalIncidents = \App\Models\IncidentReport::where('description', 'like', '%critical%')->count();

        return view('admin.dashboard', compact('patientCount', 'staffCount', 'pendingTasks', 'criticalIncidents'));
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
        $pendingReports = \App\Models\ProgressReport::where('reported_by', Auth::id())
            ->whereDate('created_at', '<', now()->subDays(30))
            ->count();

        return view('nurse.dashboard', compact('assignedPatients', 'pendingReports'));
    }

    protected function defaultDashboard()
    {
        return view('dashboard.default');
    }
}
