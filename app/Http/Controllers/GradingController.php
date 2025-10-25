<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PatientEvaluation;
use App\Services\EvaluationGradingService;
use Illuminate\Http\Request;

class GradingController extends Controller
{
    public function __construct(private EvaluationGradingService $grading)
    {
        $this->middleware('auth');
    }

    // List evaluations with current grading and filters
    public function index(Request $request)
    {
        $query = PatientEvaluation::query()
            ->with(['patient', 'psychiatrist'])
            ->latest('evaluation_date');

        if ($s = $request->string('severity')->toString()) {
            $query->where('severity_level', $s);
        }
        if ($r = $request->string('risk')->toString()) {
            $query->where('risk_level', $r);
        }
        if ($d = $request->string('decision')->toString()) {
            $query->where('decision', $d);
        }
        if ($q = $request->string('q')->toString()) {
            $query->whereHas('patient', function ($sub) use ($q) {
                $sub->where('patient_code', 'like', "%{$q}%")
                    ->orWhere('first_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%");
            });
        }

        $evaluations = $query->paginate(20)->withQueryString();

        return view('grading.index', [
            'evaluations' => $evaluations,
            'filters' => [
                'q' => $request->get('q'),
                'severity' => $request->get('severity'),
                'risk' => $request->get('risk'),
                'decision' => $request->get('decision'),
            ],
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
