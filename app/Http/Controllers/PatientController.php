<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\CareLevel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Traits\AuditLogger;
use App\Models\AuditLog;



class PatientController extends Controller
{

    use AuditLogger;
    
    public function index(Request $request)
    {
        $user = Auth::user();
        $careLevels = CareLevel::all();

        // Assigned patients
        $assignedPatients = Patient::where('admitted_by', $user->id)
            ->when($request->assigned_search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('first_name', 'like', '%' . $request->assigned_search . '%')
                    ->orWhere('last_name', 'like', '%' . $request->assigned_search . '%')
                    ->orWhere('patient_code', 'like', '%' . $request->assigned_search . '%');
                });
            })
            ->when($request->assigned_status, fn($q) => $q->where('status', $request->assigned_status))
            ->when($request->assigned_gender, fn($q) => $q->where('gender', $request->assigned_gender))
            ->when($request->assigned_care_level, fn($q) => $q->where('current_care_level_id', $request->assigned_care_level))
            ->with('careLevel')
            ->get();

        // All patients
        $allPatients = Patient::query()
            ->when($request->all_search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('first_name', 'like', '%' . $request->all_search . '%')
                    ->orWhere('last_name', 'like', '%' . $request->all_search . '%')
                    ->orWhere('patient_code', 'like', '%' . $request->all_search . '%');
                });
            })
            ->when($request->all_status, fn($q) => $q->where('status', $request->all_status))
            ->when($request->all_gender, fn($q) => $q->where('gender', $request->all_gender))
            ->when($request->all_care_level, fn($q) => $q->where('current_care_level_id', $request->all_care_level))
            ->with('careLevel')
            ->get();

            
        $nurseId = auth()->id();

        $nurseAssignedPatients = Patient::with(['careLevel', 'assignedNurse'])
        ->whereNotNull('assigned_nurse_id')
        ->when($request->filled('nurse_search'), fn($q) =>
            $q->where(function ($query) use ($request) {
                $query->where('first_name', 'like', '%' . $request->nurse_search . '%')
                      ->orWhere('last_name', 'like', '%' . $request->nurse_search . '%')
                      ->orWhere('patient_code', 'like', '%' . $request->nurse_search . '%');
            })
        )
        ->when($request->filled('nurse_status'), fn($q) => $q->where('status', $request->nurse_status))
        ->when($request->filled('nurse_gender'), fn($q) => $q->where('gender', $request->nurse_gender))
        ->when($request->filled('nurse_care_level'), fn($q) => $q->where('current_care_level_id', $request->nurse_care_level))
        ->orderBy('last_name')
        ->get();
    

        $unassignedPatients = Patient::whereNull('assigned_nurse_id')
            ->with('careLevel')
            ->get();

         // Nurses list
         $nurses = User::query()->role('nurse')->get();
        


      

        return view('nurse.patients.index', compact('assignedPatients', 'allPatients', 'careLevels', 'nurseAssignedPatients', 'unassignedPatients', 'nurses'));
    }

    public function create()
    {
        $careLevels = CareLevel::all();
        $staff = User::whereIn('role', ['admin', 'psychiatrist', 'nurse'])->get();
        return view('admin.patients.create', compact('careLevels', 'staff'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_code' => 'required|unique:patients',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'gender' => 'required|in:male,female,other',
            'dob' => 'nullable|date',
            'contact_number' => 'nullable|string',
            'admission_date' => 'required|date',
            'admission_reason' => 'nullable|string',
            'admitted_by' => 'required|exists:users,id',
            'room_number' => 'nullable|string',
            'status' => 'required|in:active,discharged',
            'current_care_level_id' => 'nullable|exists:care_levels,id',
        ]);

        Patient::create($validated);

        return redirect()->route('patients.index')->with('success', 'Patient admitted successfully.');
    }

    public function show($id)
    {
        $patient = Patient::with(['careLevel', 'admittedBy', 'evaluations', 'progressReports'])->findOrFail($id);
        $evaluations = $patient->evaluations()->with('evaluator')->latest()->get();
        $progressReports = $patient->progressReports()->with('reporter')->latest()->get();
        $billingStatement = $patient->billingStatement;
        return view('nurse.patients.show', compact('patient', 'evaluations', 'progressReports', 'billingStatement'));
    }

    public function edit($id)
    {
        $patient = Patient::findOrFail($id);
        $careLevels = CareLevel::all();
        $staff = User::whereIn('role', ['admin', 'psychiatrist', 'nurse'])->get();
        // Nurses list
        $nurses = User::query()->role('nurse')->get();

        return view('admin.patients.edit', compact('patient', 'careLevels', 'staff', 'nurses'));
    }

    public function update(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);

        $validated = $request->validate([
            'patient_code' => 'required|unique:patients,patient_code,' . $patient->id,
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'gender' => 'required|in:male,female,other',
            'dob' => 'nullable|date',
            'contact_number' => 'nullable|string',
            'admission_date' => 'required|date',
            'admission_reason' => 'nullable|string',
            'admitted_by' => 'required|exists:users,id',
            'room_number' => 'nullable|string',
            'status' => 'required|in:active,discharged',
            'current_care_level_id' => 'nullable|exists:care_levels,id',
        ]);

        $patient->update($validated);

        return redirect()->route('patients.index')->with('success', 'Patient updated successfully.');
    }

    public function destroy($id)
    {
        $patient = Patient::findOrFail($id);
        $patient->delete();

        return redirect()->route('patients.index')->with('success', 'Patient record deleted.');
    }

    public function assignNurse(Request $request, Patient $patient)
    {
        $request->validate([
            'nurse_id' => 'nullable|exists:users,id',
        ]);

        $patient->assigned_nurse_id = $request->nurse_id;
        $patient->save();

        AuditLog::log(
            'Assigned nurse to patient',
            "Patient {$patient->patient_code} assigned to nurse ID {$request->nurse_id}",
            'patients',
            'info'
        );

        return back()->with('success', 'Nurse assignment updated.');
    }


    public function discharge(Patient $patient)
    {
        $patient->status = 'discharged';
        $patient->save();

        return redirect()->route('patients.show', $patient)->with('success', 'Patient discharged successfully.');
    }

}
