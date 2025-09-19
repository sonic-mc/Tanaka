<?php

namespace App\Http\Controllers;

use App\Models\ProgressReport;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;

class ProgressReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $reports = ProgressReport::with(['patient', 'reporter'])->latest()->paginate(10);
        $patients = Patient::orderBy('last_name')->get();
        $treatmentGoals = json_encode($request->input('treatment_goals'));
    
        return view('nurse.progress.index', compact('reports', 'patients', 'treatmentGoals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $patients = Patient::all();
        $staff = User::whereIn('role', ['nurse', 'psychiatrist'])->get();
        return view('nurse.progress.create', compact('patients', 'staff'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'reported_by' => 'required|exists:users,id',
            'notes' => 'nullable|string',
            'depressed_mood' => 'nullable|integer|min:1|max:10',
            'anxiety' => 'nullable|integer|min:1|max:10',
            'suicidal_ideation' => 'nullable|integer|min:1|max:10',
            'hallucinations' => 'nullable|string',
            'delusions' => 'nullable|string',
            'self_care' => 'nullable|string',
            'work_school' => 'nullable|string',
            'social_interactions' => 'nullable|string',
            'daily_activities' => 'nullable|string',
            'attention' => 'nullable|string',
            'memory' => 'nullable|string',
            'decision_making' => 'nullable|string',
            'emotional_regulation' => 'nullable|string',
            'insight' => 'nullable|string',
            'medication_adherence' => 'nullable|boolean',
            'therapy_engagement' => 'nullable|boolean',
            'risk_behaviors' => 'nullable|string',
            'sleep_activity_patterns' => 'nullable|string',
            'weight' => 'nullable|numeric',
            'vital_signs' => 'nullable|string',
            'medication_side_effects' => 'nullable|string',
            'general_health' => 'nullable|string',
            'family_support' => 'nullable|string',
            'peer_support' => 'nullable|string',
            'housing_stability' => 'nullable|string',
            'access_to_services' => 'nullable|string',
            'suicide_risk' => 'nullable|integer|min:1|max:3',
            'homicide_risk' => 'nullable|integer|min:1|max:3',
            'self_neglect_risk' => 'nullable|integer|min:1|max:3',
            'vulnerability_risk' => 'nullable|integer|min:1|max:3',
            'treatment_goals' => 'nullable|array',
            'treatment_goals.*.goal' => 'nullable|string',
            'treatment_goals.*.baseline' => 'nullable|numeric',
            'treatment_goals.*.current' => 'nullable|numeric',
            'treatment_goals.*.notes' => 'nullable|string',
            'next_review_date' => 'nullable|date',
        ]);

        ProgressReport::create($data);

        return redirect()->back()->with('success', 'Progress report saved successfully!');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Load report with patient and reporter relationships
        $report = ProgressReport::with(['patient', 'reporter'])->findOrFail($id);
    
        // Decode treatment goals JSON
        $treatmentGoals = $report->treatment_goals ? json_decode($report->treatment_goals, true) : [];
    
        return view('nurse.progress.show', compact('report', 'treatmentGoals'));
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $report = ProgressReport::findOrFail($id);
        $patients = Patient::all();
        $staff = User::whereIn('role', ['nurse', 'psychiatrist'])->get();
        return view('admin.progress.edit', compact('report', 'patients', 'staff'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $report = ProgressReport::findOrFail($id);

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'reported_by' => 'required|exists:users,id',
            'notes' => 'nullable|string',
            'behavior' => 'nullable|string',
            'medication_response' => 'nullable|string',
            'attendance' => 'required|boolean',
        ]);

        $report->update($validated);

        return redirect()->route('progress-reports.index')->with('success', 'Progress report updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $report = ProgressReport::findOrFail($id);
        $report->delete();

        return redirect()->route('progress-reports.index')->with('success', 'Progress report deleted.');
    }
}
