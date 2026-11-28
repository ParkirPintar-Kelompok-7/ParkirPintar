<?php

namespace Tests\Feature;

use App\Models\ParkingArea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class QABusinessFlowTest extends TestCase
{
    use RefreshDatabase;

    private function createArea(string $name): ParkingArea
    {
        return ParkingArea::create([
            'name' => $name,
            'capacity_motor' => 1,
            'capacity_mobil' => 1,
        ]);
    }

    public function test_active_plate_cannot_enter_another_area(): void
    {
        $areaA = $this->createArea('Area A');
        $areaB = $this->createArea('Area B');

        $this->postJson('/api/check-in', [
            'plate_number' => 'B 1234 QA',
            'vehicle_type' => 'motor',
            'parking_area_id' => $areaA->id,
        ])->assertCreated();

        $this->postJson('/api/check-in', [
            'plate_number' => 'B 1234 QA',
            'vehicle_type' => 'motor',
            'parking_area_id' => $areaB->id,
        ])->assertStatus(409);

        $this->assertDatabaseCount('parking_records', 1);

        $this->postJson('/api/check-out', [
            'plate_number' => 'B 1234 QA',
        ])->assertOk();

        $this->postJson('/api/check-in', [
            'plate_number' => 'B 1234 QA',
            'vehicle_type' => 'motor',
            'parking_area_id' => $areaB->id,
        ])->assertCreated();

        $this->assertDatabaseCount('parking_records', 2);
    }

    public function test_motor_exactly_fifteen_minutes_is_free(): void
    {
        $area = $this->createArea('Area Gratis');

        $this->travelTo(Carbon::parse('2026-11-14 08:00:00'));

        $this->postJson('/api/check-in', [
            'plate_number' => 'B 1111 QA',
            'vehicle_type' => 'motor',
            'parking_area_id' => $area->id,
        ])->assertCreated();

        $this->travelTo(Carbon::parse('2026-11-14 08:15:00'));

        $this->postJson('/api/check-out', [
            'plate_number' => 'B 1111 QA',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'DONE')
            ->assertJsonPath('data.fee', 0);
    }

    public function test_motor_after_fifteen_minutes_is_charged(): void
    {
        $area = $this->createArea('Area Berbayar');

        $this->travelTo(Carbon::parse('2026-11-14 08:00:00'));

        $this->postJson('/api/check-in', [
            'plate_number' => 'B 2222 QA',
            'vehicle_type' => 'motor',
            'parking_area_id' => $area->id,
        ])->assertCreated();

        $this->travelTo(Carbon::parse('2026-11-14 08:15:01'));

        $this->postJson('/api/check-out', [
            'plate_number' => 'B 2222 QA',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'DONE')
            ->assertJsonPath('data.fee', 2000);
    }
}
