<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\PatientDetail;
use App\Models\User;
use App\Models\CareLevel;
use Faker\Factory as Faker;

class PatientDetailSeeder extends Seeder
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
            PatientDetail::create([
                'patient_code'           => 'PT' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'first_name'             => $faker->firstName,
                'middle_name'            => $faker->optional()->firstName,
                'last_name'              => $faker->lastName,
                'gender'                 => $faker->randomElement(['male','female','other']),
                'dob'                    => $faker->optional()->date('Y-m-d', '2005-01-01'),
                'national_id_number' => $faker->boolean ? $faker->unique()->numerify('##########') : null,
                'passport_number'    => $faker->boolean ? $faker->unique()->bothify('P########') : null,

                'photo'                  => null,
            
                'email'                  => $faker->optional()->safeEmail,
                'contact_number'         => $faker->optional()->phoneNumber,
                'residential_address'    => $faker->optional()->address,
                'race'                   => $faker->optional()->word,
                'religion'               => $faker->optional()->word,
                'language'               => $faker->optional()->word,
                'denomination'           => $faker->optional()->word,
                'marital_status'         => $faker->optional()->randomElement(['single','married','divorced','widowed']),
                'occupation'             => $faker->optional()->jobTitle,
            
                'blood_group'            => $faker->optional()->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
                'allergies'              => $faker->optional()->sentence,
                'disabilities'           => $faker->optional()->sentence,
                'special_diet'           => $faker->optional()->sentence,
                'medical_aid_provider'   => $faker->optional()->company,
                'medical_aid_number'     => $faker->optional()->numerify('########'),
                'special_medical_requirements' => $faker->optional()->paragraph,
                'current_medications'    => $faker->optional()->paragraph,
                'past_medical_history'   => $faker->optional()->paragraph,
            
                'next_of_kin_name'           => $faker->optional()->name,
                'next_of_kin_relationship'   => $faker->optional()->word,
                'next_of_kin_contact_number' => $faker->optional()->phoneNumber,
                'next_of_kin_email'          => $faker->optional()->safeEmail,
                'next_of_kin_address'        => $faker->optional()->address,
                'created_by' => $faker->randomElement($staffIds),

            ]);
            
        }
    }
}
