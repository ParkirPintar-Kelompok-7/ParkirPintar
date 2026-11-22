<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ParkingArea;

class ParkingAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ParkingArea::updateOrCreate([
            'name' => 'Area A',
        ], [
            'capacity_motor' => 50,
            'capacity_mobil' => 30,
        ]);

        ParkingArea::updateOrCreate([
            'name' => 'Area B',
        ], [
            'capacity_motor' => 40,
            'capacity_mobil' => 20,
        ]);
    }
}
