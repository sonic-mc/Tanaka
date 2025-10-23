<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\PatientDetail;
use App\Models\PatientEvaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdmissionsController extends Controller
{
    // List all admissions
    public function index()
    {
        $admissions = Admission::with('patient', 'evaluation')->latest()->get();
        return view('admissions.index', compact('admissions'));
    }

    // Show form to create new admission
    public function create()
    {
        $patients = PatientDetail::all();
        $evaluations = PatientEvaluation::where('requires_admission', true)->get();
        return view('admissions.create', compact('patients', 'evaluations'));
    }

    // Store new admission
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patient_details,id',
            'evaluation_id' => 'nullable|exists:patient_evaluations,id',
            'admission_date' => 'required|date',
            'admission_reason' => 'nullable|string',
            'room_number' => 'nullable|string',
            'care_level_id' => 'nullable|exists:care_levels,id',
        ]);

        Admission::create([
            'patient_id' => $request->patient_id,
            'evaluation_id' => $request->evaluation_id,
            'admission_date' => $request->admission_date,
            'admission_reason' => $request->admission_reason,
            'room_number' => $request->room_number,
            'care_level_id' => $request->care_level_id,
            'admitted_by' => Auth::id(),
            'assigned_psychiatrist_id' => Auth::id(),
            'status' => 'active',
            'created_by' => Auth::id(),
        ]);

        $this->logAudit(
            action: 'Patient Admission',
            description: "Admitted patient {$patient->first_name} {$patient->last_name} (Code: {$patient->patient_code})",
            module: 'patients',
            severity: 'info',
            context: [
                'patient_id' => $patient->id,
                'room_number' => $patient->room_number,
                'care_level_id' => $patient->current_care_level_id,
                'next_of_kin_email' => $patient->next_of_kin_email,
            ]
        );
        

        // ✉️ Send email to next of kin
        $subject = "Admission Confirmation: {$patient->first_name} {$patient->last_name}";
        $body = <<<EOT
Dear {$patient->next_of_kin_name},

We are writing to confirm that {$patient->first_name} {$patient->last_name} has been admitted to our facility.

Admission Details:
- Patient Code: {$patient->patient_code}
- Admission Date: {$patient->admission_date}
- Room Number: {$patient->room_number}
- Care Level ID: {$patient->current_care_level_id}
- Reason for Admission: {$patient->admission_reason}

If you have any questions or need further assistance, please contact us.

Warm regards,  
Hospital Admin Team
EOT;

        $this->emailService->sendEmailWithAttachment(
             $validated['next_of_kin_email'],
            $subject,
            $body,
            [] // No attachments yet
        );


        return redirect()->route('admissions.index')->with('success', 'Admission created successfully.');
    }

    // Show single admission
    public function show(Admission $admission)
    {
        return view('admissions.show', compact('admission'));
    }

    // Show form to edit admission
    public function edit(Admission $admission)
    {
        $patients = PatientDetail::all();
        $evaluations = PatientEvaluation::where('requires_admission', true)->get();
        return view('admissions.edit', compact('admission', 'patients', 'evaluations'));
    }

    // Update admission
    public function update(Request $request, Admission $admission)
    {
        $request->validate([
            'admission_date' => 'required|date',
            'admission_reason' => 'nullable|string',
            'room_number' => 'nullable|string',
            'care_level_id' => 'nullable|exists:care_levels,id',
            'status' => 'required|in:active,discharged,transferred,deceased',
        ]);

        $admission->update([
            'admission_date' => $request->admission_date,
            'admission_reason' => $request->admission_reason,
            'room_number' => $request->room_number,
            'care_level_id' => $request->care_level_id,
            'status' => $request->status,
            'last_modified_by' => Auth::id(),
        ]);

        return redirect()->route('admissions.index')->with('success', 'Admission updated.');
    }

    // Discharge admission
    public function discharge(Admission $admission)
    {
        $admission->update([
            'status' => 'discharged',
            'last_modified_by' => Auth::id(),
        ]);

        return redirect()->route('admissions.index')->with('success', 'Patient discharged.');
    }

    // Delete admission
    public function destroy(Admission $admission)
    {
        $admission->delete();
        return redirect()->route('admissions.index')->with('success', 'Admission deleted.');
    }
}
