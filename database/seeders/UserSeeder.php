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
                ['name' => 'Admin',      'email' => 'admin@vms.com',  'role' => 'admin',            'password' => 'AdminPass123'],
                ['name' => 'En. Shanor', 'email' => 'shanor@vms.com', 'role' => 'Head of Security', 'password' => 'lembahvm476'],
                ['name' => 'Guard 2',    'email' => 'guard2@vms.com', 'role' => 'guard',            'password' => 'GuardPass789!'],
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