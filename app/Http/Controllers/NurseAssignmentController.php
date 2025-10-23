<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNurseAssignmentRequest;
use App\Http\Requests\UpdateNurseAssignmentRequest;
use App\Models\Admission;
use App\Models\NursePatientAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NurseAssignmentController extends Controller
{
    // List with filters
    public function index(Request $request)
    {
        $query = NursePatientAssignment::query()
            ->with([
                'nurse:id,name',
                // Removed assignedPsychiatrist from eager load to avoid missing relation
                'admission' => function ($q) {
                    $q->with(['patient:id,first_name,middle_name,last_name,patient_code', 'careLevel']);
                },
                'assignedBy:id,name',
            ])
            ->latest();

        // Filters
        if ($nurseId = $request->integer('nurse_id')) {
            $query->where('nurse_id', $nurseId);
        }
        if ($shift = $request->get('shift')) {
            $query->where('shift', $shift);
        }
        if ($from = $request->get('from')) {
            $query->whereDate('assigned_date', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('assigned_date', '<=', $to);
        }
        if ($q = $request->get('q')) {
            $query->whereHas('admission.patient', function ($sub) use ($q) {
                $sub->where('patient_code', 'like', "%{$q}%")
                    ->orWhere('first_name', 'like', "%{$q}%")
                    ->orWhere('middle_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%");
            });
        }

        $assignments = $query->paginate(20)->withQueryString();

        // Nurses only
        $nurses = User::where('role', 'nurse')->orderBy('name')->get(['id', 'name']);

        return view('nurse_assignments.index', [
            'assignments' => $assignments,
            'nurses' => $nurses,
            'filters' => [
                'nurse_id' => $request->get('nurse_id'),
                'shift' => $request->get('shift'),
                'from' => $request->get('from'),
                'to' => $request->get('to'),
                'q' => $request->get('q'),
            ],
        ]);
    }

    public function create(Request $request)
    {
        // Nurses only
        $nurses = User::where('role', 'nurse')->orderBy('name')->get(['id', 'name']);

        // Only active admissions; eager-load patient only (removed assignedPsychiatrist to avoid missing relation)
        $admissions = Admission::where('status', 'active')
            ->with(['patient:id,first_name,middle_name,last_name,patient_code'])
            ->orderByDesc('admission_date')
            ->get(['id', 'patient_id', 'room_number', 'admission_date', 'assigned_psychiatrist_id', 'status']);

        $prefillAdmissionId = $request->integer('admission_id');

        return view('nurse_assignments.create', compact('nurses', 'admissions', 'prefillAdmissionId'));
    }

    public function store(StoreNurseAssignmentRequest $request)
    {
        // Prevent duplicate assignment for same nurse/admission/shift on same date
        $assignedDate = $request->assigned_date ?: now()->toDateString();
        $exists = NursePatientAssignment::where('nurse_id', $request->nurse_id)
            ->where('admission_id', $request->admission_id)
            ->when($request->shift, fn($q) => $q->where('shift', $request->shift))
            ->whereDate('assigned_date', $assignedDate)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['admission_id' => 'This nurse is already assigned to this admission for the selected date/shift.']);
        }

        NursePatientAssignment::create([
            'nurse_id' => $request->nurse_id,
            'admission_id' => $request->admission_id,
            'shift' => $request->shift,
            'assigned_date' => $assignedDate,
            'notes' => $request->notes,
            'assigned_by' => Auth::id(),
        ]);

        return redirect()->route('nurse-assignments.index')->with('success', 'Patient assigned to nurse.');
    }

    public function edit(NursePatientAssignment $nurse_assignment)
    {
        $nurses = User::where('role', 'nurse')->orderBy('name')->get(['id', 'name']);
        $admissions = Admission::where('status', 'active')
            ->with(['patient:id,first_name,middle_name,last_name,patient_code'])
            ->orderByDesc('admission_date')
            ->get(['id', 'patient_id', 'room_number', 'admission_date']);

        return view('nurse_assignments.edit', [
            'assignment' => $nurse_assignment,
            'nurses' => $nurses,
            'admissions' => $admissions,
        ]);
    }

    public function update(UpdateNurseAssignmentRequest $request, NursePatientAssignment $nurse_assignment)
    {
        $assignedDate = $request->assigned_date ?: ($nurse_assignment->assigned_date?->format('Y-m-d') ?? now()->toDateString());

        $duplicate = NursePatientAssignment::where('id', '<>', $nurse_assignment->id)
            ->where('nurse_id', $request->nurse_id)
            ->where('admission_id', $request->admission_id)
            ->when($request->shift, fn($q) => $q->where('shift', $request->shift))
            ->whereDate('assigned_date', $assignedDate)
            ->exists();

        if ($duplicate) {
            return back()
                ->withInput()
                ->withErrors(['admission_id' => 'This nurse is already assigned to this admission for the selected date/shift.']);
        }

        $nurse_assignment->update([
            'nurse_id' => $request->nurse_id,
            'admission_id' => $request->admission_id,
            'shift' => $request->shift,
            'assigned_date' => $assignedDate,
            'notes' => $request->notes,
        ]);

        return redirect()->route('nurse-assignments.index')->with('success', 'Assignment updated.');
    }

    // Unassign (delete)
    public function destroy(NursePatientAssignment $nurse_assignment)
    {
        $nurse_assignment->delete();
        return redirect()->route('nurse-assignments.index')->with('success', 'Assignment removed.');
    }
}
