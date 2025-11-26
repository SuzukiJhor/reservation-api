<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('clerk')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/auth/check', function (Request $request) {
        $user = $request->user();
        if (!$user || !$user->exists) {
            return response()->json([
                'first_time' => true,
            ]);
        }
        return response()->json([
            'first_time' => false,
            'user' => $user,
        ]);
    });

    Route::post('/register-profile', [AuthController::class, 'registerProfile']);
    Route::get('/companies', [CompanyController::class, 'index']);
    Route::apiResource('events', EventController::class);
});
