<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CareLevel;

class CareLevelSeeder extends Seeder
{
    public function run()
    {
        $levels = ['High Dependency', 'Standard Care', 'Observation', 'Rehabilitation'];

        foreach ($levels as $level) {
            CareLevel::create([
                'name' => $level,
                'description' => $level . ' patients',
            ]);
        }
    }
}
