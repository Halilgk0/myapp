<?php

use App\Http\Controllers\Api\VehicleLocationController;
use App\Http\Controllers\Api\TicketLocationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Vehicle Location API Routes
Route::prefix('vehicles')->group(function () {
    Route::get('/locations', [VehicleLocationController::class, 'index']);
    Route::post('/{vehicle}/location', [VehicleLocationController::class, 'updateLocation']);
    Route::get('/{vehicle}/locations', [VehicleLocationController::class, 'getLocations']);
});

// Driver Location Update API
Route::middleware('auth:sanctum')->prefix('drivers')->group(function () {
    Route::post('/location/update', [\App\Http\Controllers\Api\DriverLocationController::class, 'updateLocation']);
});

// Ticket Location API Routes
Route::prefix('tickets')->group(function () {
    Route::post('/location/update', [TicketLocationController::class, 'updateLocation']);
    Route::get('/locations', [TicketLocationController::class, 'getCustomerLocations']);
});
