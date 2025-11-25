<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::middleware(['clerk'])->group(function () {
//     Route::get('/user', function (Request $request) {
//         return $request->user();
//     });

//     Route::apiResource('events', EventController::class);
// });

Route::middleware('clerk')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user(); // User sincronizado com Clerk
    });

    Route::apiResource('events', EventController::class);
});

Route::get('/', function () {
    return 'ola muundo API';
});
