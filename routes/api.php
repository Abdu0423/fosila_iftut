<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API для получения предметов (используется в формах создания уроков/расписания)
Route::middleware(['auth', 'staff'])->group(function () {
    Route::get('/subjects', [App\Http\Controllers\Admin\SubjectController::class, 'apiIndex'])->name('api.subjects.index');
});
