<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Admin', 'email' => 'admin@vmsystem.test', 'role' => 'admin'],
            ['name' => 'Supervisor', 'email' => 'supervisor@vmsystem.test', 'role' => 'supervisor'],
            ['name' => 'Guard', 'email' => 'guard@vmsystem.test', 'role' => 'guard'],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'password' => Hash::make('password'),
                ]
            );
        }
    }
}
