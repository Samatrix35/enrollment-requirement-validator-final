<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MyEnrollmentController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\RequirementController;

Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware(['https.production', 'throttle:5,1']);

Route::middleware(['https.production', 'auth:sanctum'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::middleware('role:student')->group(function () {
        Route::get('/my/enrollment', [MyEnrollmentController::class, 'show']);
        Route::patch('/my/requirements/{requirement}/submit', [MyEnrollmentController::class, 'submit']);
    });

    Route::middleware('role:enrollment_staff')->group(function () {
        Route::apiResource('students', StudentController::class);
        Route::apiResource('requirements', RequirementController::class);
    });
});
