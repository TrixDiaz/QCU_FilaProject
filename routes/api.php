<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/sections', [\App\Http\Controllers\SectionController::class, 'showSections']);

Route::get('/sections/{id}', [\App\Http\Controllers\SectionController::class, 'showClassroomBuildingById']);

Route::post('/store/attendance', [\App\Http\Controllers\SectionController::class, 'storeAttendance']);

