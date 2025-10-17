<?php

namespace App\Http\Controllers;

use App\Models\ProgressReport;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ProgressReportController extends Controller
{
    public function index(Request $request)
    {
        $patients = Patient::orderBy('last_name')->orderBy('first_name')->get(['id','first_name','last_name','patient_code']);

        // All reports (for table or overview if needed)
        $reports = ProgressReport::with(['patient:id,first_name,last_name,patient_code', 'reporter:id,name'])
            ->latest()
            ->paginate(10);

        // Optional filter to view specific patient's reports in the "View Reports" tab
        $selectedPatient = null;
        $filteredReports = collect();
        if ($request->filled('patient_id')) {
            $selectedPatient = Patient::find($request->integer('patient_id'));
            if ($selectedPatient) {
                $filteredReports = ProgressReport::with(['reporter:id,name'])
                    ->where('patient_id', $selectedPatient->id)
                    ->latest()
                    ->get();
            }
        }

        return view('nurse.progress.index', compact('patients', 'reports', 'selectedPatient', 'filteredReports'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],

            // Symptom Severity (1-10)
            'depressed_mood'     => ['nullable', 'integer', 'min:1', 'max:10'],
            'anxiety'            => ['nullable', 'integer', 'min:1', 'max:10'],
            'suicidal_ideation'  => ['nullable', 'integer', 'min:1', 'max:10'],
            'hallucinations'     => ['nullable', 'string', 'max:255'],
            'delusions'          => ['nullable', 'string', 'max:255'],
            'sleep_disturbance'  => ['nullable', 'integer', 'min:1', 'max:10'],
            'appetite_changes'   => ['nullable', 'integer', 'min:1', 'max:10'],

            // Functional Status
            'self_care'           => ['nullable', 'string', 'max:255'],
            'work_school'         => ['nullable', 'string', 'max:255'],
            'social_interactions' => ['nullable', 'string', 'max:255'],
            'daily_activities'    => ['nullable', 'string', 'max:255'],

            // Cognitive & Emotional
            'attention'            => ['nullable', 'string', 'max:255'],
            'memory'               => ['nullable', 'string', 'max:255'],
            'decision_making'      => ['nullable', 'string', 'max:255'],
            'emotional_regulation' => ['nullable', 'string', 'max:255'],
            'insight'              => ['nullable', 'string', 'max:255'],

            // Behavioral
            'medication_adherence'   => ['nullable', 'in:0,1'],
            'therapy_engagement'     => ['nullable', 'in:0,1'],
            'risk_behaviors'         => ['nullable', 'string', 'max:255'],
            'sleep_activity_patterns'=> ['nullable', 'string', 'max:255'],

            // Physical
            'weight'                   => ['nullable', 'numeric', 'min:0', 'max:500'],
            'vital_signs'              => ['nullable', 'string', 'max:255'],
            'medication_side_effects'  => ['nullable', 'string', 'max:255'],
            'general_health'           => ['nullable', 'string', 'max:255'],

            // Social
            'family_support'    => ['nullable', 'string', 'max:255'],
            'peer_support'      => ['nullable', 'string', 'max:255'],
            'housing_stability' => ['nullable', 'string', 'max:255'],
            'access_to_services'=> ['nullable', 'string', 'max:255'],

            // Risk (1-3)
            'suicide_risk'        => ['nullable', 'integer', 'min:1', 'max:3'],
            'homicide_risk'       => ['nullable', 'integer', 'min:1', 'max:3'],
            'self_neglect_risk'   => ['nullable', 'integer', 'min:1', 'max:3'],
            'vulnerability_risk'  => ['nullable', 'integer', 'min:1', 'max:3'],

            // Treatment Goals JSON
            'treatment_goals'            => ['nullable', 'array'],
            'treatment_goals.*.goal'     => ['nullable', 'string', 'max:255'],
            'treatment_goals.*.baseline' => ['nullable', 'numeric'],
            'treatment_goals.*.current'  => ['nullable', 'numeric'],
            'treatment_goals.*.notes'    => ['nullable', 'string', 'max:255'],

            // Notes
            'notes' => ['nullable', 'string'],
        ]);

        // reported_by must come from the authenticated user, not the client
        $data['reported_by'] = auth()->id();

        // Clean empty treatment goals rows (where all fields are null/empty)
        if (!empty($data['treatment_goals'])) {
            $data['treatment_goals'] = collect($data['treatment_goals'])
                ->filter(function ($goal) {
                    $arr = array_filter((array) $goal, fn($v) => $v !== null && $v !== '');
                    return !empty($arr);
                })
                ->values()
                ->all();
            if (empty($data['treatment_goals'])) {
                $data['treatment_goals'] = null;
            }
        }

        // Normalize nullable boolean selects to actual boolean/null
        foreach (['medication_adherence', 'therapy_engagement'] as $boolField) {
            if (! Arr::exists($data, $boolField) || $data[$boolField] === null || $data[$boolField] === '') {
                $data[$boolField] = null;
            } else {
                $data[$boolField] = (int) $data[$boolField] === 1;
            }
        }

        ProgressReport::create($data);

        // Redirect back to index and open "View" tab with this patient selected
        return redirect()->route('progress-reports.index', [
            'patient_id' => $data['patient_id'],
        ])->with('success', 'Progress report saved successfully!')
          ->with('activeTab', '#view');
    }

    public function show(string $id)
    {
        $report = ProgressReport::with(['patient:id,first_name,last_name,patient_code', 'reporter:id,name'])
            ->findOrFail($id);

        return view('nurse.progress.show', compact('report'));
    }

    public function edit(string $id)
    {
        $report = ProgressReport::with(['patient', 'reporter'])->findOrFail($id);
        $patients = Patient::orderBy('last_name')->orderBy('first_name')->get();
        return view('admin.progress.edit', compact('report', 'patients'));
    }

    public function update(Request $request, string $id)
    {
        $report = ProgressReport::findOrFail($id);

        $data = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],

            // Same schema as store
            'depressed_mood'     => ['nullable', 'integer', 'min:1', 'max:10'],
            'anxiety'            => ['nullable', 'integer', 'min:1', 'max:10'],
            'suicidal_ideation'  => ['nullable', 'integer', 'min:1', 'max:10'],
            'hallucinations'     => ['nullable', 'string', 'max:255'],
            'delusions'          => ['nullable', 'string', 'max:255'],
            'sleep_disturbance'  => ['nullable', 'integer', 'min:1', 'max:10'],
            'appetite_changes'   => ['nullable', 'integer', 'min:1', 'max:10'],
            'self_care'           => ['nullable', 'string', 'max:255'],
            'work_school'         => ['nullable', 'string', 'max:255'],
            'social_interactions' => ['nullable', 'string', 'max:255'],
            'daily_activities'    => ['nullable', 'string', 'max:255'],
            'attention'            => ['nullable', 'string', 'max:255'],
            'memory'               => ['nullable', 'string', 'max:255'],
            'decision_making'      => ['nullable', 'string', 'max:255'],
            'emotional_regulation' => ['nullable', 'string', 'max:255'],
            'insight'              => ['nullable', 'string', 'max:255'],
            'medication_adherence'   => ['nullable', 'in:0,1'],
            'therapy_engagement'     => ['nullable', 'in:0,1'],
            'risk_behaviors'         => ['nullable', 'string', 'max:255'],
            'sleep_activity_patterns'=> ['nullable', 'string', 'max:255'],
            'weight'                   => ['nullable', 'numeric', 'min:0', 'max:500'],
            'vital_signs'              => ['nullable', 'string', 'max:255'],
            'medication_side_effects'  => ['nullable', 'string', 'max:255'],
            'general_health'           => ['nullable', 'string', 'max:255'],
            'family_support'    => ['nullable', 'string', 'max:255'],
            'peer_support'      => ['nullable', 'string', 'max:255'],
            'housing_stability' => ['nullable', 'string', 'max:255'],
            'access_to_services'=> ['nullable', 'string', 'max:255'],
            'suicide_risk'        => ['nullable', 'integer', 'min:1', 'max:3'],
            'homicide_risk'       => ['nullable', 'integer', 'min:1', 'max:3'],
            'self_neglect_risk'   => ['nullable', 'integer', 'min:1', 'max:3'],
            'vulnerability_risk'  => ['nullable', 'integer', 'min:1', 'max:3'],
            'treatment_goals'            => ['nullable', 'array'],
            'treatment_goals.*.goal'     => ['nullable', 'string', 'max:255'],
            'treatment_goals.*.baseline' => ['nullable', 'numeric'],
            'treatment_goals.*.current'  => ['nullable', 'numeric'],
            'treatment_goals.*.notes'    => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        if (!empty($data['treatment_goals'])) {
            $data['treatment_goals'] = collect($data['treatment_goals'])
                ->filter(function ($goal) {
                    $arr = array_filter((array) $goal, fn($v) => $v !== null && $v !== '');
                    return !empty($arr);
                })
                ->values()
                ->all();
            if (empty($data['treatment_goals'])) {
                $data['treatment_goals'] = null;
            }
        }

        foreach (['medication_adherence', 'therapy_engagement'] as $boolField) {
            if (! array_key_exists($boolField, $data) || $data[$boolField] === null || $data[$boolField] === '') {
                $data[$boolField] = null;
            } else {
                $data[$boolField] = (int) $data[$boolField] === 1;
            }
        }

        $report->update($data);

        return redirect()->route('progress-reports.show', $report)->with('success', 'Progress report updated.');
    }

    public function destroy(string $id)
    {
        $report = ProgressReport::findOrFail($id);
        $report->delete();

        return redirect()->route('progress-reports.index')->with('success', 'Progress report deleted.');
    }
}
