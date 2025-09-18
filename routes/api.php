<?php

use App\Http\Controllers\Auth\AuthAPIController;
use App\Http\Controllers\Client\ClientAPIController;
use App\Http\Controllers\Client\InactiveClientAPIController;
use App\Http\Controllers\Coordinator\CoordinatorAPIController;
use App\Http\Controllers\Coordinator\InactiveCoordinatorAPIController;
use App\Http\Controllers\Device\DeviceAPIController;
use App\Http\Controllers\DeviceLink\DeviceLinkAPIController;
use App\Http\Controllers\Maintenance\MaintenanceAPIController;
use App\Http\Controllers\Other\ResourceAPIController;
use App\Http\Controllers\Technician\AvailableTechnicianController;
use App\Http\Controllers\Technician\InactiveTechnicianAPIController;
use App\Http\Controllers\Technician\TechnicianAPIController;
use Illuminate\Support\Facades\Route;

Route::group([], function () {
// Auth
Route::post('/acceptPolicy', AuthAPIController::class . '@acceptPolicy');
Route::get('/me', AuthAPIController::class . '@me');
// Clients
Route::apiResource('/clients', ClientAPIController::class);
    Route::put('/clients/{client}/status', InactiveClientAPIController::class);
// LinkDevices
Route::apiResource('/linkDevices', DeviceLinkAPIController::class);
// Devices
Route::apiResource('/devices', DeviceAPIController::class);
// Maintenance
Route::apiResource('/maintenances', MaintenanceAPIController::class);
// Other
Route::post('loadImage', ResourceAPIController::class . '@loadImage');
Route::post('loadDoc', ResourceAPIController::class . '@loadDoc');
// Technician
Route::get('/availableDates', AvailableTechnicianController::class);
Route::apiResource('/technical', TechnicianAPIController::class);
Route::put('/technical/{technical}/status', InactiveTechnicianAPIController::class);
//Coordinators
Route::post('/coordinators', CoordinatorAPIController::class . '@store');
Route::get('/coordinators', CoordinatorAPIController::class . '@index');
Route::put('/coordinators/{coordinator}', CoordinatorAPIController::class . '@update');
Route::get('/coordinators/{coordinator}', CoordinatorAPIController::class . '@show');
Route::delete('/coordinators/{coordinator}', CoordinatorAPIController::class . '@destroy');
Route::put('/coordinator/{coordinator}/status', InactiveCoordinatorAPIController::class);
});


