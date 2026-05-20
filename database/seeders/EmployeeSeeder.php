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
            'ABDUL HANAN' => ['JQB 1724'],
            'ABDUL SHAMIR' => ['VAV 3189'],
            'AFIZA ARBAK' => ['VL 432'],
            'AHMAD AFIF' => ['BOW 5659'],
            'AHMAD AZAMUDIAN' => ['PQG 9405'],
            'AHMAD ZAKI BADRUL' => ['BPM 8404'],
            'AISYAH JAAFAR' => ['WUX 5380'],
            'AMIR IDZLAN' => ['ANV 3077'],
            'AMIRUDIN' => ['CEK 2363'],
            'AMIRUL' => ['BRC 2094'],
            'ANIS RINA' => ['RR 4377'],
            'ANIS SYAFIQAH' => ['ANY 5485'],
            'ASHRIL' => ['RAD 3951'],
            'AUDREY' => [],
            'AZRUL MOHD JOHAN' => ['RAE 8275'],
            'BADRUL' => ['WWN 1985'],
            'DELEILAH BINTI MOHAMAD AZLAN' => ['WXY 7587'],
            'FARAH ROSLI' => ['WQQ 8986'],
            'FIRDAUS' => ['WLH 6686'],
            'GAVIN' => [],
            'HALIM MAT ROM' => ['BPL 5681'],
            'HARIATI' => [],
            'ISMI' => [],
            'MOHD AMIR HAMIRUL BIN MOHD AZIZ' => ['WB 8237 J'],
            'MOHD SHAFUAN BIN HASHIM' => ['BMC 499'],
            'MUHAMMAD ROHMAN' => ['VET 142'],
            'MUHAZLAN' => ['KEM 229'],
            'MUZEER' => ['VFB 203'],
            'NADIA MASYITAH' => ['WA 306 Q'],
            'NOOR NADIA FARAHANNA BT CHE MAN' => ['VGA 4370'],
            'NURUL BALQIS' => ['VMF 3649'],
            'ONG LEAN IM' => [],
            'SHANORPUTRA SHAFEI' => ['VH 2960'],
            'SITI' => [],
            'SITI FARADILLA' => [],
            'SITI KHALIJAH' => [],
            'SYAHRUL ILAILI' => ['YSQ 4210', 'MDP 7553'],
            'SYAZWANI' => ['VJA 3548'],
            'WAN HANAPI WAN MUSTAPA' => ['WA 123 N'],
            'WAN MUHAMMAD' => ['PNC 8431'],
            'YONG HWA MING' => ['VGD 9575'],
        ];

        foreach ($data as $name => $plates) {
            $employee = Employee::firstOrCreate(
                ['name' => $name],
                ['status' => 'active']
            );

            foreach ($plates as $plate) {
                $vehicle = Vehicle::firstOrCreate(
                    ['plate_number' => $plate],
                    ['owner_type' => 'employee']
                );

                if (!$employee->vehicles()->where('vehicle_id', $vehicle->id)->exists()) {
                    $employee->vehicles()->attach($vehicle->id);
                }
            }
        }



    }
}
