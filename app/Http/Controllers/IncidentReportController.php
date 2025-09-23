<?php

namespace App\Http\Controllers;

use App\Models\IncidentReport;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\AuditLogger;



class IncidentReportController extends Controller
{

    use AuditLogger;

    public function index(Request $request)
    {
        // Patients for dropdown
        $patients = Patient::orderBy('last_name')->get();
    
        // Base query for filtering
        $query = IncidentReport::with(['patient', 'reporter']);
    
        if ($request->filled('search_patient')) {
            $query->whereHas('patient', fn($q) =>
                $q->where('first_name', 'like', "%{$request->search_patient}%")
                  ->orWhere('last_name', 'like', "%{$request->search_patient}%")
            );
        }
    
        if ($request->filled('search_reporter')) {
            $query->whereHas('reporter', fn($q) =>
                $q->where('name', 'like', "%{$request->search_reporter}%")
            );
        }
    
        if ($request->filled('search_date')) {
            $query->whereDate('incident_date', $request->search_date);
        }
    
        // Final paginated reports
        $reports = $query->latest()->paginate(10);
    
        // Staff involvement stats
        $staffStats = User::withCount('incidentReports')
            ->orderByDesc('incident_reports_count')
            ->get();
    
        // Incident analytics: monthly counts
        $incidentStats = IncidentReport::selectRaw('MONTH(incident_date) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');
    
        // Recurrence by patient
        $recurrenceStats = IncidentReport::select('patient_id', DB::raw('COUNT(*) as count'))
            ->groupBy('patient_id')
            ->with('patient')
            ->orderByDesc('count')
            ->get();
    
        return view('nurse.incidents.index', compact(
            'reports',
            'patients',
            'staffStats',
            'incidentStats',
            'recurrenceStats'
        ));
    }

    // Show form to create a new report
    public function create()
    {
        $patients = Patient::orderBy('last_name')->get();
        return view('incident.create', compact('patients'));
    }

    // Store the submitted report
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'incident_date' => 'required|date',
            'description' => 'required|string|max:5000',
        ]);

        IncidentReport::create([
            'patient_id' => $request->patient_id,
            'reported_by' => Auth::id(),
            'incident_date' => $request->incident_date,
            'description' => $request->description,
        ]);

        return redirect()->route('incidents.index')->with('success', 'Incident report submitted successfully.');
    }

    // View a single report
    public function show(IncidentReport $incidentReport)
    {
        $incidentReport->load(['patient', 'reporter']);
        return view('nurse.incident.show', compact('incidentReport'));
    }

    // Optional: filter reports by patient
    public function byPatient($patientId)
    {
        $patient = Patient::findOrFail($patientId);
        $reports = IncidentReport::where('patient_id', $patientId)->with('reporter')->latest()->paginate(10);
        return view('incident-reports.by-patient', compact('patient', 'reports'));
    }
}
