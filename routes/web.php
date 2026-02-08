<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PwaController;
use App\Http\Controllers\FishController;
use App\Http\Controllers\LakeController;
use App\Http\Controllers\RiverController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\CatchedController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\ImprintController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WelcomeController;
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
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Admin\NewsletterAdminController;
use App\Http\Controllers\Admin\AssociationAdminController;

// Route::post('/send-mail', [MailController::class, 'sendTestMail'])->name('send.mail');

Route::name('public.')->group(function () {
    Route::get('/', [PublicController::class, 'index'])->name('index');
    Route::get('/terms-of-service', [TermsOfServiceController::class, 'show'])->name('terms');
    Route::get('/privacy-policy', [PrivacyPolicyController::class, 'show'])->name('policy');
    Route::get('/impressum', [ImprintController::class, 'show'])->name('imprint');
    Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
    Route::get('/pricing', [PublicController::class, 'pricing'])->name('pricing');
    Route::get('/catch/{catched}', [PublicController::class, 'showCatched'])->name('catched.show');
});

Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)->middleware(['auth', 'signed'])->name('verification.verify');

Route::middleware(['signed'])->name('public.')->group(function () {
    Route::get('/newsletter/unsubscribe/{user}', [NewsletterAdminController::class, 'unsubscribe'])->name('newsletter.unsubscribe');
});


Route::middleware(['auth'])->name('app.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/app', [DashboardController::class, 'index'])->name('pwa.dashboard'); //PWA entry point

    Route::get('/welcome', [WelcomeController::class, 'index'])->name('welcome');

    Route::get('/pwa', [PwaController::class, 'index'])->name('pwa.index');

    Route::resource('catched', CatchedController::class)
        ->except(['update'])
        ->names([
            'index' => 'catched.index',
            'create' => 'catched.create',
            'store' => 'catched.store',
            'show' => 'catched.show',
            'edit' => 'catched.edit',
            'destroy' => 'catched.destroy',
        ]);
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

    Route::get('/user/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/user/newsletter-preferences', [ProfileController::class, 'updateNewsletterPreference'])->name('profile.newsletter-preferences.update');
    Route::patch('/user/state', [ProfileController::class, 'updateState'])->name('profile.state.update');
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

    Route::get('/newsletter', [NewsletterAdminController::class, 'index'])->name('newsletter.index');
    Route::post('/newsletter/send', [NewsletterAdminController::class, 'send'])->name('newsletter.send');
    Route::post('/newsletter/test', [NewsletterAdminController::class, 'sendTest'])->name('newsletter.test');
});
