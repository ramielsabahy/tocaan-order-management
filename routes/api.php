<?php

use App\Http\Controllers\Api\ConfirmOrderController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthenticationController;

Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthenticationController::class, 'register']);
    Route::post('login', [AuthenticationController::class, 'login']);
});

Route::group(['middleware' => 'auth:sanctum'],function () {
    Route::resource('orders', OrderController::class);
    Route::put('confirm-order/{order}', ConfirmOrderController::class);
    Route::post('payment/{order}', PaymentController::class);
});
