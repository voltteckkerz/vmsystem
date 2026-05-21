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
                ['name' => 'Admin',      'email' => 'admin@vms.com',  'role' => 'admin',            'password' => 'adminpass123'],
                ['name' => 'Shanorputra Shafei', 'email' => 'shanor@vms.com', 'role' => 'Head of Security', 'password' => 'lembahvm476'],
                ['name' => 'Halim Mat Rom',      'email' => 'halim@vms.com',  'role' => 'Security Officer', 'password' => 'lembahvm456'],
                ['name' => 'Amir Idzlan',       'email' => 'amir@vms.com',   'role' => 'Security Officer', 'password' => 'lembahvm789'],
                ['name' => 'Muhammad Firdaus',    'email' => 'firdaus@vms.com', 'role' => 'Security Officer', 'password' => 'lembahvm312'],
                ['name' => 'Guard 2',    'email' => 'guard2@vms.com', 'role' => 'guard',            'password' => 'GuardPass789'],
            ];

            foreach ($users as $user) {
                User::firstOrCreate(
                    ['email' => $user['email']],
                    [
                        'name'     => $user['name'],
                        'role'     => $user['role'],
                        // Hash the specific password assigned in the array above
                        'password' => Hash::make($user['password']),
                    ]
                );
            }
        }
}