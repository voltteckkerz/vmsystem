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
        //Employees
        $ahmad = Employee::create(['name' => 'Ahmad', 'status' => 'active']);
        $ali = Employee::create(['name' => 'Ali', 'status' => 'active']);
        $sara = Employee::create(['name' => 'Sara', 'status' => 'active']);
            
        //Vehicles
        $v1 = Vehicle::create(['plate_number' => 'ABC 123', 'owner_type' => 'employee']);
        $v2 = Vehicle::create(['plate_number' => 'XYZ 789', 'owner_type' => 'employee']);
        $v3 = Vehicle::create(['plate_number' => 'LMN 456', 'owner_type' => 'employee']);
            
        //Assign vehicles to employees
        $ahmad->vehicles()->attach([$v1->id, $v2->id]);
        $sara->vehicles()->attach([$v3->id]);


    }
}
