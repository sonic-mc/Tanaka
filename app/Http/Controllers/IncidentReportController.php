<?php

namespace App\Http\Controllers;

use App\Models\IncidentReport;
use App\Models\PatientDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Traits\AuditLogger;

class IncidentReportController extends Controller
{
    use AuditLogger;

    public function index(Request $request)
    {
        // Patients for dropdown
        $patients = PatientDetail::orderBy('last_name')->get();

        // Base query for filtering (align with model relations provided)
        $query = IncidentReport::with(['patient', 'reportedBy']);

        if ($request->filled('search_patient')) {
            $search = $request->input('search_patient');
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('search_reporter')) {
            $search = $request->input('search_reporter');
            $query->whereHas('reportedBy', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('search_date')) {
            $query->whereDate('incident_date', $request->input('search_date'));
        }

        // Final paginated reports
        $reports = $query->latest()->paginate(10);

        // Staff involvement stats (avoid relying on a User->incidentReports() relation)
        $incidentReportsTable = 'incidents_reports';

        $staffStats = User::query()
            ->leftJoin($incidentReportsTable, "{$incidentReportsTable}.reported_by", '=', 'users.id')
            ->select([
                'users.id',
                'users.name',
                DB::raw("COUNT({$incidentReportsTable}.id) as incident_reports_count"),
            ])
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('incident_reports_count')
            ->get();

        // Incident analytics: monthly counts (YYYY-MM buckets)
        $incidentStats = IncidentReport::selectRaw('DATE_FORMAT(incident_date, "%Y-%m") as period, COUNT(*) as count')
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('count', 'period');

        // Recurrence by patient
        $recurrenceStats = IncidentReport::select('patient_id', DB::raw('COUNT(*) as count'))
            ->with('patient')
            ->groupBy('patient_id')
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
        $patients = PatientDetail::orderBy('last_name')->get();
        return view('incident.create', compact('patients'));
    }

    // Store the submitted report
    public function store(Request $request)
    {
        $request->validate([
            // Ensure we validate against the correct table: patient_details
            'patient_id'    => 'required|exists:patient_details,id',
            'incident_date' => 'required|date',
            'description'   => 'required|string|max:5000',
        ]);

        IncidentReport::create([
            'patient_id'    => $request->patient_id,
            'reported_by'   => Auth::id(),
            'incident_date' => $request->incident_date,
            'description'   => $request->description,
        ]);

        return redirect()->route('incidents.index')->with('success', 'Incident report submitted successfully.');
    }

    // View a single report
    public function show(IncidentReport $incidentReport)
    {
        $incidentReport->load(['patient', 'reportedBy']);
        return view('nurse.incident.show', compact('incidentReport'));
    }

    // Optional: filter reports by patient
    public function byPatient($patientId)
    {
        $patient = PatientDetail::findOrFail($patientId);
        $reports = IncidentReport::where('patient_id', $patientId)
            ->with('reportedBy')
            ->latest()
            ->paginate(10);

        return view('incident-reports.by-patient', compact('patient', 'reports'));
    }
}
