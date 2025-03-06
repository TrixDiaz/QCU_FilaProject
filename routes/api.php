<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/subjects', [\App\Http\Controllers\SectionController::class, 'showSubjects']);

Route::get('/subject/{id}', [\App\Http\Controllers\SectionController::class, 'showClassroomBuildingById']);

Route::post('/store/attendance', [\App\Http\Controllers\SectionController::class, 'storeAttendance']);
