<?php

use App\Http\Controllers\ProductsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('test-wayfinder', function () {
//    return Inertia::render('welcome', [
//        'canRegister' => Features::enabled(Features::registration()),
//    ]);
})->name('test-wayfinder');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::get('products', [ProductsController::class, 'index'])->name('products.index');
    Route::get('products/{id}', [ProductsController::class, 'show'])->whereNumber('id')->name('products.show');
});

require __DIR__.'/settings.php';
