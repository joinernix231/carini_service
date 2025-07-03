<?php

use App\Http\Controllers\Auth\AuthAPIController;
use App\Http\Controllers\Client\ClientAPIController;
use App\Http\Controllers\Device\DeviceAPIController;
use App\Http\Controllers\DeviceLink\DeviceLinkAPIController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group([], function () {
Route::apiResource('/clients', ClientAPIController::class);
Route::apiResource('/linkDevices', DeviceLinkAPIController::class);
    Route::apiResource('/devices', DeviceAPIController::class);

});


