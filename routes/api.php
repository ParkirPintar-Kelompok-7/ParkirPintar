<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParkingController;
use App\Http\Controllers\ParkingAreaController;
use App\Http\Controllers\ReportController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/check-in', [ParkingController::class, 'checkIn']);
Route::post('/check-out', [ParkingController::class, 'checkOut']);
Route::apiResource('areas', ParkingAreaController::class);
Route::get('/reports/occupancy', [ReportController::class, 'occupancy']);
Route::get('/reports/revenue', [ReportController::class, 'revenue']);
Route::get('/parking/active', [ParkingController::class, 'active']);
