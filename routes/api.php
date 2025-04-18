<?php

use App\Http\Controllers\Auth\AuthAPIController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group([], function () {
    Route::post('/login', [AuthAPIController::class, 'login']);


    Route::middleware('auth:api')->get('/user', function (Request $request) {
        return $request->user();
    });

});
