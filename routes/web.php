<?php

use App\Livewire\PublicAssetGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Filament\App\Resources\TicketResource;

Route::get('/', function () {
    return view('welcome');
});

// Routes for forbidden page actions
Route::post('/logout-and-home', [App\Http\Controllers\ForbiddenController::class, 'logoutAndRedirectHome'])->name('logout.and.home');
Route::post('/send-technician-activation', [App\Http\Controllers\ForbiddenController::class, 'sendTechnicianActivation'])->name('send.technician.activation');

// Public routes that require authentication
Route::middleware(['auth'])->group(function () {
    Route::get('/publicAssetsGroups/{classroomId}', PublicAssetGroup::class)->name('publicAssetsGroups');
    Route::prefix('tickets')->name('filament.app.resources.tickets.')->group(function () {
        Route::get('/', [TicketResource::class, 'index'])->name('index');
        Route::get('/create', [TicketResource::class, 'create'])->name('create');
        Route::get('/{record}', [TicketResource::class, 'view'])->name('view');
        Route::get('/{record}/edit', [TicketResource::class, 'edit'])->name('edit');
    });
});

// Redirect users to Filament login page if they're not authenticated
Route::get('/login', function () {
    return redirect()->route('filament.app.auth.login');
})->name('login');
