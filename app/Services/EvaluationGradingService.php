<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\PatientEvaluation;
use Illuminate\Support\Str;

class EvaluationGradingService
{
    /**
     * Compute grading based on evaluation content (does not persist).
     *
     * Returns an array:
     * - severity_level: mild|moderate|severe|critical
     * - risk_level: low|medium|high
     * - priority_score: int 1..10
     * - rationale: string[] explanation of applied rules
     */
    public function compute(PatientEvaluation $evaluation): array
    {
        $severity = 'mild';
        $risk = 'low';
        $priority = 3;
        $rationale = [];

        $decision = strtolower((string) $evaluation->decision);
        $diagnosis = strtolower((string) $evaluation->diagnosis);
        $recs = strtolower((string) $evaluation->recommendations);
        $notes = strtolower((string) $evaluation->admission_trigger_notes);
        $requiresAdmission = (bool) $evaluation->requires_admission;

        // High-priority keywords
        $criticalKeywords = ['suicid', 'homicid', 'violent', 'aggression', 'psychosis', 'manic', 'mania', 'catatonia'];
        $severeKeywords = ['severe', 'schizo', 'bipolar', 'psychotic', 'self-harm', 'delusion', 'hallucin'];

        // 1) Admission requirement or decision -> critical/high baseline
        if ($decision === 'admit' || $requiresAdmission) {
            $severity = 'critical';
            $risk = 'high';
            $priority = 9;
            $rationale[] = 'Admission required/decided: baseline set to critical/high (priority 9).';
        }

        // 2) Diagnosis-based heuristics (apply if not already at critical)
        if ($severity !== 'critical') {
            if ($this->containsAny($diagnosis, $criticalKeywords)) {
                $severity = 'severe';
                $risk = 'high';
                $priority = max($priority, 8);
                $rationale[] = 'Critical keywords present in diagnosis: set to severe/high (priority >= 8).';
            } elseif ($this->containsAny($diagnosis, ['depress'])) {
                // Depression with severity mention escalates
                if ($this->containsAny($diagnosis, ['severe'])) {
                    $severity = 'severe';
                    $risk = 'high';
                    $priority = max($priority, 8);
                    $rationale[] = 'Severe depression: set to severe/high (priority >= 8).';
                } else {
                    $severity = max($severity, 'moderate', fn($a,$b)=>$this->severityRank($a) <=> $this->severityRank($b));
                    $risk = max($risk, 'medium', fn($a,$b)=>$this->riskRank($a) <=> $this->riskRank($b));
                    $priority = max($priority, 6);
                    $rationale[] = 'Depression: at least moderate/medium (priority >= 6).';
                }
            }
        }

        // 3) Admission trigger notes can escalate to critical/high/10
        if ($this->containsAny($notes, $criticalKeywords)) {
            $severity = 'critical';
            $risk = 'high';
            $priority = max($priority, 10);
            $rationale[] = 'Critical risk in admission notes: escalated to critical/high (priority 10).';
        } elseif ($this->containsAny($notes, $severeKeywords)) {
            if ($this->severityRank($severity) < $this->severityRank('severe')) {
                $severity = 'severe';
            }
            if ($this->riskRank($risk) < $this->riskRank('high')) {
                $risk = 'high';
            }
            $priority = max($priority, 8);
            $rationale[] = 'Severe risk in admission notes: escalated to severe/high (priority >= 8).';
        }

        // 4) Recommendations indicating watch/monitor may lower severity a bit (but never below moderate if severe markers exist)
        if ($this->containsAny($recs, ['monitor', 'watchful waiting', 'follow-up'])) {
            $rationale[] = 'Recommendations suggest monitoring; capping escalation.';
            // Do not actively lower previously escalated severe/critical, just avoid further escalation
        }

        // Final clamps
        $priority = (int) max(1, min(10, $priority));

        return [
            'severity_level' => $severity,
            'risk_level' => $risk,
            'priority_score' => $priority,
            'rationale' => $rationale,
        ];
    }

    /**
     * Apply compute() and persist the grading on the evaluation.
     */
    public function apply(PatientEvaluation $evaluation): PatientEvaluation
    {
        $result = $this->compute($evaluation);

        $evaluation->severity_level = $result['severity_level'];
        $evaluation->risk_level = $result['risk_level'];
        $evaluation->priority_score = $result['priority_score'];
        $evaluation->save();

        return $evaluation->refresh();
    }

    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $n) {
            if (Str::contains($haystack, $n)) {
                return true;
            }
        }
        return false;
    }

    private function severityRank(string $level): int
    {
        return match (strtolower($level)) {
            'mild' => 1,
            'moderate' => 2,
            'severe' => 3,
            'critical' => 4,
            default => 1,
        };
    }

    private function riskRank(string $level): int
    {
        return match (strtolower($level)) {
            'low' => 1,
            'medium' => 2,
            'high' => 3,
            default => 1,
        };
    }
}
