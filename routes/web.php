<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FishController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\WatersController;
use App\Http\Controllers\CatchedController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\ImprintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PrivacyPolicyController;
use App\Http\Controllers\TermsOfServiceController;
use Spark\Http\Controllers\BillingPortalController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

Route::get('/', [PublicController::class, 'index'])->name('public.index');
Route::get('/terms-of-service', [TermsOfServiceController::class, 'show'])->name('terms.show');
Route::get('/privacy-policy', [PrivacyPolicyController::class, 'show'])->name('policy.show');
Route::get('/impressum', [ImprintController::class, 'show'])->name('imprint.show');
Route::get('/pricing', [PublicController::class, 'pricing'])->name('public.pricing');
Route::get('/contact', [PublicController::class, 'contact'])->name('public.contact');

Route::get('/coupon/{coupon}', function ($coupon) { return redirect('/?coupon=' . $coupon); });

Route::get('/catch/{catch}', [PublicController::class, 'showCatched'])->name('public.catched.show');


Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/dashboard')->with('success', 'Deine E-Mail wurde erfolgreich bestätigt!');
})->middleware(['auth', 'signed'])->name('verification.verify');


Route::middleware(['auth:sanctum', config('jetstream.auth_session')])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('catched', CatchedController::class)->except(['update']);
    Route::post('catched/{catched}/update', [CatchedController::class, 'update'])->name('catched.update');
    Route::delete('catched/photo/{mediaId}', [CatchedController::class, 'deletePhoto'])->name('catched.photo.delete');

    Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
    Route::get('/waters', [WatersController::class, 'index'])->name('waters.index');
    Route::get('/waters/{state}', [WatersController::class, 'state'])->name('waters.state');
    Route::get('/waters/{state}/{lake}', [WatersController::class, 'waters'])->name('waters.state.waters');


    Route::get('/fish', [FishController::class, 'index'])->name('fish.index');
    Route::get('/fish/{fish}', [FishController::class, 'fish'])->name('fish.fish');
});
