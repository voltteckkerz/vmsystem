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
            'Abdul Shamir' => ['VAV 3189'],
            'Muzeer' => ['VFB 203'],
            'Farah' => ['WQQ 8986'],
            'Yong Hwa Ming' => ['VGD 9575'],
            'Azrul Mohd Johan' => ['RAE 8275'],
            'Nadia Masyitah' => ['WA 3016 Q'],
            'Muhazlan' => ['KEM 229'],
            'Muhammad Rohman' => ['VET 142'],
            'Amirul' => ['BQC 9642'],
            'Muhd Shafuan' => ['BMC 499'],
            'Wan Hanapi' => ['WA 123 N'],
            'Anis Syafiqah' => ['ANY 5485'],
            'Ahmad Azamuddin' => [''],
            'Noor Nadia Farahaana' => ['VGA 4370'],
            'Shanorputra' => ['VM 9260'],
            'Halim bin Mat Rom' => ['BPL 5681'],
            'Amir Idzlan' => ['ANV 3077'],
            'Muhammad Firdaus' => ['WLH 6686'],
            'Deleilah' => ['WXY 9260'],
            'Ashril' => ['RAD 3951'],
            'Wan Muhammad' => ['PNC 8431'],
            'Abdul Hanan' => ['JQB 1724'],
            'Nurul Balqis' => ['VMS 3649'],
            'Ahmad Afif' => ['BQW 5659'],
            'Ahmad Zaki Badrul' => ['BPM 8404'],
            'Syarul Laili' => ['WSQ 4210'],
            'Amirudin' => ['CEK 2363'],
            'Afiza' => ['VL 432'],
            'Anis Rina' => ['RR 4377'],
            'Badrul' => ['WWN 1985'],
            'Ismi' => [''],
            'Syazwani' => ['VJA 3548'],
            'Mohd Amir' => [''],
            'Hariati' => [''],

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
