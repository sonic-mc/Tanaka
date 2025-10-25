<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\PatientProgressReport;
use App\Models\User;
use Carbon\Carbon;

class ProgressAnalyticsService
{
    /**
     * @var array<string,mixed>
     */
    private array $config;

    public function __construct(?array $config = null)
    {
        $this->config = array_replace_recursive($this->defaultConfig(), $config ?? []);
    }

    /**
     * Compute Improved/Stable/Declined distribution for the dashboard over a time window.
     */
    public function distributionForUser(User $user, ?int $days = 30): array
    {
        $from = $days ? Carbon::now()->subDays($days)->startOfDay() : null;

        $query = PatientProgressReport::query()
            ->select([
                'id',
                'patient_id',
                'clinician_id',
                'report_date',
                'gaf_score',
                'phq9_score',
                'gad7_score',
                'who_das_score',
                'honos_score',
                'bprs_score',
                'cgi_severity',
                'global_severity_score',
                'functional_score',
                'risk_level',
                'metrics',
                'created_at',
                'deleted_at',
            ])
            ->whereNull('deleted_at'); // ✅ valid Eloquent syntax

        if ($from) {
            $query->where(function ($q) use ($from) {
                $q->where(function ($qq) use ($from) {
                    $qq->whereNotNull('report_date')
                       ->where('report_date', '>=', $from->toDateString());
                })->orWhere(function ($qq) use ($from) {
                    $qq->whereNull('report_date')
                       ->where('created_at', '>=', $from);
                });
            });
        }

        // Optional clinician scope:
        // if (method_exists($user, 'hasRole') && ($user->hasRole('psychiatrist') || $user->hasRole('nurse'))) {
        //     $query->where('clinician_id', $user->id);
        // }

        $query->orderBy('patient_id')
              ->orderByRaw('(report_date IS NULL) ASC')
              ->orderByDesc('report_date')
              ->orderByDesc('created_at');

        $reportsByPatient = $query->get()->groupBy('patient_id');

        $counts = [
            'improved' => 0,
            'stable'   => 0,
            'declined' => 0,
        ];

        $improveThreshold = (float) ($this->config['delta_threshold'] ?? 0.10);
        $declineThreshold = -$improveThreshold;

        foreach ($reportsByPatient as $list) {
            $latest   = $list->get(0);
            $previous = $list->get(1);

            if (!$latest) {
                continue;
            }

            $sLatest = $this->computeCompositeSeverity($latest);
            $sPrev   = $previous ? $this->computeCompositeSeverity($previous) : null;

            if ($sLatest === null || $sPrev === null) {
                $counts['stable']++;
                continue;
            }

            $delta = $sPrev - $sLatest;

            if ($delta >= $improveThreshold) {
                $counts['improved']++;
            } elseif ($delta <= $declineThreshold) {
                $counts['declined']++;
            } else {
                $counts['stable']++;
            }
        }

        $total = max(1, ($counts['improved'] + $counts['stable'] + $counts['declined']));
        $percentages = [
            'improved' => (int) round($counts['improved'] / $total * 100),
            'stable'   => (int) round($counts['stable'] / $total * 100),
            'declined' => (int) round($counts['declined'] / $total * 100),
        ];

        return [
            'counts' => $counts + ['total' => $total],
            'percentages' => $percentages,
            'chart' => [
                'labels' => ['Improved', 'Stable', 'Declined'],
                'data'   => [$counts['improved'], $counts['stable'], $counts['declined']],
                'colors' => ['#22c55e', '#f59e0b', '#ef4444'],
            ],
        ];
    }

