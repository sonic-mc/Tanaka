<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Evaluation;
use App\Traits\AuditLogger;

class EvaluationController extends Controller
{
    use AuditLogger;

    public function index(Request $request)
    {
        // Per-page with sane defaults and allow list
        $perPage = (int) $request->input('per_page', 10);
        $allowed = [10, 25, 50, 100];
        if (! in_array($perPage, $allowed, true)) {
            $perPage = 10;
        }

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
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id','first_name','last_name','patient_code']);

        $evaluations = Evaluation::with([
                'patient:id,first_name,last_name,patient_code,gender,admission_date',
                'evaluator:id,name,email'
            ])
            ->latest()
            ->paginate($perPage)
            ->withQueryString(); // preserve per_page and other query params across pages

        return view('nurse.evaluations.index', compact('evaluations', 'allPatients', 'perPage', 'allowed'));
    }

    public function create()
    {
        $patients = Patient::orderBy('last_name')->orderBy('first_name')->get(['id','first_name','last_name','patient_code']);
        return view('nurse.evaluations.create', compact('patients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => ['required','exists:patients,id'],
            'risk_level' => ['nullable','in:mild,moderate,severe'],
            'notes'      => ['nullable','string'],
            'scores'     => ['nullable'],
        ]);

        $scores = null;
        if (is_array($request->input('scores'))) {
            $scores = collect($request->input('scores'))
                ->filter(fn($v) => $v !== null && $v !== '')
                ->all();
            if (empty($scores)) {
                $scores = null;
            }
        } elseif (is_string($request->input('scores')) && $request->input('scores') !== '') {
            $decoded = json_decode($request->input('scores'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $scores = $decoded;
            }
        }

        $evaluation = Evaluation::create([
            'patient_id'   => $validated['patient_id'],
            'evaluated_by' => auth()->id(),
            'risk_level'   => $validated['risk_level'] ?? null,
            'notes'        => $validated['notes'] ?? null,
            'scores'       => $scores,
        ]);

        try {
            $this->logAudit('Created evaluation', "Evaluation ID {$evaluation->id} created", 'evaluations');
        } catch (\Throwable $e) {
            // ignore audit failures
        }

        return redirect()->route('evaluations.index')
            ->with('success', 'Evaluation recorded successfully.');
    }

    public function show($id)
    {
        $evaluation = Evaluation::with(['patient','evaluator'])->findOrFail($id);
        return view('nurse.evaluations.show', compact('evaluation'));
    }

    public function edit($id)
    {
        $evaluation = Evaluation::with(['patient','evaluator'])->findOrFail($id);
        $patients = Patient::orderBy('last_name')->orderBy('first_name')->get(['id','first_name','last_name','patient_code']);
        return view('nurse.evaluations.edit', compact('evaluation', 'patients'));
    }

    public function update(Request $request, $id)
    {
        $evaluation = Evaluation::findOrFail($id);

        $validated = $request->validate([
            'patient_id' => ['required','exists:patients,id'],
            'risk_level' => ['nullable','in:mild,moderate,severe'],
            'notes'      => ['nullable','string'],
            'scores'     => ['nullable'],
        ]);

        $scores = null;
        if (is_array($request->input('scores'))) {
            $scores = collect($request->input('scores'))
                ->filter(fn($v) => $v !== null && $v !== '')
                ->all();
            if (empty($scores)) {
                $scores = null;
            }
        } elseif (is_string($request->input('scores')) && $request->input('scores') !== '') {
            $decoded = json_decode($request->input('scores'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $scores = $decoded;
            }
        }

        $evaluation->update([
            'patient_id'   => $validated['patient_id'],
            'risk_level'   => $validated['risk_level'] ?? null,
            'notes'        => $validated['notes'] ?? null,
            'scores'       => $scores,
            'evaluated_by' => auth()->id(),
        ]);

        try {
            $this->logAudit('Updated evaluation', "Evaluation ID {$evaluation->id} updated", 'evaluations');
        } catch (\Throwable $e) {}

        return redirect()->route('evaluations.index')
            ->with('success', 'Evaluation updated successfully.');
    }

    public function destroy($id)
    {
        $evaluation = Evaluation::findOrFail($id);
        $evaluation->delete();

        try {
            $this->logAudit('Deleted evaluation', "Evaluation ID {$id}", 'evaluations');
        } catch (\Throwable $e) {}

        return back()->with('success', 'Evaluation deleted.');
    }
}
