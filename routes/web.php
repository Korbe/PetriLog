<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FishController;
use App\Http\Controllers\LakeController;
use App\Http\Controllers\RiverController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\CatchedController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\ImprintController;
use App\Http\Controllers\BugReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AssociationController;
use App\Http\Controllers\PrivacyPolicyController;
use App\Http\Controllers\TermsOfServiceController;
use App\Http\Controllers\Admin\FishAdminController;
use App\Http\Controllers\Admin\LakeAdminController;
use App\Http\Controllers\Admin\RiverAdminController;
use App\Http\Controllers\Admin\StateAdminController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\Admin\AssociationAdminController;
use App\Http\Controllers\PwaController;

Route::get('/', [PublicController::class, 'index'])->name('public.index');
Route::get('/terms-of-service', [TermsOfServiceController::class, 'show'])->name('terms.show');
Route::get('/privacy-policy', [PrivacyPolicyController::class, 'show'])->name('policy.show');
Route::get('/impressum', [ImprintController::class, 'show'])->name('imprint.show');
Route::get('/contact', [PublicController::class, 'contact'])->name('public.contact');
Route::get('/pricing', [PublicController::class, 'pricing'])->name('public.pricing');

Route::get('/coupon/{coupon}', function ($coupon) {
    return redirect('/?coupon=' . $coupon);
});

Route::get('/catch/{catched}', [PublicController::class, 'showCatched'])->name('public.catched.show');


Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/dashboard')->with('success', 'Deine E-Mail wurde erfolgreich bestätigt!');
})->middleware(['auth', 'signed'])->name('verification.verify');


Route::middleware(['auth:sanctum', config('jetstream.auth_session')])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/pwa', [PwaController::class, 'index'])->name('pwa.index');

    Route::resource('catched', CatchedController::class)->except(['update']);
    Route::post('catched/{catched}/update', [CatchedController::class, 'update'])->name('catched.update');
    Route::delete('catched/photo/{mediaId}', [CatchedController::class, 'deletePhoto'])->name('catched.photo.delete');

    Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');

    Route::prefix('states')->group(function () {

        // Alle Bundesländer / States
        Route::get('/', [StateController::class, 'index'])->name('states.index');

        // Einzelnes Bundesland / State
        Route::get('/{state}', [StateController::class, 'show'])->name('states.show');

        // Einzelner See
        Route::get('{state}/lakes/{lake}', [LakeController::class, 'show'])->name('lakes.show');

        // Einzelner Fluss
        Route::get('{state}/rivers/{river}', [RiverController::class, 'show'])->name('rivers.show');

        // Einzelner Verein
        Route::get('{state}/associations/{association}', [AssociationController::class, 'show'])->name('associations.show');
    });

    Route::get('/fish', [FishController::class, 'index'])->name('fish.index');
    Route::get('/fish/{fish}', [FishController::class, 'show'])->name('fish.show');

    Route::get('/bug-report', [BugReportController::class, 'create'])->name('bug-report.create');
    Route::post('/bug-report', [BugReportController::class, 'store'])->name('bug-report.store');


    Route::post('/admin', [AdminController::class, 'index'])->name('admin.index');
});

Route::middleware(['auth', 'isAdmin'])->prefix('admin')->name('admin.')->group(function () {

    Route::impersonate();

    Route::get('/', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/bugreports', [BugReportController::class, 'index'])->name('bugreports.index');
    Route::get('/bugreports/{bugreport}', [BugReportController::class, 'show'])->name('bugreports.show');
    Route::delete('/bugreports/{report}', [BugReportController::class, 'destroy'])->name('bugreports.destroy');


    Route::resource('lake', LakeAdminController::class)->except(['show']);
    Route::resource('river', RiverAdminController::class)->except(['show']);
    Route::resource('association', AssociationAdminController::class)->except(['show']);


    Route::resource('state', StateAdminController::class)->except(['show', 'update']);
    Route::post('state/{state}/update', [StateAdminController::class, 'update'])->name('state.update');
    Route::delete('state/photo/{mediaId}', [StateAdminController::class, 'deletePhoto'])->name('state.photo.delete');


    Route::resource('fish', FishAdminController::class)->except(['show', 'update']);
    Route::post('fish/{fish}/update', [FishAdminController::class, 'update'])->name('fish.update');
    Route::delete('fish/photo/{mediaId}', [FishAdminController::class, 'deletePhoto'])->name('fish.photo.delete');

    Route::get('user', [UserController::class, 'index'])->name('admin.users.index');
});