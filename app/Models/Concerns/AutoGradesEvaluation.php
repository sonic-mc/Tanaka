<?php
declare(strict_types=1);

namespace App\Models\Concerns;

use App\Services\EvaluationGradingService;

trait AutoGradesEvaluation
{
    public static function bootAutoGradesEvaluation(): void
    {
        static::saving(function ($model) {
            // Recompute grading when key fields change
            if ($model->isDirty(['decision', 'requires_admission', 'diagnosis', 'admission_trigger_notes', 'recommendations'])) {
                app(EvaluationGradingService::class)->apply($model);
                // Ensure we don't recurse infinitely; fields are already set in apply()
            }
        });
    }
}
