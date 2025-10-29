<?php

namespace App\Http\Controllers;

use App\Models\PatientDetail;
use App\Models\PatientEvaluation;
use App\Models\Admission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PatientEvaluationController extends Controller
{
    private const EVALUATION_TYPES = ['initial', 'follow-up', 'emergency'];
    private const SEVERITY_LEVELS = ['mild', 'moderate', 'severe', 'critical'];
    private const RISK_LEVELS = ['low', 'medium', 'high'];
    private const DECISIONS = ['admit', 'outpatient', 'refer', 'monitor'];

    public function index(Request $request)
    {
        $query = PatientEvaluation::with(['patient', 'psychiatrist'])->latest();

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->integer('patient_id'));
        }

        if ($request->filled('evaluation_type') && in_array($request->get('evaluation_type'), self::EVALUATION_TYPES, true)) {
            $query->where('evaluation_type', $request->get('evaluation_type'));
        }

        if ($request->filled('severity_level') && in_array($request->get('severity_level'), self::SEVERITY_LEVELS, true)) {
            $query->where('severity_level', $request->get('severity_level'));
        }

        if ($request->filled('risk_level') && in_array($request->get('risk_level'), self::RISK_LEVELS, true)) {
            $query->where('risk_level', $request->get('risk_level'));
        }

        if ($request->filled('from')) {
            $query->whereDate('evaluation_date', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('evaluation_date', '<=', $request->date('to'));
        }

        if ($request->filled('q')) {
            $search = trim($request->get('q'));
            $query->where(function ($q) use ($search) {
                $q->whereHas('patient', function ($qp) use ($search) {
                    $qp->where('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('patient_code', 'like', "%{$search}%");
                })->orWhere('diagnosis', 'like', "%{$search}%")
                 ->orWhere('presenting_complaints', 'like', "%{$search}%");
            });
        }

        $evaluations = $query->paginate(15)->withQueryString();
        $patients = PatientDetail::orderBy('first_name')->get(['id', 'first_name', 'middle_name', 'last_name', 'patient_code']);

        return view('patient_evaluations.index', [
            'evaluations' => $evaluations,
            'patients' => $patients,
            'filters' => [
                'evaluation_types' => self::EVALUATION_TYPES,
                'severity_levels' => self::SEVERITY_LEVELS,
                'risk_levels' => self::RISK_LEVELS,
            ],
        ]);
    }

    public function create()
    {
        $patients = PatientDetail::orderBy('first_name')->get(['id', 'first_name', 'middle_name', 'last_name', 'patient_code']);

        return view('patient_evaluations.create', [
            'patients' => $patients,
            'evaluationTypes' => self::EVALUATION_TYPES,
            'severityLevels' => self::SEVERITY_LEVELS,
            'riskLevels' => self::RISK_LEVELS,
            'decisions' => self::DECISIONS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patient_details,id'],
            'evaluation_date' => ['required', 'date'],
            'evaluation_type' => ['required', Rule::in(self::EVALUATION_TYPES)],
            'presenting_complaints' => ['nullable', 'string'],
            'clinical_observations' => ['nullable', 'string'],
            'diagnosis' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'],
            'severity_level' => ['required', Rule::in(self::SEVERITY_LEVELS)],
            'risk_level' => ['required', Rule::in(self::RISK_LEVELS)],
            'priority_score' => ['nullable', 'integer', 'min:1', 'max:10'],
            'decision' => ['required', Rule::in(self::DECISIONS)],
            'requires_admission' => ['sometimes', 'boolean'],
            'admission_trigger_notes' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($request, $validated) {
            $requiresAdmission = ($request->boolean('requires_admission'))
                || ($validated['decision'] === 'admit')
                || ($validated['severity_level'] === 'critical')
                || ($validated['risk_level'] === 'high')
                || ((int)($validated['priority_score'] ?? 0) >= 8);

            $evaluation = new PatientEvaluation();
            $evaluation->fill($validated);
            $evaluation->psychiatrist_id = Auth::id();
            $evaluation->created_by = Auth::id();
            $evaluation->last_modified_by = Auth::id();
            $evaluation->requires_admission = $requiresAdmission;
            $evaluation->decision_made_at = now();

            if ($requiresAdmission && empty($validated['admission_trigger_notes'])) {
                $evaluation->admission_trigger_notes = 'Auto-flagged by decision/severity/risk/priority logic.';
            }

            $evaluation->save();

            // Auto-admit if conditions are met
            $this->autoAdmitIfRequired($evaluation);

            return redirect()
                ->route('evaluations.show', $evaluation->id)
                ->with('status', 'Evaluation created successfully.'.($evaluation->requires_admission && $evaluation->decision === 'admit' ? ' Patient auto-admitted.' : ''));
        });
    }

    public function show(string $id)
    {
        $evaluation = PatientEvaluation::with(['patient', 'psychiatrist'])->findOrFail($id);

        return view('patient_evaluations.show', compact('evaluation'));
    }

    public function edit(string $id)
    {
        $evaluation = PatientEvaluation::with(['patient', 'psychiatrist'])->findOrFail($id);
        $patients = PatientDetail::orderBy('first_name')->get(['id', 'first_name', 'middle_name', 'last_name', 'patient_code']);

        return view('patient_evaluations.edit', [
            'evaluation' => $evaluation,
            'patients' => $patients,
            'evaluationTypes' => self::EVALUATION_TYPES,
            'severityLevels' => self::SEVERITY_LEVELS,
            'riskLevels' => self::RISK_LEVELS,
            'decisions' => self::DECISIONS,
        ]);
    }

    // Optional: Only works if you add a PUT/PATCH route.
    public function update(Request $request, string $id)
    {
        $evaluation = PatientEvaluation::findOrFail($id);

        $validated = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patient_details,id'],
            'evaluation_date' => ['required', 'date'],
            'evaluation_type' => ['required', Rule::in(self::EVALUATION_TYPES)],
            'presenting_complaints' => ['nullable', 'string'],
            'clinical_observations' => ['nullable', 'string'],
            'diagnosis' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'],
            'severity_level' => ['required', Rule::in(self::SEVERITY_LEVELS)],
            'risk_level' => ['required', Rule::in(self::RISK_LEVELS)],
            'priority_score' => ['nullable', 'integer', 'min:1', 'max:10'],
            'decision' => ['required', Rule::in(self::DECISIONS)],
            'requires_admission' => ['sometimes', 'boolean'],
            'admission_trigger_notes' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($request, $evaluation, $validated) {
            $requiresAdmission = ($request->boolean('requires_admission'))
                || ($validated['decision'] === 'admit')
                || ($validated['severity_level'] === 'critical')
                || ($validated['risk_level'] === 'high')
                || ((int)($validated['priority_score'] ?? 0) >= 8);

            $evaluation->fill($validated);
            $evaluation->requires_admission = $requiresAdmission;
            $evaluation->last_modified_by = Auth::id();

            if ($requiresAdmission && empty($validated['admission_trigger_notes'])) {
                $evaluation->admission_trigger_notes = 'Auto-flagged by decision/severity/risk/priority logic.';
            }

            $evaluation->decision_made_at = now();
            $evaluation->save();

            // Auto-admit if conditions are met and no active admission exists
            $this->autoAdmitIfRequired($evaluation);

            return redirect()
                ->route('evaluations.show', $evaluation->id)
                ->with('status', 'Evaluation updated successfully.'.($evaluation->requires_admission && $evaluation->decision === 'admit' ? ' Patient auto-admitted.' : ''));
        });
    }

    /**
     * Create an admission when the evaluation indicates admission and no active admission exists.
     */
    private function autoAdmitIfRequired(PatientEvaluation $evaluation): void
    {
        // Must explicitly be an admission decision and requires_admission true
        if (!($evaluation->decision === 'admit' && $evaluation->requires_admission)) {
            return;
        }

        // Prevent duplicate active admissions for the patient
        $alreadyActive = Admission::query()
            ->where('patient_id', $evaluation->patient_id)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->exists();

        if ($alreadyActive) {
            return;
        }

        Admission::create([
            'patient_id' => $evaluation->patient_id,
            'evaluation_id' => $evaluation->id,
            'admitted_by' => Auth::id(),
            'assigned_psychiatrist_id' => $evaluation->psychiatrist_id, // same user who created the eval
            'care_level_id' => null, // set if you have a default
            'admission_date' => now()->toDateString(),
            'admission_reason' => trim(
                'Auto-admitted from evaluation #'.$evaluation->id.
                ' | Severity: '.($evaluation->severity_level ?? 'n/a').
                ' | Risk: '.($evaluation->risk_level ?? 'n/a').
                ($evaluation->admission_trigger_notes ? ' | Notes: '.$evaluation->admission_trigger_notes : '')
            ),
            'room_number' => null,
            'status' => 'active',
            'created_by' => Auth::id(),
            'last_modified_by' => Auth::id(),
        ]);
    }
}
