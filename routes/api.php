<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthenticationController;

Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthenticationController::class, 'register']);
    Route::post('login', [AuthenticationController::class, 'login']);
});
