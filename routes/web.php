<?php

use App\Livewire\PublicAssetGroup;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


// Public routes that require authentication
Route::middleware(['auth'])->group(function () {
    Route::get('/publicAssetsGroups/{classroomId}', PublicAssetGroup::class)->name('publicAssetsGroups');
});

// Redirect users to Filament login page if they're not authenticated
Route::get('/login', function () {
    return redirect()->route('filament.app.auth.login');
})->name('login');
