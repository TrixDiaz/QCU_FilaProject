<?php

use App\Livewire\PublicAssetGroup;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/publicAssetsGroups/{classroomId}', PublicAssetGroup::class)->name('publicAssetsGroups');
