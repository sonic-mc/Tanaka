<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Traits\AuditLogger;





class AdmissionController extends Controller
{

    use AuditLogger;
   
    /**
     * Show admission form.
     */
    public function create()
    {
        // Only nurses can admit patients
        if (!Auth::user()->hasRole('nurse')) {
            abort(403, 'Only nurses can admit patients.');
        }

        // List doctors/nurses if needed for assignment
        $nurses = \App\Models\User::whereHas('roles', function ($q) {
            $q->where('name', 'nurse');
        })->get();

        $staff = User::whereIn('role', ['nurse', 'psychiatrist'])->get();
        


        $careLevels = \App\Models\CareLevel::all();

        return view('admissions.create', compact('nurses', 'careLevels', 'staff'));
    }

    /**
     * Store new admission (admit patient).
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasRole('nurse')) {
            abort(403, 'Only nurses can admit patients.');
        }

        $validated = $request->validate([
            'first_name'         => 'required|string|max:255',
            'last_name'          => 'required|string|max:255',
            'gender'             => 'required|in:male,female,other',
            'dob'                => 'nullable|date',
            'contact_number'     => 'nullable|string|max:20',
            'admission_date'     => 'required|date',
            'admission_reason'   => 'nullable|string',
            'assigned_nurse_id'  => 'nullable|exists:users,id',
            'room_number'        => 'nullable|string|max:50',
            'current_care_level_id' => 'nullable|exists:care_levels,id',
        ]);

        $patient = Patient::create([
            'patient_code'        => strtoupper(Str::random(8)),
            'first_name'          => $validated['first_name'],
            'last_name'           => $validated['last_name'],
            'gender'              => $validated['gender'],
            'dob'                 => $validated['dob'] ?? null,
            'contact_number'      => $validated['contact_number'] ?? null,
            'admission_date'      => $validated['admission_date'],
            'admission_reason'    => $validated['admission_reason'] ?? null,
            'admitted_by'         => Auth::id(), // Nurse admitting
            'assigned_nurse_id'   => $validated['assigned_nurse_id'] ?? Auth::id(),
            'room_number'         => $validated['room_number'] ?? null,
            'status'              => 'active',
            'current_care_level_id' => $validated['current_care_level_id'] ?? null,
        ]);

        // log admission
        \App\Models\AuditLog::log(
            'create',
            "Admitted new patient: {$patient->first_name} {$patient->last_name} (Code: {$patient->patient_code})",
            'patients'
        );

        return redirect()->route('patients.show', $patient->id)
            ->with('success', 'Patient admitted successfully.');
    }
}
