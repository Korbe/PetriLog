<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FishController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\WatersController;
use App\Http\Controllers\CatchedController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\DashboardController;

Route::get('/', [PublicController::class, 'index'])->name('public.index');
Route::get('/pricing', [PublicController::class, 'pricing'])->name('public.pricing');
Route::get('/contact', [PublicController::class, 'contact'])->name('public.contact');

Route::get('/catch/{catch}', [PublicController::class, 'showCatched'])->name('public.catched.show');


Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::resource('catched', CatchedController::class)->except(['update']);
    Route::post('catched/{catched}/update', [CatchedController::class, 'update'])->name('catched.update');
    Route::delete('catched/photo/{mediaId}', [CatchedController::class, 'deletePhoto'])->middleware(['auth', 'verified'])->name('catched.photo.delete');

    Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
    Route::get('/waters', [WatersController::class, 'index'])->name('waters.index');
    Route::get('/waters/{state}', [WatersController::class, 'state'])->name('waters.state');
    Route::get('/waters/{state}/{lake}', [WatersController::class, 'waters'])->name('waters.state.waters');


    Route::get('/fish', [FishController::class, 'index'])->name('fish.index');
    Route::get('/fish/{fish}', [FishController::class, 'fish'])->name('fish.fish');

});
