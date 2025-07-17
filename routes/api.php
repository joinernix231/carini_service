<?php

use App\Http\Controllers\Client\ClientAPIController;
use App\Http\Controllers\Device\DeviceAPIController;
use App\Http\Controllers\DeviceLink\DeviceLinkAPIController;
use App\Http\Controllers\Maintenance\MaintenanceAPIController;
use App\Http\Controllers\Maintenance\MaintenanceTypeAPIController;
use App\Http\Controllers\Other\ResourceAPIController;
use Illuminate\Support\Facades\Route;

Route::group([], function () {
// Clients
Route::apiResource('/clients', ClientAPIController::class);
// LinkDevices
Route::apiResource('/linkDevices', DeviceLinkAPIController::class);
// Devices
Route::apiResource('/devices', DeviceAPIController::class);
// Maintenance
Route::apiResource('/maintenances', MaintenanceAPIController::class);
Route::apiResource('/maintenanceTypes', MaintenanceTypeAPIController::class);
// Other
Route::post('loadImage', ResourceAPIController::class . '@loadImage');
Route::post('loadDoc', ResourceAPIController::class . '@loadDoc');
});


