<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure the Admin role exists
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);

        // Create the test user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Assign the Admin role
        $user->assignRole($adminRole);
    }
}


