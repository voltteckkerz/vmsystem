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
            ['name' => 'Admin',   'email' => 'admin@vmsystem.com',  'role' => 'admin'],
            ['name' => 'Guard 1', 'email' => 'guard1@vmsystem.com', 'role' => 'guard'],
            ['name' => 'Guard 2', 'email' => 'guard2@vmsystem.com', 'role' => 'guard'],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                [
                    'name'     => $user['name'],
                    'role'     => $user['role'],
                    'password' => Hash::make('password123'),
                ]
            );
        }
    }
}