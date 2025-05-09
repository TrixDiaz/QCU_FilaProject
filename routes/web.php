<?php

use App\Livewire\PublicAssetGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Filament\App\Resources\TicketResource;
use App\Http\Controllers\AssetController;

Route::get('/example-csv', [AssetController::class, 'downloadExampleCsv'])->name('example-csv');


Route::get('/', function () {
    return view('welcome');
});

Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
// Routes for forbidden page actions
Route::post('/logout-and-home', [App\Http\Controllers\ForbiddenController::class, 'logoutAndRedirectHome'])->name('logout.and.home');
Route::post('/send-technician-activation', [App\Http\Controllers\ForbiddenController::class, 'sendTechnicianActivation'])->name('send.technician.activation');

// Public routes that require authentication
Route::middleware(['auth'])->group(function () {
    Route::get('/publicAssetsGroups/{classroomId}', PublicAssetGroup::class)->name('publicAssetsGroups');

    // Fix: Use the correct namespace and redirect to Filament pages
    Route::prefix('tickets')->group(function () {
        Route::get('/', function () {
            return redirect()->route('filament.app.resources.tickets.index');
        });
        Route::get('/create', function () {
            return redirect()->route('filament.app.resources.tickets.create');
        });
        Route::get('/{record}', function ($record) {
            return redirect()->route('filament.app.resources.tickets.view', ['record' => $record]);
        });
        Route::get('/{record}/edit', function ($record) {
            return redirect()->route('filament.app.resources.tickets.edit', ['record' => $record]);
        });
    });
});

// Redirect users to Filament login page if they're not authenticated
Route::get('/login', function () {
    return redirect()->route('filament.app.auth.login');
})->name('login');
