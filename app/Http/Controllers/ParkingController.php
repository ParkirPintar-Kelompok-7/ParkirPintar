<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckOutRequest;
use App\Services\ParkingFeeCalculator;
use App\Http\Requests\CheckInRequest;
use App\Models\ParkingArea;
use App\Models\ParkingRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ParkingController extends Controller
{
    public function checkIn(CheckInRequest $request): JsonResponse
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            // Kunci baris area supaya dua check-in bersamaan tidak melewati kapasitas
            $area = ParkingArea::whereKey($data['parking_area_id'])->lockForUpdate()->firstOrFail();

            $alreadyInside = ParkingRecord::where('plate_number', $data['plate_number'])
                ->where('status', 'ACTIVE')
                ->exists();

            if ($alreadyInside) {
                return response()->json([
                    'message' => "Plat {$data['plate_number']} masih berada di dalam area parkir.",
                ], 409);
            }

            $capacity = $area->{'capacity_' . $data['vehicle_type']};
            $occupied = ParkingRecord::where('parking_area_id', $area->id)
                ->where('vehicle_type', $data['vehicle_type'])
                ->where('status', 'ACTIVE')
                ->count();

            if ($occupied >= $capacity) {
                return response()->json([
                    'message' => "Area {$area->name} penuh untuk {$data['vehicle_type']}.",
                ], 409);
            }

            $record = ParkingRecord::create([
                'parking_area_id' => $area->id,
                'plate_number' => $data['plate_number'],
                'vehicle_type' => $data['vehicle_type'],
                'check_in_at' => now(),
                'status' => 'ACTIVE',
            ]);

            return response()->json([
                'message' => 'Check-in berhasil.',
                'data' => $record,
            ], 201);
        });
    }

    public function checkOut(CheckOutRequest $request, ParkingFeeCalculator $calculator): JsonResponse
    {
        $data = $request->validated();

         return DB::transaction(function () use ($data, $calculator) {
            // Kunci record supaya dua check-out bersamaan tidak diproses ganda
            $record = ParkingRecord::where('plate_number', $data['plate_number'])
                ->where('status', 'ACTIVE')
                ->orderByDesc('check_in_at')
                ->lockForUpdate()
                ->first();

            if (! $record) {
                return response()->json([
                    'message' => "Tidak ada parkir aktif untuk plat {$data['plate_number']} (belum check-in atau sudah check-out).",
                ], 404);
            }

            $checkOutAt = now();
            $fee = $calculator->calculate($record->vehicle_type, $record->check_in_at, $checkOutAt);

            $record->update([
                'check_out_at' => $checkOutAt,
                'fee' => $fee,
                'status' => 'DONE',
            ]);

            return response()->json([
                'message' => 'Check-out berhasil.',
                'data' => $record,
            ]);
        });
    }

        public function active(ParkingFeeCalculator $calculator): JsonResponse
    {
        $now = now();

        $vehicles = ParkingRecord::with('area')
            ->where('status', 'ACTIVE')
            ->orderBy('check_in_at')
            ->get()
            ->map(function ($record) use ($calculator, $now) {
                // Jaga-jaga jika jam sistem pernah diubah mundur setelah check-in
                $until = $now->lessThan($record->check_in_at) ? $record->check_in_at : $now;

                return [
                    'id' => $record->id,
                    'plate_number' => $record->plate_number,
                    'vehicle_type' => $record->vehicle_type,
                    'parking_area_id' => $record->parking_area_id,
                    'area_name' => $record->area->name,
                    'check_in_at' => $record->check_in_at->toIso8601String(),
                    'duration_minutes' => intdiv($until->getTimestamp() - $record->check_in_at->getTimestamp(), 60),
                    'estimated_fee' => $calculator->calculate($record->vehicle_type, $record->check_in_at, $until),
                ];
            })
            ->values();

        return response()->json(['data' => $vehicles]);
    }
}
