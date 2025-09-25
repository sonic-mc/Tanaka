<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Evaluation;
use App\Models\User;
use App\Traits\AuditLogger;

class EvaluationController extends Controller
{
    use AuditLogger;

    public function index(Request $request)
    {
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

        $evaluations = Evaluation::with(['patient', 'evaluator'])->latest()->paginate(10);

        return view('nurse.evaluations.index', compact('evaluations', 'allPatients'));
    }

    public function create()
    {
        $patients = Patient::orderBy('last_name')->get();
        return view('nurse.evaluations.create', compact('patients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'risk_level' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'scores' => 'nullable|json',
        ]);

        $evaluation = Evaluation::create([
            'patient_id' => $validated['patient_id'],
            'evaluated_by' => auth()->id(),
            'risk_level' => $validated['risk_level'],
            'notes' => $validated['notes'],
            'scores' => $validated['scores'],
        ]);

        AuditLogger::log('Created evaluation', "Patient ID {$evaluation->patient_id}, Risk: {$evaluation->risk_level}", 'evaluations');

        return redirect()->route('evaluations.index')->with('success', 'Evaluation recorded successfully.');
    }

    public function show($id)
    {
        $evaluation = Evaluation::with(['patient', 'evaluator'])->findOrFail($id);
        return view('nurse.evaluations.show', compact('evaluation'));
    }

    public function edit($id)
    {
        $evaluation = Evaluation::findOrFail($id);
        $patients = Patient::orderBy('last_name')->get();
        return view('nurse.evaluations.edit', compact('evaluation', 'patients'));
    }

    public function update(Request $request, $id)
    {
        $evaluation = Evaluation::findOrFail($id);

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'risk_level' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'scores' => 'nullable|json',
        ]);

        $evaluation->update([
            'patient_id' => $validated['patient_id'],
            'risk_level' => $validated['risk_level'],
            'notes' => $validated['notes'],
            'scores' => $validated['scores'],
        ]);

        AuditLogger::log('Updated evaluation', "Evaluation ID {$evaluation->id}", 'evaluations');

        return redirect()->route('evaluations.index')->with('success', 'Evaluation updated successfully.');
    }

    public function destroy($id)
    {
        $evaluation = Evaluation::findOrFail($id);
        $evaluation->delete();

        AuditLogger::log('Deleted evaluation', "Evaluation ID {$id}", 'evaluations');

        return back()->with('success', 'Evaluation deleted.');
    }
}

