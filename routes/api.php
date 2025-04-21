<?php

use App\Http\Controllers\Auth\AuthAPIController;
use App\Http\Controllers\Client\ClientAPIController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group([], function () {
Route::apiResource('/clients', ClientAPIController::class);

});


