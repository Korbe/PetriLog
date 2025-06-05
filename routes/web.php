<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatchedController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\DashboardController;

Route::redirect('/', '/dashboard');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('catched', CatchedController::class)->except(['update']);
    Route::post('catched/{catched}/update', [CatchedController::class, 'update'])->name('catched.update');
    Route::delete('catched/photo/{mediaId}', [CatchedController::class, 'deletePhoto'])
    ->middleware(['auth', 'verified'])
    ->name('catched.photo.delete');

    Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');

});
