<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Patient;
use App\Models\User;
use App\Models\CareLevel;
use Faker\Factory as Faker;

class PatientSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Get some users for 'admitted_by'
        $staffIds = User::pluck('id')->toArray();
        $careLevelIds = CareLevel::pluck('id')->toArray();

        if (empty($staffIds)) {
            $this->command->info('No users found. Please seed users first!');
            return;
        }

        if (empty($careLevelIds)) {
            $this->command->info('No care levels found. Please seed care_levels first!');
            return;
        }

        for ($i = 1; $i <= 10; $i++) {
            Patient::create([
                'patient_code' => 'PT' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'first_name'   => $faker->firstName,
                'last_name'    => $faker->lastName,
                'gender'       => $faker->randomElement(['male','female','other']),
                'dob'          => $faker->date('Y-m-d', '2005-01-01'),
                'contact_number' => $faker->phoneNumber,
                'admission_date' => $faker->date('Y-m-d', 'now'),
                'admission_reason' => $faker->sentence(6),
                'admitted_by' => $faker->randomElement($staffIds),
                'room_number' => $faker->numberBetween(101, 120),
                'status' => $faker->randomElement(['active', 'discharged']),
                'current_care_level_id' => $faker->randomElement($careLevelIds),
            ]);
        }
    }
}
