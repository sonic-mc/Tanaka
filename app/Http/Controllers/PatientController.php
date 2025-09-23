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

        return view('nurse.patients.index', compact('assignedPatients', 'allPatients', 'careLevels'));
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
        return view('admin.patients.edit', compact('patient', 'careLevels', 'staff'));
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
}
