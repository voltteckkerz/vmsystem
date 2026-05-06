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
            ['name' => 'Admin',   'email' => 'admin@vmsystem.com'],
            ['name' => 'Guard 1', 'email' => 'guard1@vmsystem.com'],
            ['name' => 'Guard 2', 'email' => 'guard2@vmsystem.com'],
        ];
        foreach ($users as $user) {
            User::create([
                'name'     => $user['name'],
                'email'    => $user['email'],
                'password' => Hash::make('password123'), // same password for all
                ]);
            }
        }
}