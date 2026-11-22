<?php

namespace Tests\Feature;

use App\Models\ParkingArea;
use App\Models\ParkingRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ActiveParkingTest extends TestCase
{
    use RefreshDatabase;

    private function makeArea(string $name = 'Gedung A'): ParkingArea
    {
        return ParkingArea::create([
            'name' => $name,
            'capacity_motor' => 5,
            'capacity_mobil' => 5,
        ]);
    }

    private function addRecord(ParkingArea $area, string $plate, string $type, string $checkIn, string $status = 'ACTIVE'): ParkingRecord
    {
        $done = $status === 'DONE';

        return ParkingRecord::create([
            'parking_area_id' => $area->id,
            'plate_number' => $plate,
            'vehicle_type' => $type,
            'check_in_at' => Carbon::parse($checkIn),
            'check_out_at' => $done ? Carbon::parse($checkIn)->addHour() : null,
            'fee' => $done ? 2000 : null,
            'status' => $status,
        ]);
    }

    public function test_lists_only_active_vehicles_oldest_first_with_estimated_fee(): void
    {
        $area = $this->makeArea('Gedung A');
        $this->travelTo(Carbon::parse('2026-11-14 10:00:00'));

        $this->addRecord($area, 'B 2222 BBB', 'mobil', '2026-11-14 09:00:00');
        $this->addRecord($area, 'B 1111 AAA', 'motor', '2026-11-14 07:30:00');
        $this->addRecord($area, 'B 3333 CCC', 'motor', '2026-11-14 06:00:00', 'DONE');

        $this->getJson('/api/parking/active')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.plate_number', 'B 1111 AAA')
            ->assertJsonPath('data.0.area_name', 'Gedung A')
            ->assertJsonPath('data.0.duration_minutes', 150)
            ->assertJsonPath('data.0.estimated_fee', 4000)
            ->assertJsonPath('data.1.plate_number', 'B 2222 BBB')
            ->assertJsonPath('data.1.duration_minutes', 60)
            ->assertJsonPath('data.1.estimated_fee', 5000);
    }

    public function test_returns_empty_list_when_nobody_is_parked(): void
    {
        $this->getJson('/api/parking/active')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_vehicle_leaves_the_list_after_check_out(): void
    {
        $area = $this->makeArea();

        $this->postJson('/api/check-in', [
            'plate_number' => 'B 1111 AAA',
            'vehicle_type' => 'motor',
            'parking_area_id' => $area->id,
        ])->assertCreated();

        $this->getJson('/api/parking/active')->assertJsonCount(1, 'data');

        $this->postJson('/api/check-out', ['plate_number' => 'B 1111 AAA'])->assertOk();

        $this->getJson('/api/parking/active')->assertJsonCount(0, 'data');
    }
}
