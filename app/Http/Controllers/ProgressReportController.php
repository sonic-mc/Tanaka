<?php

namespace App\Http\Controllers;

use App\Models\PatientDetail;
use App\Models\PatientProgressReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class ProgressReportController extends Controller
{
    /**
     * Index: show patient selector + recent reports overview
     */
    public function index(Request $request)
    {
        $patients = PatientDetail::orderBy('last_name')->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'patient_code']);

        $reports = PatientProgressReport::with(['patient:id,first_name,last_name,patient_code', 'creator:id,name'])
            ->latest()
            ->paginate(12);

        // If a patient is selected, load that patient's reports (for view tab)
        $selectedPatient = null;
        $patientReports = collect();
        if ($request->filled('patient_id')) {
            $selectedPatient = PatientDetail::find($request->integer('patient_id'));
            if ($selectedPatient) {
                $patientReports = PatientProgressReport::with('creator')
                    ->where('patient_id', $selectedPatient->id)
                    ->orderByDesc('report_date')
                    ->get();
            }
        }

        return view('progress_reports.index', compact('patients', 'reports', 'selectedPatient', 'patientReports'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $patients = PatientDetail::orderBy('last_name')->orderBy('first_name')->get();
        return view('progress_reports.create', compact('patients'));
    }

    /**
     * Store a new progress report
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patient_details,id'],
            'report_date' => ['nullable', 'date'],
            // minimal numeric checks for the most used scales
            'phq9_score' => ['nullable', 'integer', 'min:0', 'max:27'],
            'gad7_score' => ['nullable', 'integer', 'min:0', 'max:21'],
            'global_severity_score' => ['nullable', 'numeric'],
            'functional_score' => ['nullable', 'numeric'],
            'risk_level' => ['nullable','in:none,low,moderate,high,critical'],
            'symptom_summary' => ['nullable','string'],
            'observations' => ['nullable','string'],
            'treatment_plan' => ['nullable','string'],
            'medication_changes' => ['nullable','string'],
            'metrics' => ['nullable','array'],
            'attachments' => ['nullable','array'],
            'created_by' => ['nullable','integer'],
        ]);

        $validated['created_by'] = Auth::id();
        $validated['clinician_id'] = Auth::id();
        if (empty($validated['report_date'])) {
            $validated['report_date'] = now()->toDateString();
        }

        DB::beginTransaction();
        try {
            $report = PatientProgressReport::create($validated);

            // Audit hook if available
            if (method_exists($this, 'audit')) {
                $this->audit('info', 'progress-report-created', [
                    'module' => 'progress_reports',
                    'user_id' => Auth::id(),
                    'patient_id' => $report->patient_id,
                    'description' => 'Created progress report id: ' . $report->id,
                ]);
            }

            DB::commit();

            return redirect()->route('progress-reports.show', $report)
                ->with('success', 'Progress report saved.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->withErrors(['error' => 'Could not save report: ' . $e->getMessage()]);
        }
    }

    /**
     * Show single progress report
     */
    public function show($id)
    {
        $report = PatientProgressReport::with(['patient', 'creator'])->findOrFail($id);
        return view('progress_reports.show', compact('report'));
    }

    /**
     * Edit form
     */
    public function edit($id)
    {
        $report = PatientProgressReport::findOrFail($id);
        $patients = PatientDetail::orderBy('last_name')->orderBy('first_name')->get();
        return view('progress_reports.edit', compact('report', 'patients'));
    }

    /**
     * Update existing report
     */
    public function update(Request $request, $id)
    {
        $report = PatientProgressReport::findOrFail($id);

        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patient_details,id'],
            'report_date' => ['nullable', 'date'],
            'phq9_score' => ['nullable', 'integer', 'min:0', 'max:27'],
            'gad7_score' => ['nullable', 'integer', 'min:0', 'max:21'],
            'global_severity_score' => ['nullable', 'numeric'],
            'functional_score' => ['nullable', 'numeric'],
            'risk_level' => ['nullable','in:none,low,moderate,high,critical'],
            'symptom_summary' => ['nullable','string'],
            'observations' => ['nullable','string'],
            'treatment_plan' => ['nullable','string'],
            'medication_changes' => ['nullable','string'],
            'metrics' => ['nullable','array'],
            'attachments' => ['nullable','array'],
        ]);

        $validated['last_modified_by'] = Auth::id();

        DB::beginTransaction();
        try {
            $report->update($validated);

            if (method_exists($this, 'audit')) {
                $this->audit('info', 'progress-report-updated', [
                    'module' => 'progress_reports',
                    'user_id' => Auth::id(),
                    'patient_id' => $report->patient_id,
                    'description' => 'Updated progress report id: ' . $report->id,
                ]);
            }

            DB::commit();

            return redirect()->route('progress-reports.show', $report)->with('success', 'Report updated.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->withErrors(['error' => 'Could not update report: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete
     */
    public function destroy($id)
    {
        $report = PatientProgressReport::findOrFail($id);
        $report->delete();
        return redirect()->route('progress-reports.index')->with('success', 'Report deleted.');
    }

    /**
     * Compare/Trend: show time-series for a patient and provide a simple progress interpretation.
     */
    public function compare(Request $request, $patientId)
    {
        $patient = PatientDetail::findOrFail($patientId);

        // load last N reports (default 12)
        $limit = (int) $request->get('limit', 12);
        $reports = PatientProgressReport::where('patient_id', $patient->id)
            ->orderBy('report_date', 'asc')
            ->take($limit)
            ->get();

        if ($reports->isEmpty()) {
            return redirect()->route('progress-reports.index', ['patient_id' => $patient->id])
                ->with('warning', 'No progress reports available for that patient.');
        }

        // Build time-series arrays for charting
        $series = [
            'labels' => $reports->map(fn($r) => $r->report_date->format('Y-m-d'))->all(),
            'phq9' => $reports->map(fn($r) => $r->phq9_score !== null ? (int)$r->phq9_score : null)->all(),
            'gad7' => $reports->map(fn($r) => $r->gad7_score !== null ? (int)$r->gad7_score : null)->all(),
            'global' => $reports->map(fn($r) => $r->global_severity_score !== null ? (float)$r->global_severity_score : null)->all(),
            'functional' => $reports->map(fn($r) => $r->functional_score !== null ? (float)$r->functional_score : null)->all(),
            'risk' => $reports->map(fn($r) => $r->risk_level)->all(),
        ];

        // Compute simple comparisons: last vs previous and last vs baseline
        $last = $reports->last();
        $previous = $reports->count() >= 2 ? $reports->slice(-2,1)->first() : null;
        $baseline = $reports->first();

        $compare = $this->interpretChanges($baseline, $previous, $last);

        return view('progress_reports.compare', compact('patient', 'reports', 'series', 'compare'));
    }

    /**
     * Export patient's reports as CSV (streamed)
     */
    public function exportCsv($patientId)
    {
        $patient = PatientDetail::findOrFail($patientId);
        $reports = PatientProgressReport::where('patient_id', $patient->id)->orderBy('report_date')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="progress_reports_' . $patient->patient_code . '.csv"',
        ];

        $columns = [
            'report_date','phq9_score','gad7_score','global_severity_score','functional_score','risk_level','symptom_summary','observations','treatment_plan','created_by'
        ];

        $callback = function () use ($reports, $columns) {
            $handle = fopen('php://output', 'w');
            // header
            fputcsv($handle, array_merge(['patient_code','patient_name'], $columns));

            foreach ($reports as $r) {
                $row = [
                    $r->patient->patient_code ?? '',
                    ($r->patient->first_name ?? '') . ' ' . ($r->patient->last_name ?? ''),
                ];
                foreach ($columns as $col) {
                    $row[] = is_array($r->$col) ? json_encode($r->$col) : ($r->$col ?? '');
                }
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Interpret changes across baseline, previous and last report and produce human-friendly summary.
     */
    protected function interpretChanges($baseline, $previous, $last)
    {
        // Helper to determine direction for a metric where lower is better (e.g. PHQ9, GAD7, global)
        $directionLowBetter = fn($metricName) => in_array($metricName, ['phq9_score','gad7_score','global_severity_score']);

        $metrics = ['phq9_score', 'gad7_score', 'global_severity_score', 'functional_score'];

        $summary = [];
        foreach ($metrics as $m) {
            $base = $baseline ? $baseline->$m : null;
            $prev = $previous ? $previous->$m : null;
            $curr = $last ? $last->$m : null;

            $deltaPrev = ($prev !== null && $curr !== null) ? ($curr - $prev) : null;
            $deltaBase = ($base !== null && $curr !== null) ? ($curr - $base) : null;

            $interpret = 'No change';
            if ($deltaPrev !== null) {
                if ($deltaPrev === 0) $interpret = 'No short-term change';
                else {
                    $improved = $directionLowBetter($m) ? ($deltaPrev < 0) : ($deltaPrev > 0);
                    $interpret = $improved ? 'Improved (recent)' : 'Worsened (recent)';
                }
            } elseif ($deltaBase !== null) {
                $improved = $directionLowBetter($m) ? ($deltaBase < 0) : ($deltaBase > 0);
                $interpret = $improved ? 'Improved (vs baseline)' : 'Worsened (vs baseline)';
            }

            $summary[$m] = [
                'baseline' => $base,
                'previous' => $prev,
                'current' => $curr,
                'delta_prev' => $deltaPrev,
                'delta_base' => $deltaBase,
                'interpretation' => $interpret,
            ];
        }

        // Risk level quick note (last vs previous)
        $riskNote = 'No change';
        if ($previous && $last) {
            if ($previous->risk_level !== $last->risk_level) {
                $riskNote = "Risk changed from {$previous->risk_level} to {$last->risk_level}";
            } else {
                $riskNote = "Risk remains {$last->risk_level}";
            }
        } elseif ($last) {
            $riskNote = "Current risk: {$last->risk_level}";
        }

        return [
            'metrics' => $summary,
            'risk_note' => $riskNote,
            'last_report' => $last,
            'previous_report' => $previous,
            'baseline_report' => $baseline,
        ];
    }
}
