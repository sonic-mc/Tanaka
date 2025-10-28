<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Admin User', 'email' => 'admin@example.com', 'role' => 'admin'],
            ['name' => 'Psychiatrist User', 'email' => 'psyc@example.com', 'role' => 'psychiatrist'],
            ['name' => 'Nurse User', 'email' => 'nurse@example.com', 'role' => 'nurse'],
        ];

        foreach ($users as $data) {
            User::create(
                [
                    'email' => $data['email'],    
                    'name' => $data['name'],
                    'role' => $data['role'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}