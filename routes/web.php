<?php

use App\Http\Controllers\FoundItemController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/found-items/create', [FoundItemController::class, 'create'])
    ->name('found-items.create');

Route::post('/found-items', [FoundItemController::class, 'store'])
    ->name('found-items.store');
