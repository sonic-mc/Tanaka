<?php

namespace App\Http\Controllers;

use App\Models\ProgressReport;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;

class ProgressReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reports = ProgressReport::with(['patient', 'reporter'])->latest()->paginate(10);
        return view('nurse.progress.index', compact('reports'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $patients = Patient::all();
        $staff = User::whereIn('role', ['nurse', 'psychiatrist'])->get();
        return view('nurse.progress.create', compact('patients', 'staff'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'reported_by' => 'required|exists:users,id',
            'notes' => 'nullable|string',
            'behavior' => 'nullable|string',
            'medication_response' => 'nullable|string',
            'attendance' => 'required|boolean',
        ]);

        ProgressReport::create($validated);

        return redirect()->route('progress-reports.index')->with('success', 'Progress report submitted.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $report = ProgressReport::with(['patient', 'reporter'])->findOrFail($id);
        return view('admin.progress.show', compact('report'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $report = ProgressReport::findOrFail($id);
        $patients = Patient::all();
        $staff = User::whereIn('role', ['nurse', 'psychiatrist'])->get();
        return view('admin.progress.edit', compact('report', 'patients', 'staff'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $report = ProgressReport::findOrFail($id);

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'reported_by' => 'required|exists:users,id',
            'notes' => 'nullable|string',
            'behavior' => 'nullable|string',
            'medication_response' => 'nullable|string',
            'attendance' => 'required|boolean',
        ]);

        $report->update($validated);

        return redirect()->route('progress-reports.index')->with('success', 'Progress report updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $report = ProgressReport::findOrFail($id);
        $report->delete();

        return redirect()->route('progress-reports.index')->with('success', 'Progress report deleted.');
    }
}
