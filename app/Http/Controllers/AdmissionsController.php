<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\PatientDetail;
use App\Models\PatientEvaluation;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AdmissionsController extends Controller
{
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    // List all admissions (paginated)
    public function index(Request $request)
    {
        $admissions = Admission::with(['patient', 'evaluation'])
            ->orderByDesc('admission_date')
            ->paginate(25)
            ->withQueryString();

        return view('admissions.index', compact('admissions'));
    }

    // Show form to create new admission
    public function create(Request $request)
    {
        // Fetch patients who do NOT have an active admission
        $activePatientIds = Admission::where('status', 'active')->pluck('patient_id')->toArray();

        $patients = PatientDetail::whereNotIn('id', $activePatientIds)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // Evaluations that flagged requires_admission = true (recent first)
        $evaluations = PatientEvaluation::where('requires_admission', true)
            ->orderByDesc('evaluation_date')
            ->get();

        return view('admissions.create', compact('patients', 'evaluations'));
    }

    // Store new admission
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_details,id',
            'evaluation_id' => 'nullable|exists:patient_evaluations,id',
            'admission_date' => 'required|date',
            'admission_reason' => 'nullable|string|max:2000',
            'room_number' => 'nullable|string|max:50',
            'care_level_id' => 'nullable|exists:care_levels,id',
        ]);

        $patient = PatientDetail::findOrFail($validated['patient_id']);

        // Prevent admitting a patient who already has an active admission
        $alreadyAdmitted = Admission::where('patient_id', $patient->id)
            ->where('status', 'active')
            ->exists();

        if ($alreadyAdmitted) {
            return back()
                ->withInput()
                ->withErrors(['patient_id' => 'This patient already has an active admission.']);
        }

        DB::beginTransaction();

        try {
            $admission = Admission::create([
                'patient_id' => $patient->id,
                'evaluation_id' => $validated['evaluation_id'] ?? null,
                'admission_date' => $validated['admission_date'],
                'admission_reason' => $validated['admission_reason'] ?? null,
                'room_number' => $validated['room_number'] ?? null,
                'care_level_id' => $validated['care_level_id'] ?? null,
                'admitted_by' => Auth::id(),
                'assigned_psychiatrist_id' => Auth::id(), // default to current user; adjust as needed
                'status' => 'active',
                'created_by' => Auth::id(),
            ]);

            // Optional audit: if AuditLogger trait exists, prefer it
            if (method_exists($this, 'audit')) {
                $this->audit('info', 'patient-admitted', [
                    'message' => "Admitted patient {$patient->first_name} {$patient->last_name} ({$patient->patient_code})",
                    'patient_id' => $patient->id,
                    'admission_id' => $admission->id,
                ]);
            } else {
                Log::info('Admitted patient', [
                    'patient_id' => $patient->id,
                    'patient_code' => $patient->patient_code,
                    'admission_id' => $admission->id,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to create admission', [
                'error' => $e->getMessage(),
                'payload' => $validated,
            ]);

            return back()->withInput()->withErrors(['error' => 'Could not create admission: ' . $e->getMessage()]);
        }

        // After admission is created, send email to next of kin if configured
        try {
            $nokEmail = $patient->next_of_kin_email;
            $nokName = $patient->next_of_kin_name ?: trim("{$patient->first_name} {$patient->last_name}");

            if ($nokEmail) {
                $subject = "Admission confirmation for {$patient->first_name} {$patient->last_name}";
                $bodyLines = [
                    "Dear {$nokName},",
                    "",
                    "This is to confirm that {$patient->first_name} {$patient->last_name} (Patient Code: {$patient->patient_code}) has been admitted to our facility.",
                    "",
                    "Admission details:",
                    "• Admission date: " . optional($admission->admission_date)->format('Y-m-d'),
                    "• Room number: " . ($admission->room_number ?? 'To be assigned'),
                    "• Care level id: " . ($admission->care_level_id ?? 'N/A'),
                    "• Reason: " . ($admission->admission_reason ?? '—'),
                    "",
                    "A member of the care team will contact you with further details. If you have questions, please call the facility.",
                    "",
                    "Warm regards,",
                    "Hospital Admin Team",
                ];
                $body = implode("\n", $bodyLines);

                $result = $this->emailService->sendEmailWithAttachment($nokEmail, $subject, $body, []);

                if (!empty($result['success']) && $result['success']) {
                    Log::info('Admission notification sent', [
                        'patient_id' => $patient->id,
                        'admission_id' => $admission->id,
                        'recipient' => $nokEmail,
                    ]);
                } else {
                    Log::warning('Admission notification failed', [
                        'patient_id' => $patient->id,
                        'admission_id' => $admission->id,
                        'recipient' => $nokEmail,
                        'error' => $result['error'] ?? 'unknown',
                    ]);
                }
            } else {
                Log::info('No next-of-kin email configured; skipping admission notification', [
                    'patient_id' => $patient->id,
                    'admission_id' => $admission->id,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Error sending admission email', [
                'exception' => $e->getMessage(),
                'patient_id' => $patient->id,
                'admission_id' => $admission->id ?? null,
            ]);
            // do not block user flow
        }

        return redirect()->route('admissions.index')->with('success', 'Admission created successfully.');
    }

    // Show single admission
    public function show(Admission $admission)
    {
        $admission->load(['patient', 'evaluation']);
        return view('admissions.show', compact('admission'));
    }

    // Show form to edit admission
    public function edit(Admission $admission)
    {
        // For editing we still show patients who don't already have an active admission,
        // plus the currently admitted patient so the select shows the existing value.
        $activePatientIds = Admission::where('status', 'active')->pluck('patient_id')->toArray();

        $patients = PatientDetail::whereNotIn('id', $activePatientIds)
            ->orWhere('id', $admission->patient_id)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $evaluations = PatientEvaluation::where('requires_admission', true)->get();

        return view('admissions.edit', compact('admission', 'patients', 'evaluations'));
    }

    // Update admission
    public function update(Request $request, Admission $admission)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_details,id',
            'evaluation_id' => 'nullable|exists:patient_evaluations,id',
            'admission_date' => 'required|date',
            'admission_reason' => 'nullable|string|max:2000',
            'room_number' => 'nullable|string|max:50',
            'care_level_id' => 'nullable|exists:care_levels,id',
            'status' => 'required|in:active,discharged,transferred,deceased',
        ]);

        $admission->update([
            'patient_id' => $validated['patient_id'],
            'evaluation_id' => $validated['evaluation_id'] ?? null,
            'admission_date' => $validated['admission_date'],
            'admission_reason' => $validated['admission_reason'] ?? null,
            'room_number' => $validated['room_number'] ?? null,
            'care_level_id' => $validated['care_level_id'] ?? null,
            'status' => $validated['status'],
            'last_modified_by' => Auth::id(),
        ]);

        return redirect()->route('admissions.index')->with('success', 'Admission updated.');
    }

    // Discharge admission (quick action)
    public function discharge(Admission $admission)
    {
        $admission->update([
            'status' => 'discharged',
            'last_modified_by' => Auth::id(),
        ]);

        return redirect()->route('admissions.index')->with('success', 'Patient discharged.');
    }

    // Delete admission (soft delete)
    public function destroy(Admission $admission)
    {
        $admission->delete();
        return redirect()->route('admissions.index')->with('success', 'Admission deleted.');
    }
}
