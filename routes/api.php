<?php
use App\Http\Controllers\Api\AuthController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\OrientationController;

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/save-specialite', [OrientationController::class, 'saveSpecialite']);
    Route::post('/save-notes', [OrientationController::class, 'saveNotes']);
    Route::post('/login', [AuthController::class, 'login']);

  
   



});


Route::middleware('auth:sanctum')->post('/records', [StudentController::class, 'store']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});






