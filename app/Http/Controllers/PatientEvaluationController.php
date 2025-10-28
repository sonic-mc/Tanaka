<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientEvaluationRequest;
use App\Http\Requests\UpdatePatientEvaluationRequest;
use App\Models\Admission;
use App\Models\PatientDetail;
use App\Models\PatientEvaluation;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PatientEvaluationController extends Controller
{
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

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

    // Persist new evaluation (includes severity_level, risk_level, priority_score)
    public function store(StorePatientEvaluationRequest $request)
    {
        $userId = Auth::id();

        $createdEvaluation = null;
        $createdAdmission = null;

        DB::transaction(function () use ($request, $userId, &$createdEvaluation, &$createdAdmission) {
            // If decision is 'admit', force requires_admission to true for consistency
            $requiresAdmission = $request->boolean('requires_admission') || $request->decision === PatientEvaluation::DECISION_ADMIT;

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
                'requires_admission' => $requiresAdmission,
                'admission_trigger_notes' => $request->admission_trigger_notes,
                'decision_made_at' => now(),
                // Grading fields
                'severity_level' => $this->sanitizeSeverity($request->input('severity_level', 'mild')),
                'risk_level' => $this->sanitizeRisk($request->input('risk_level', 'low')),
                'priority_score' => $this->sanitizePriority($request->input('priority_score')),
                'created_by' => $userId,
            ]);

            $createdEvaluation = $evaluation;

            // Admission logic: create only if required and no active admission exists
            if ($requiresAdmission) {
                $createdAdmission = $this->createAdmissionIfNoneActive($evaluation, $userId);
            }
        });

        // After transaction commit, send notification email(s) if next-of-kin email exists
        try {
            if ($createdEvaluation) {
                $patient = PatientDetail::find($createdEvaluation->patient_id);

                if ($patient) {
                    $nokEmail = $patient->next_of_kin_email;
                    $nokName = $patient->next_of_kin_name ?? ($patient->first_name . ' ' . $patient->last_name);

                    if ($nokEmail) {
                        $subject = "Evaluation outcome for {$patient->first_name} {$patient->last_name}";
                        $bodyLines = [];
                        $bodyLines[] = "Dear {$nokName},";
                        $bodyLines[] = "";
                        $bodyLines[] = "This is to inform you of the recent clinical evaluation for {$patient->first_name} {$patient->last_name} (Patient Code: {$patient->patient_code}). Below are the key details:";
                        $bodyLines[] = "";
                        $bodyLines[] = "Evaluation date: " . optional($createdEvaluation->evaluation_date)->format('Y-m-d');
                        $bodyLines[] = "Evaluation type: " . ucfirst($createdEvaluation->evaluation_type);
                        $bodyLines[] = "Decision: " . ucfirst($createdEvaluation->decision);
                        // Grading fields
                        $bodyLines[] = "Severity: " . ucfirst($createdEvaluation->severity_level ?? 'mild');
                        $bodyLines[] = "Risk: " . ucfirst($createdEvaluation->risk_level ?? 'low');
                        $bodyLines[] = "Priority score: " . ($createdEvaluation->priority_score !== null ? $createdEvaluation->priority_score : '—');

                        if ($createdEvaluation->presenting_complaints) {
                            $bodyLines[] = "";
                            $bodyLines[] = "Presenting complaints:";
                            $bodyLines[] = $createdEvaluation->presenting_complaints;
                        }
                        if ($createdEvaluation->diagnosis) {
                            $bodyLines[] = "";
                            $bodyLines[] = "Diagnosis:";
                            $bodyLines[] = $createdEvaluation->diagnosis;
                        }
                        if ($createdEvaluation->recommendations) {
                            $bodyLines[] = "";
                            $bodyLines[] = "Recommendations:";
                            $bodyLines[] = $createdEvaluation->recommendations;
                        }

                        // If admission was created, append admission details
                        if ($createdAdmission) {
                            $bodyLines[] = "";
                            $bodyLines[] = "Admission details:";
                            $bodyLines[] = "Admission date: " . optional($createdAdmission->admission_date)->format('Y-m-d');
                            $bodyLines[] = "Reason: " . ($createdAdmission->admission_reason ?? '—');
                            $bodyLines[] = "Assigned psychiatrist ID: " . ($createdAdmission->assigned_psychiatrist_id ?? '—');
                            $bodyLines[] = "Room: " . ($createdAdmission->room_number ?? 'To be assigned');
                            $bodyLines[] = "";
                            $bodyLines[] = "The patient has been admitted and the family/next-of-kin will be contacted by the ward staff with further details.";
                        } else {
                            $bodyLines[] = "";
                            $bodyLines[] = "No admission was required/created at this time.";
                        }

                        $bodyLines[] = "";
                        $bodyLines[] = "If you have questions, please contact the clinic.";
                        $body = implode("\n", $bodyLines);

                        // Use EmailService to send the email (no attachments)
                        $result = $this->emailService->sendEmailWithAttachment($nokEmail, $subject, $body, []);

                        if (!empty($result['success']) && $result['success']) {
                            Log::info('Evaluation notification sent', [
                                'patient_id' => $patient->id,
                                'evaluation_id' => $createdEvaluation->id,
                                'admission_id' => $createdAdmission->id ?? null,
                                'recipient' => $nokEmail,
                                'response' => $result['data'] ?? null,
                            ]);
                        } else {
                            Log::warning('Evaluation notification failed', [
                                'patient_id' => $patient->id,
                                'evaluation_id' => $createdEvaluation->id,
                                'admission_id' => $createdAdmission->id ?? null,
                                'recipient' => $nokEmail,
                                'error' => $result['error'] ?? 'unknown',
                            ]);
                        }
                    } else {
                        Log::info('No next-of-kin email configured for patient; skipping notification', [
                            'patient_id' => $patient->id,
                            'evaluation_id' => $createdEvaluation->id,
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Log but do not interrupt normal flow
            Log::error('Error sending evaluation notification', [
                'exception' => $e->getMessage(),
                'evaluation_id' => $createdEvaluation->id ?? null,
            ]);
        }

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

    // Update record (includes severity_level, risk_level, priority_score)
    public function update(UpdatePatientEvaluationRequest $request, PatientEvaluation $evaluation)
    {
        $userId = Auth::id();

        $createdAdmission = null;

        DB::transaction(function () use ($request, $evaluation, $userId, &$createdAdmission) {
            $changedDecision = $evaluation->decision !== $request->decision;
            $requiresAdmission = $request->boolean('requires_admission') || $request->decision === PatientEvaluation::DECISION_ADMIT;

            $evaluation->update([
                'evaluation_date' => $request->evaluation_date,
                'evaluation_type' => $request->evaluation_type,
                'presenting_complaints' => $request->presenting_complaints,
                'clinical_observations' => $request->clinical_observations,
                'diagnosis' => $request->diagnosis,
                'recommendations' => $request->recommendations,
                'decision' => $request->decision,
                'requires_admission' => $requiresAdmission,
                'admission_trigger_notes' => $request->admission_trigger_notes,
                'last_modified_by' => $userId,
                'decision_made_at' => $changedDecision ? now() : $evaluation->decision_made_at,
                // Grading fields
                'severity_level' => $this->sanitizeSeverity($request->input('severity_level', $evaluation->severity_level ?? 'mild')),
                'risk_level' => $this->sanitizeRisk($request->input('risk_level', $evaluation->risk_level ?? 'low')),
                'priority_score' => $this->sanitizePriority($request->input('priority_score', $evaluation->priority_score)),
            ]);

            // Admission creation on update if newly requiring admission and none active
            if ($requiresAdmission) {
                $createdAdmission = $this->createAdmissionIfNoneActive($evaluation, $userId, 'Based on evaluation update');
            }
        });

        // Send notification to next-of-kin with updated evaluation outcome and admission details (if created)
        try {
            $patient = PatientDetail::find($evaluation->patient_id);
            if ($patient) {
                $nokEmail = $patient->next_of_kin_email;
                $nokName = $patient->next_of_kin_name ?? ($patient->first_name . ' ' . $patient->last_name);

                if ($nokEmail) {
                    $subject = "Updated evaluation outcome for {$patient->first_name} {$patient->last_name}";
                    $bodyLines = [];
                    $bodyLines[] = "Dear {$nokName},";
                    $bodyLines[] = "";
                    $bodyLines[] = "The evaluation for {$patient->first_name} {$patient->last_name} has been updated. Key details:";
                    $bodyLines[] = "";
                    $bodyLines[] = "Evaluation date: " . optional($evaluation->evaluation_date)->format('Y-m-d');
                    $bodyLines[] = "Decision: " . ucfirst($evaluation->decision);
                    // Grading fields
                    $bodyLines[] = "Severity: " . ucfirst($evaluation->severity_level ?? 'mild');
                    $bodyLines[] = "Risk: " . ucfirst($evaluation->risk_level ?? 'low');
                    $bodyLines[] = "Priority score: " . ($evaluation->priority_score !== null ? $evaluation->priority_score : '—');

                    if ($evaluation->presenting_complaints) {
                        $bodyLines[] = "";
                        $bodyLines[] = "Presenting complaints:";
                        $bodyLines[] = $evaluation->presenting_complaints;
                    }
                    if ($evaluation->diagnosis) {
                        $bodyLines[] = "";
                        $bodyLines[] = "Diagnosis:";
                        $bodyLines[] = $evaluation->diagnosis;
                    }
                    if ($evaluation->recommendations) {
                        $bodyLines[] = "";
                        $bodyLines[] = "Recommendations:";
                        $bodyLines[] = $evaluation->recommendations;
                    }

                    if ($createdAdmission) {
                        $bodyLines[] = "";
                        $bodyLines[] = "Admission details:";
                        $bodyLines[] = "Admission date: " . optional($createdAdmission->admission_date)->format('Y-m-d');
                        $bodyLines[] = "Reason: " . ($createdAdmission->admission_reason ?? '—');
                        $bodyLines[] = "Assigned psychiatrist ID: " . ($createdAdmission->assigned_psychiatrist_id ?? '—');
                        $bodyLines[] = "Room: " . ($createdAdmission->room_number ?? 'To be assigned');
                    }

                    $bodyLines[] = "";
                    $bodyLines[] = "If you have questions, please contact the clinic.";
                    $body = implode("\n", $bodyLines);

                    $result = $this->emailService->sendEmailWithAttachment($nokEmail, $subject, $body, []);

                    if (!empty($result['success']) && $result['success']) {
                        Log::info('Evaluation update notification sent', [
                            'patient_id' => $patient->id,
                            'evaluation_id' => $evaluation->id,
                            'admission_id' => $createdAdmission->id ?? null,
                            'recipient' => $nokEmail,
                            'response' => $result['data'] ?? null,
                        ]);
                    } else {
                        Log::warning('Evaluation update notification failed', [
                            'patient_id' => $patient->id,
                            'evaluation_id' => $evaluation->id,
                            'admission_id' => $createdAdmission->id ?? null,
                            'recipient' => $nokEmail,
                            'error' => $result['error'] ?? 'unknown',
                        ]);
                    }
                } else {
                    Log::info('No next-of-kin email configured; skipping update notification', [
                        'patient_id' => $patient->id,
                        'evaluation_id' => $evaluation->id,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error sending evaluation update notification', [
                'exception' => $e->getMessage(),
                'evaluation_id' => $evaluation->id ?? null,
            ]);
        }

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

    private function createAdmissionIfNoneActive(PatientEvaluation $evaluation, int $userId, ?string $reasonOverride = null): ?Admission
    {
        $hasActiveAdmission = Admission::where('patient_id', $evaluation->patient_id)
            ->where('status', 'active')
            ->exists();

        if ($hasActiveAdmission) {
            return null;
        }

        return Admission::create([
            'patient_id' => $evaluation->patient_id,
            'evaluation_id' => $evaluation->id,
            'admission_date' => now(),
            'admission_reason' => $reasonOverride ?? ($evaluation->admission_trigger_notes ?: 'Based on evaluation outcome'),
            'admitted_by' => $userId,
            'assigned_psychiatrist_id' => $evaluation->psychiatrist_id,
            // 'care_level_id' => null, // optional
            'status' => 'active',
            'created_by' => $userId,
            // 'last_modified_by' => null,
        ]);
    }

    private function sanitizeSeverity(?string $val): string
    {
        $allowed = ['mild', 'moderate', 'severe', 'critical'];
        $val = strtolower((string) $val);

        return in_array($val, $allowed, true) ? $val : 'mild';
    }

    private function sanitizeRisk(?string $val): string
    {
        $allowed = ['low', 'medium', 'high'];
        $val = strtolower((string) $val);

        return in_array($val, $allowed, true) ? $val : 'low';
    }

    private function sanitizePriority($val): ?int
    {
        if ($val === null || $val === '') {
            return null;
        }
        $n = (int) $val;
        if ($n < 1) $n = 1;
        if ($n > 10) $n = 10;

        return $n;
    }
}
