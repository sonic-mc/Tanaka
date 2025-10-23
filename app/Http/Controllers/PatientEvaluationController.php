<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientEvaluationRequest;
use App\Http\Requests\UpdatePatientEvaluationRequest;
use App\Models\Admission;
use App\Models\PatientDetail;
use App\Models\PatientEvaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PatientEvaluationController extends Controller
{
    // List evaluations with filters, search, soft-delete status
    public function index(Request $request)
    {
        $status = $request->get('status', 'active'); // active|trashed|all

        $query = PatientEvaluation::query()
            ->with(['patient', 'psychiatrist']);

        if ($status === 'trashed') {
            $query->onlyTrashed();
        } elseif ($status === 'all') {
            $query->withTrashed();
        }

        $evaluations = $query
            ->ofType($request->get('type'))
            ->ofDecision($request->get('decision'))
            ->dateBetween($request->get('from'), $request->get('to'))
            ->search($request->get('q'))
            ->latest('evaluation_date')
            ->paginate(20)
            ->withQueryString();

        return view('evaluations.index', [
            'evaluations' => $evaluations,
            'filters' => [
                'q' => $request->get('q'),
                'type' => $request->get('type'),
                'decision' => $request->get('decision'),
                'from' => $request->get('from'),
                'to' => $request->get('to'),
                'status' => $status,
            ],
        ]);
    }

    // Show create form; supports preselected patient via ?patient_id=
    public function create(Request $request)
    {
        $selectedPatientId = $request->integer('patient_id');
        // Provide a small initial list; large lists should be fetched via AJAX lookup
        $patients = PatientDetail::latest()->limit(100)->get();

        return view('evaluations.create', compact('patients', 'selectedPatientId'));
    }

    // Persist new evaluation
    public function store(StorePatientEvaluationRequest $request)
    {
        $userId = Auth::id();

        DB::transaction(function () use ($request, $userId) {
            $evaluation = PatientEvaluation::create([
                'patient_id' => $request->patient_id,
                'psychiatrist_id' => $userId,
                'evaluation_date' => $request->evaluation_date,
                'evaluation_type' => $request->evaluation_type,
                'presenting_complaints' => $request->presenting_complaints,
                'clinical_observations' => $request->clinical_observations,
                'diagnosis' => $request->diagnosis,
                'recommendations' => $request->recommendations,
                'decision' => $request->decision,
                'requires_admission' => (bool) $request->requires_admission,
                'admission_trigger_notes' => $request->admission_trigger_notes,
                'decision_made_at' => now(),
                'created_by' => $userId,
            ]);

            // Admission logic: create only if required and no active admission exists
            if ($evaluation->requires_admission) {
                $hasActiveAdmission = Admission::where('patient_id', $evaluation->patient_id)
                    ->where('status', 'active')
                    ->exists();

                if (!$hasActiveAdmission) {
                    Admission::create([
                        'patient_id' => $evaluation->patient_id,
                        'evaluation_id' => $evaluation->id,
                        'admission_date' => now(),
                        'admission_reason' => $evaluation->admission_trigger_notes ?: 'Based on evaluation outcome',
                        'admitted_by' => $userId,
                        'assigned_psychiatrist_id' => $evaluation->psychiatrist_id,
                        'status' => 'active',
                        'created_by' => $userId,
                    ]);
                }
            }
        });

        return redirect()->route('evaluations.index')->with('success', 'Evaluation saved successfully.');
    }

    // Show details (also allow viewing soft-deleted evaluation)
    public function show($id)
    {
        $evaluation = PatientEvaluation::withTrashed()
            ->with(['patient', 'psychiatrist', 'creator', 'lastModifier'])
            ->findOrFail($id);

        return view('evaluations.show', compact('evaluation'));
    }

    // Edit form
    public function edit(PatientEvaluation $evaluation)
    {
        $patients = PatientDetail::latest()->limit(100)->get();
        return view('evaluations.edit', compact('evaluation', 'patients'));
    }

    // Update record
    public function update(UpdatePatientEvaluationRequest $request, PatientEvaluation $evaluation)
    {
        $userId = Auth::id();

        DB::transaction(function () use ($request, $evaluation, $userId) {
            $changedDecision = $evaluation->decision !== $request->decision;

            $evaluation->update([
                'evaluation_date' => $request->evaluation_date,
                'evaluation_type' => $request->evaluation_type,
                'presenting_complaints' => $request->presenting_complaints,
                'clinical_observations' => $request->clinical_observations,
                'diagnosis' => $request->diagnosis,
                'recommendations' => $request->recommendations,
                'decision' => $request->decision,
                'requires_admission' => (bool) $request->requires_admission,
                'admission_trigger_notes' => $request->admission_trigger_notes,
                'last_modified_by' => $userId,
                'decision_made_at' => $changedDecision ? now() : $evaluation->decision_made_at,
            ]);

            // Admission creation on update if newly requiring admission and none active
            if ($evaluation->requires_admission) {
                $hasActiveAdmission = Admission::where('patient_id', $evaluation->patient_id)
                    ->where('status', 'active')
                    ->exists();

                if (!$hasActiveAdmission) {
                    Admission::create([
                        'patient_id' => $evaluation->patient_id,
                        'evaluation_id' => $evaluation->id,
                        'admission_date' => now(),
                        'admission_reason' => $evaluation->admission_trigger_notes ?: 'Based on evaluation update',
                        'admitted_by' => $userId,
                        'assigned_psychiatrist_id' => $evaluation->psychiatrist_id,
                        'status' => 'active',
                        'created_by' => $userId,
                    ]);
                }
            }
        });

        return redirect()->route('evaluations.index')->with('success', 'Evaluation updated.');
    }

    // Soft-delete evaluation
    public function destroy(PatientEvaluation $evaluation)
    {
        $evaluation->delete();
        return redirect()->route('evaluations.index')->with('success', 'Evaluation archived.');
    }

    // Restore soft-deleted evaluation
    public function restore($id)
    {
        $evaluation = PatientEvaluation::onlyTrashed()->findOrFail($id);
        $evaluation->restore();

        return redirect()->route('evaluations.index', ['status' => 'trashed'])->with('success', 'Evaluation restored.');
    }

    // Permanently delete evaluation
    public function forceDelete($id)
    {
        $evaluation = PatientEvaluation::onlyTrashed()->findOrFail($id);
        $evaluation->forceDelete();

        return redirect()->route('evaluations.index', ['status' => 'trashed'])->with('success', 'Evaluation permanently deleted.');
    }
}
