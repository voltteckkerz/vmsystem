<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Vehicle;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Employees with their vehicles
        $data = [
            'Ahmad'  => ['ABC 123', 'XYZ 789'],
            'Ali'    => [],
            'Sara'   => ['LMN 456'],
            'Zainab' => ['PQR 012'],
            'Omar'   => ['DEF 345', 'GHI 678'],
        ];

        foreach ($data as $name => $plates) {
            $employee = Employee::create(['name' => $name, 'status' => 'active']);

            foreach ($plates as $plate) {
                $vehicle = Vehicle::create(['plate_number' => $plate, 'owner_type' => 'employee']);
                $employee->vehicles()->attach($vehicle->id);
            }
        }



    }
}
