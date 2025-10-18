<?php

namespace App\Services;

use App\Models\ProgressReport;
use App\Models\User;
use Carbon\Carbon;

class ProgressAnalyticsService
{
    /**
     * Compute Improved/Stable/Declined distribution for the dashboard.
     * Strategy:
     * - For each patient, take the latest two reports within the window (if available).
     * - Prefer treatment_goals numeric delta: avg(current - baseline) between R2 (latest) and R1 (previous).
     * - Fallback: symptom severity delta (prev_sum - latest_sum); positive = improvement.
     * - Thresholds: improved if delta >= +1, declined if <= -1, otherwise stable.
     */
    public function distributionForUser(User $user, ?int $days = 30): array
    {
        $from = $days ? Carbon::now()->subDays($days) : null;

        $query = ProgressReport::query()
            ->select([
                'id',
                'patient_id',
                'treatment_goals',
                'depressed_mood',
                'anxiety',
                'sleep_disturbance',
                'appetite_changes',
                'suicidal_ideation',
                'created_at',
            ])
            ->when($from, fn($q) => $q->where('created_at', '>=', $from))
            ->orderBy('patient_id')
            ->orderByDesc('created_at');

        // If you want to restrict to the psychiatrist/nurse’s own reports or patients, adjust here.
        // Example (reports made by the logged-in clinician):
        // if (in_array($user->role, ['psychiatrist', 'nurse'])) {
        //     $query->where('reported_by', $user->id);
        // }

        $reports = $query->get()->groupBy('patient_id');

        $counts = [
            'improved' => 0,
            'stable'   => 0,
            'declined' => 0,
        ];

        $improveThreshold = 1.0;
        $declineThreshold = -1.0;

        foreach ($reports as $patientId => $list) {
            // Latest two for this patient
            $latest = $list->get(0);
            $previous = $list->get(1);

            if (!$latest) {
                continue;
            }

            // Compute delta
            $delta = 0.0;
            $hasGoalDelta = false;

            // Try treatment_goals delta if both have goals
            if ($latest && $previous && is_array($latest->treatment_goals) && is_array($previous->treatment_goals)) {
                $latestAvg = $this->goalsAvgDelta($latest->treatment_goals);
                $prevAvg   = $this->goalsAvgDelta($previous->treatment_goals);

                if (!is_null($latestAvg) && !is_null($prevAvg)) {
                    $delta = $latestAvg - $prevAvg; // improvement if positive
                    $hasGoalDelta = true;
                }
            }

            // Fallback to symptom severity if no goal-based delta
            if (!$hasGoalDelta) {
                if ($latest && $previous) {
                    $latestSeverity = $this->symptomSeveritySum($latest);
                    $prevSeverity   = $this->symptomSeveritySum($previous);

                    if (!is_null($latestSeverity) && !is_null($prevSeverity)) {
                        $delta = $prevSeverity - $latestSeverity; // lower severity = better
                    } else {
                        // Not enough data to judge; treat as stable
                        $counts['stable']++;
                        continue;
                    }
                } else {
                    // Only one report; classify as stable by default
                    $counts['stable']++;
                    continue;
                }
            }

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
            'improved' => round($counts['improved'] / $total * 100),
            'stable'   => round($counts['stable'] / $total * 100),
            'declined' => round($counts['declined'] / $total * 100),
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
     * Average of (current - baseline) across numeric goals.
     * Expects treatment_goals like:
     * [{"goal":"...","baseline":"3","current":"5"}, ...]
     */
    private function goalsAvgDelta(?array $goals): ?float
    {
        if (!$goals || !is_array($goals)) {
            return null;
        }
        $sum = 0.0;
        $n = 0;
        foreach ($goals as $g) {
            if (!is_array($g)) continue;
            $baseline = isset($g['baseline']) ? (float) $g['baseline'] : null;
            $current  = isset($g['current'])  ? (float) $g['current']  : null;
            if ($baseline === null || $current === null) continue;
            // Accept numeric-like strings; float cast handles it
            if (!is_numeric((string)$baseline) || !is_numeric((string)$current)) continue;
            $sum += ((float)$current - (float)$baseline);
            $n++;
        }
        if ($n === 0) return null;
        return $sum / $n;
    }

    /**
     * Sum of selected symptom severities where lower is better.
     */
    private function symptomSeveritySum($report): ?float
    {
        $fields = ['depressed_mood', 'anxiety', 'sleep_disturbance', 'appetite_changes', 'suicidal_ideation'];
        $vals = [];
        foreach ($fields as $f) {
            $v = $report->{$f};
            if (!is_null($v)) {
                $vals[] = (float) $v;
            }
        }
        if (count($vals) === 0) return null;
        return array_sum($vals);
    }
}
