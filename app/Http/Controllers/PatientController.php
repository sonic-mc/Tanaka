<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\CareLevel;
use App\Models\User;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index()
    {
        $patients = Patient::with('careLevel', 'admittedBy')->latest()->paginate(10);
        return view('admin.patients.index', compact('patients'));
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
        return view('admin.patients.show', compact('patient'));
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
