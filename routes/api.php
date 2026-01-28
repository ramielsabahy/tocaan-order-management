<?php

use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthenticationController;

Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthenticationController::class, 'register']);
    Route::post('login', [AuthenticationController::class, 'login']);
});

Route::group(['middleware' => 'auth:sanctum'],function () {
    Route::post('orders', [OrderController::class, 'store']);
});
