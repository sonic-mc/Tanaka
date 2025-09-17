<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Evaluation;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Str;

class EvaluationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch all patients
        $patients = Patient::all();

        // Fetch all doctors/nurses
        $staff = User::whereIn('role', ['doctor', 'nurse'])->get();

        $riskLevels = ['mild', 'moderate', 'severe'];

        foreach ($patients as $patient) {
            // Each patient gets 1–3 evaluations
            $numEvaluations = rand(1, 3);

            for ($i = 0; $i < $numEvaluations; $i++) {
                Evaluation::create([
                    'patient_id' => $patient->id,
                    'evaluated_by' => $staff->random()->id,
                    'notes' => "Patient shows " . Str::random(20) . " behavior and response.",
                    'risk_level' => $riskLevels[array_rand($riskLevels)],
                    'scores' => json_encode([
                        'mobility' => rand(1, 10),
                        'cognition' => rand(1, 10),
                        'mood' => rand(1, 10),
                    ]),
                ]);
            }
        }
    }
}
