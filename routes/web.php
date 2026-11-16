
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\FoundItemController;
use App\Http\Controllers\ReturnController;
use Illuminate\Support\Facades\Route;

// Halaman utama
Route::get('/', function () {
    return view('welcome');
});

// Guest authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'store']);
});

// Fitur pengembalian barang (Admin dan Security)
Route::middleware(['auth', 'role:admin,security'])->group(function () {
    Route::get('/returns/create', [ReturnController::class, 'create'])
        ->name('returns.create');

    Route::post('/returns', [ReturnController::class, 'store'])
        ->name('returns.store');

    Route::get('/returns', [ReturnController::class, 'index'])
        ->name('returns.index');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])
        ->name('logout');

    // Katalog barang
    Route::get('/catalog', [CatalogController::class, 'index'])
        ->name('catalog.index');

    // Form input barang temuan
    Route::get('/found-items/create', [FoundItemController::class, 'create'])
        ->name('found-items.create');

    // Simpan barang temuan sebagai Draft
    Route::post('/found-items', [FoundItemController::class, 'store'])
        ->name('found-items.store');

   // Pengelolaan status barang (Admin saja)
    Route::middleware('role:admin')->group(function () {
        Route::post('/found-items/{foundItem}/publish', [FoundItemController::class, 'publish'])
        ->name('found-items.publish');

        Route::post('/found-items/{foundItem}/archive', [FoundItemController::class, 'archive'])
        ->name('found-items.archive');
    });
});