    /**
     * Compute a 0..1 composite severity score where higher means worse condition.
     */
    private function computeCompositeSeverity(PatientProgressReport $report): ?float
    {
        $cfg = $this->config;
        $components = [];

        $push = function (?float $val, string $key, bool $higherIsWorse, float $max) use (&$components, $cfg): void {
            if ($val === null || $max <= 0.0) {
                return;
            }

            $norm = max(0.0, min(1.0, $val / $max));
            $severity = $higherIsWorse ? $norm : (1.0 - $norm);

            $weight = (float) ($cfg['weights'][$key] ?? 0.0);
            if ($weight <= 0.0) {
                return;
            }

            $components[] = ['severity' => $severity, 'weight' => $weight];
        };

        $getNum = static function ($v): ?float {
            return is_numeric($v) ? (float) $v : null;
        };

        // Collect all measures
        $push($getNum($report->global_severity_score), 'global_severity_score', true, $cfg['maxima']['global_severity_score']);
        $push($getNum($report->phq9_score), 'phq9_score', true, $cfg['maxima']['phq9_score']);
        $push($getNum($report->gad7_score), 'gad7_score', true, $cfg['maxima']['gad7_score']);
        $push($getNum($report->who_das_score), 'who_das_score', true, $cfg['maxima']['who_das_score']);
        $push($getNum($report->honos_score), 'honos_score', true, $cfg['maxima']['honos_score']);
        $push($getNum($report->bprs_score), 'bprs_score', true, $cfg['maxima']['bprs_score']);
        $push($getNum($report->cgi_severity), 'cgi_severity', true, $cfg['maxima']['cgi_severity']);
        $push($getNum($report->gaf_score), 'gaf_score', false, $cfg['maxima']['gaf_score']);
        $push($getNum($report->functional_score), 'functional_score', false, $cfg['maxima']['functional_score']);

        // Decode metrics JSON
        $metrics = $report->metrics;
        if (is_string($metrics)) {
            $decoded = json_decode($metrics, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $metrics = $decoded;
            } else {
                $metrics = null;
            }
        }

        if (is_array($metrics)) {
            foreach (['severity', 'global_score', 'composite_severity'] as $mKey) {
                if (isset($metrics[$mKey]) && is_numeric($metrics[$mKey])) {
                    $push((float) $metrics[$mKey], 'metrics_severity', true, $cfg['maxima']['metrics_severity']);
                    break;
                }
            }
        }

        if (empty($components)) {
            $riskPenalty = $this->riskPenalty($report->risk_level);
            return $riskPenalty > 0 ? min(1.0, $riskPenalty) : null;
        }

        $num = 0.0;
        $den = 0.0;
        foreach ($components as $c) {
            $num += $c['severity'] * $c['weight'];
            $den += $c['weight'];
        }

        if ($den <= 0.0) {
            return null;
        }

        $baseSeverity = $num / $den;
        $severity = $baseSeverity + $this->riskPenalty($report->risk_level);

        return max(0.0, min(1.0, $severity));
    }

    private function riskPenalty(?string $riskLevel): float
    {
        $map = $this->config['risk_penalty'] ?? [];
        return (float) ($map[$riskLevel ?? 'none'] ?? 0.0);
    }

    private function defaultConfig(): array
    {
        return [
            'maxima' => [
                'global_severity_score' => 100.0,
                'phq9_score' => 27.0,
                'gad7_score' => 21.0,
                'who_das_score' => 100.0,
                'honos_score' => 48.0,
                'bprs_score' => 126.0,
                'cgi_severity' => 7.0,
                'gaf_score' => 100.0,
                'functional_score' => 100.0,
                'metrics_severity' => 100.0,
            ],
            'weights' => [
                'global_severity_score' => 3.0,
                'phq9_score' => 1.5,
                'gad7_score' => 1.5,
                'who_das_score' => 1.0,
                'honos_score' => 1.0,
                'bprs_score' => 1.0,
                'cgi_severity' => 1.0,
                'gaf_score' => 1.5,
                'functional_score' => 1.5,
                'metrics_severity' => 1.0,
            ],
            'delta_threshold' => 0.10,
            'risk_penalty' => [
                'none' => 0.00,
                'low' => 0.02,
                'moderate' => 0.05,
                'high' => 0.10,
                'critical' => 0.15,
            ],
        ];
    }
}
