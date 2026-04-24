<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pass;

class PassSeeder extends Seeder
{
    public function run(): void
    {
        // Create 20 passes (P0-01 to P0-20)
        for ($i = 1; $i <= 20; $i++) {
            $number = str_pad($i, 2, '0', STR_PAD_LEFT);
            Pass::firstOrCreate(['pass_number' => "P0-$number"]);
        }
    }
}
