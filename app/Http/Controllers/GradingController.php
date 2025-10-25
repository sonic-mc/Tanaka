<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PatientEvaluation;
use App\Services\EvaluationGradingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradingController extends Controller
{
    public function __construct(private EvaluationGradingService $grading)
    {
        $this->middleware('auth');
    }

    // List evaluations with current grading and filters + chart data
    public function index(Request $request)
    {
        $filters = [
            'q' => $request->get('q'),
            'severity' => $request->get('severity'),
            'risk' => $request->get('risk'),
            'decision' => $request->get('decision'),
        ];

        $query = PatientEvaluation::query()
            ->with(['patient', 'psychiatrist'])
            ->latest('evaluation_date');

        // Apply filters to the list
        if ($filters['severity']) {
            $query->where('severity_level', $filters['severity']);
        }
        if ($filters['risk']) {
            $query->where('risk_level', $filters['risk']);
        }
        if ($filters['decision']) {
            $query->where('decision', $filters['decision']);
        }
        if ($filters['q']) {
            $q = $filters['q'];
            $query->whereHas('patient', function ($sub) use ($q) {
                $sub->where('patient_code', 'like', "%{$q}%")
                    ->orWhere('first_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%");
            });
        }

        $evaluations = $query->paginate(20)->withQueryString();

        // Build chart stats over the filtered set, using the latest evaluation per patient
        // 1) Base filtered set (for raw queries)
        $base = DB::table('patient_evaluations as pe')
            ->leftJoin('patient_details as pd', 'pd.id', '=', 'pe.patient_id')
            ->whereNull('pe.deleted_at');

        if ($filters['decision']) {
            $base->where('pe.decision', $filters['decision']);
        }
        if ($filters['severity']) {
            $base->where('pe.severity_level', $filters['severity']);
        }
        if ($filters['risk']) {
            $base->where('pe.risk_level', $filters['risk']);
        }
        if ($filters['q']) {
            $q = $filters['q'];
            $base->where(function ($w) use ($q) {
                $w->where('pd.patient_code', 'like', "%{$q}%")
                  ->orWhere('pd.first_name', 'like', "%{$q}%")
                  ->orWhere('pd.last_name', 'like', "%{$q}%");
            });
        }

        // 2) Latest evaluation date per patient (within filtered base)
        $latestDates = DB::table('patient_evaluations as pe1')
            ->leftJoin('patient_details as pd1', 'pd1.id', '=', 'pe1.patient_id')
            ->whereNull('pe1.deleted_at');

        if ($filters['decision']) {
            $latestDates->where('pe1.decision', $filters['decision']);
        }
        if ($filters['severity']) {
            $latestDates->where('pe1.severity_level', $filters['severity']);
        }
        if ($filters['risk']) {
            $latestDates->where('pe1.risk_level', $filters['risk']);
        }
        if ($filters['q']) {
            $q = $filters['q'];
            $latestDates->where(function ($w) use ($q) {
                $w->where('pd1.patient_code', 'like', "%{$q}%")
                  ->orWhere('pd1.first_name', 'like', "%{$q}%")
                  ->orWhere('pd1.last_name', 'like', "%{$q}%");
            });
        }

        $latestDates = $latestDates
            ->select('pe1.patient_id', DB::raw('MAX(pe1.evaluation_date) as latest_date'))
            ->groupBy('pe1.patient_id');

        // 3) For those latest dates, pick the latest id to break ties
        $latestIds = DB::table('patient_evaluations as pe2')
            ->joinSub($latestDates, 'ld', function ($join) {
                $join->on('pe2.patient_id', '=', 'ld.patient_id')
                     ->on('pe2.evaluation_date', '=', 'ld.latest_date');
            })
            ->select('pe2.patient_id', DB::raw('MAX(pe2.id) as latest_id'))
            ->groupBy('pe2.patient_id');

        // 4) Join to obtain the latest evaluation rows and compute distributions
        $latestEval = DB::table('patient_evaluations as pe')
            ->joinSub($latestIds, 'li', function ($join) {
                $join->on('pe.patient_id', '=', 'li.patient_id')
                     ->on('pe.id', '=', 'li.latest_id');
            })
            ->whereNull('pe.deleted_at');

        // Severity distribution (patients by latest severity)
        $severityRaw = (clone $latestEval)
            ->select('pe.severity_level', DB::raw('COUNT(*) as total'))
            ->groupBy('pe.severity_level')
            ->pluck('total', 'pe.severity_level')
            ->toArray();

        // Risk distribution (patients by latest risk)
        $riskRaw = (clone $latestEval)
            ->select('pe.risk_level', DB::raw('COUNT(*) as total'))
            ->groupBy('pe.risk_level')
            ->pluck('total', 'pe.risk_level')
            ->toArray();

        // Normalize to fixed order for charts
        $severityOrder = ['mild', 'moderate', 'severe', 'critical'];
        $riskOrder = ['low', 'medium', 'high'];

        $severityCounts = [];
        foreach ($severityOrder as $s) {
            $severityCounts[$s] = (int) ($severityRaw[$s] ?? 0);
        }
        $riskCounts = [];
        foreach ($riskOrder as $r) {
            $riskCounts[$r] = (int) ($riskRaw[$r] ?? 0);
        }

        $chartData = [
            'severity' => [
                'labels' => array_map('ucfirst', $severityOrder),
                'data' => array_values($severityCounts),
                'colors' => ['#22c55e', '#f59e0b', '#ef4444', '#111827'], // mild, moderate, severe, critical
            ],
            'risk' => [
                'labels' => array_map('ucfirst', $riskOrder),
                'data' => array_values($riskCounts),
                'colors' => ['#22c55e', '#f59e0b', '#ef4444'], // low, medium, high
            ],
        ];

        return view('grading.index', [
            'evaluations' => $evaluations,
            'filters' => $filters,
            'severityCounts' => $severityCounts,
            'riskCounts' => $riskCounts,
            'chartData' => $chartData,
        ]);
    }

    // Show a single evaluation and computed grading (live preview)
    public function show(PatientEvaluation $evaluation)
    {
        $preview = $this->grading->compute($evaluation);
        return view('grading.show', [
            'evaluation' => $evaluation->load(['patient', 'psychiatrist']),
            'preview' => $preview,
        ]);
    }

    // Persist recalculated grading using current rules
    public function recalculate(PatientEvaluation $evaluation)
    {
        $this->grading->apply($evaluation);
        return redirect()
            ->route('grading.show', $evaluation)
            ->with('success', 'Grading recalculated and saved.');
    }
}
