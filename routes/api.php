<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/hello', function () {
    return 'Hello';
});

Route::get('/sections', [\App\Http\Controllers\SectionController::class, 'index']);

Route::get('/sections/{id}', function ($id) {
    return \App\Models\Section::with('classroom.building')->findOrFail($id);
});
