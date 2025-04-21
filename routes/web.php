<?php

use App\Http\Controllers\Auth\AuthAPIController;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthAPIController::class, 'login']);

