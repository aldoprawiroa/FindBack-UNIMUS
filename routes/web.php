<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FoundItemController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Guest authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    // Protected catalog route (temporary verification route for Paket A2)
    Route::get('/catalog', function () {
        return response('Katalog Barang Temuan UNIMUS (Terlindungi)');
    })->name('catalog.index');

    Route::get('/found-items/create', [FoundItemController::class, 'create'])
        ->name('found-items.create');

    Route::post('/found-items', [FoundItemController::class, 'store'])
        ->name('found-items.store');
});
