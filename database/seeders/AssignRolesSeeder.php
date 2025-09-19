<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AssignRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles if they don't exist
        $roles = ['admin', 'psychiatrist', 'nurse'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Assign roles to users
        $users = [
            ['email' => 'admin@example.com', 'role' => 'admin'],
            ['email' => 'psyc@example.com', 'role' => 'psychiatrist'],
            ['email' => 'nurse@example.com', 'role' => 'nurse'],
        ];

        foreach ($users as $data) {
            $user = User::where('email', $data['email'])->first();
            if ($user) {
                $user->assignRole($data['role']);
            }
        }
    }
}
