<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\EmailService;
use App\Traits\AuditLogger;

class AdmissionController extends Controller
{
    use AuditLogger;

    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Show admission form.
     */
    public function create()
    {
        if (!Auth::user()->hasRole('nurse')) {
            abort(403, 'Only nurses can admit patients.');
        }

        $nurses = User::whereHas('roles', fn($q) => $q->where('name', 'nurse'))->get();
        $staff = User::whereIn('role', ['nurse', 'psychiatrist'])->get();
        $careLevels = \App\Models\CareLevel::all();

        return view('admissions.create', compact('nurses', 'careLevels', 'staff'));
    }

    /**
     * Store new admission.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasRole('nurse')) {
            abort(403, 'Only nurses can admit patients.');
        }

        $validated = $request->validate([
            'first_name'               => 'required|string|max:255',
            'last_name'                => 'required|string|max:255',
            'gender'                   => 'required|in:male,female,other',
            'dob'                      => 'nullable|date',
            'contact_number'          => 'nullable|string|max:20',
            'admission_date'          => 'required|date',
            'admission_reason'        => 'nullable|string',
            'assigned_nurse_id'       => 'nullable|exists:users,id',
            'room_number'             => 'nullable|string|max:50',
            'current_care_level_id'   => 'nullable|exists:care_levels,id',
            'next_of_kin_name'        => 'required|string|max:255',
            'next_of_kin_relationship'=> 'required|string|max:100',
            'next_of_kin_contact_number' => 'nullable|string|max:20',
            'next_of_kin_email'       => 'required|email',
        ]);

        $patient = Patient::create([
            'patient_code'              => strtoupper(Str::random(8)),
            'first_name'                => $validated['first_name'],
            'last_name'                 => $validated['last_name'],
            'gender'                    => $validated['gender'],
            'dob'                       => $validated['dob'] ?? null,
            'contact_number'           => $validated['contact_number'] ?? null,
            'admission_date'           => $validated['admission_date'],
            'admission_reason'         => $validated['admission_reason'] ?? null,
            'admitted_by'              => Auth::id(),
            'assigned_nurse_id'        => $validated['assigned_nurse_id'] ?? Auth::id(),
            'room_number'              => $validated['room_number'] ?? null,
            'status'                   => 'active',
            'current_care_level_id'    => $validated['current_care_level_id'] ?? null,
            'next_of_kin_name'         => $validated['next_of_kin_name'],
            'next_of_kin_relationship' => $validated['next_of_kin_relationship'],
            'next_of_kin_contact_number' => $validated['next_of_kin_contact_number'] ?? null,
            'next_of_kin_email'        => $validated['next_of_kin_email'],
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

        return redirect()->route('patients.show', $patient->id)
            ->with('success', 'Patient admitted successfully. Next of kin notified.');
    }
}
