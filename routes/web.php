<?php

use Spark\Spark;
use Illuminate\Support\Facades\Route;
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
use App\Http\Controllers\TicketController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AssociationController;
use App\Http\Controllers\PrivacyPolicyController;
use App\Http\Middleware\EnsureVerifiedForBilling;
use App\Http\Controllers\TermsOfServiceController;
use App\Http\Controllers\Admin\FishAdminController;
use App\Http\Controllers\Admin\LakeAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\RiverAdminController;
use App\Http\Controllers\Admin\StateAdminController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Admin\TicketAdminController;
use App\Http\Controllers\Admin\NewsletterAdminController;
use App\Http\Controllers\Admin\AssociationAdminController;
use App\Http\Controllers\ManifestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SpotController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\Admin\SubmissionAdminController;
use App\Http\Controllers\Admin\WikiExportAdminController;
use App\Http\Controllers\Admin\DevelopmentController;
use App\Http\Controllers\Admin\BackupAdminController;

// Route::post('/send-mail', [MailController::class, 'sendTestMail'])->name('send.mail');

Route::name('public.')->group(function () {
    Route::get('/', [PublicController::class, 'index'])->name('index');
    Route::get('/manifest.json', [ManifestController::class, 'index'])->name('manifest');
    
    Route::get('/terms-of-service', [TermsOfServiceController::class, 'show'])->name('terms');
    Route::get('/privacy-policy', [PrivacyPolicyController::class, 'show'])->name('policy');
    Route::get('/impressum', [ImprintController::class, 'show'])->name('imprint');
    Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
    Route::get('/pricing', [PublicController::class, 'pricing'])->name('pricing');
    // Nur geteilte Fänge, über einen nicht erratbaren Token (nie über die ID)
    Route::get('/catch/{token}', [PublicController::class, 'showCatched'])
        ->where('token', '[A-Za-z0-9]{32}')
        ->name('catched.show');
});

Route::post('/demo', \App\Http\Controllers\Auth\DemoLoginController::class)
    ->middleware(['guest', 'throttle:10,1'])->name('demo.login');

Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)->middleware(['auth', 'signed'])->name('verification.verify');

Route::middleware(['signed'])->name('public.')->group(function () {
    Route::get('/newsletter/unsubscribe/{user}', [NewsletterAdminController::class, 'unsubscribe'])->name('newsletter.unsubscribe');
});


Route::middleware(['auth'])->name('app.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/app', [DashboardController::class, 'index'])->name('pwa.dashboard'); //PWA entry point

    Route::get('/welcome', [WelcomeController::class, 'index'])->name('welcome');


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
    Route::post('catched/{catched}/share', [CatchedController::class, 'share'])->name('catched.share');
    Route::delete('catched/{catched}/share', [CatchedController::class, 'unshare'])->name('catched.unshare');
    Route::delete('catched/photo/{mediaId}', [CatchedController::class, 'deletePhoto'])->name('catched.photo.delete');

    Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');

    Route::post('/corrections/{type}/{id}', [\App\Http\Controllers\CorrectionController::class, 'store'])
        ->whereIn('type', ['fish', 'lake', 'river'])->whereNumber('id')
        ->middleware('throttle:10,60')->name('corrections.store');

    Route::get('/geocode/reverse', [\App\Http\Controllers\GeocodeController::class, 'reverse'])
        ->middleware('throttle:30,1')->name('geocode.reverse');

    Route::get('/spots', [SpotController::class, 'index'])->name('spots.index');
    Route::post('/spots', [SpotController::class, 'store'])->name('spots.store');
    Route::put('/spots/{spot}', [SpotController::class, 'update'])->name('spots.update');
    Route::delete('/spots/{spot}', [SpotController::class, 'destroy'])->name('spots.destroy');

    Route::get('/report', [ReportController::class, 'index'])->name('report.index');
    Route::get('/report/export', [ReportController::class, 'export'])->name('report.export');
    Route::get('/report/print', [ReportController::class, 'print'])->name('report.print');
    Route::get('/report/pdf', [ReportController::class, 'pdf'])->middleware('throttle:20,1')->name('report.pdf');
    Route::patch('/report/catches/{catched}', [ReportController::class, 'updateRelease'])->name('report.release');

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

    Route::post('/submissions/{type}', [SubmissionController::class, 'store'])
        ->whereIn('type', ['fish', 'lake', 'river'])
        ->middleware('throttle:20,60')
        ->name('submissions.store');

    Route::get('/ticket', [TicketController::class, 'create'])->name('ticket.create');
    Route::post('/ticket', [TicketController::class, 'store'])->name('ticket.store');

    Route::get('/user/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/user/newsletter-preferences', [ProfileController::class, 'updateNewsletterPreference'])->name('profile.newsletter-preferences.update');
    Route::patch('/user/state', [ProfileController::class, 'updateState'])->name('profile.state.update');
});

Route::middleware(['auth', EnsureVerifiedForBilling::class])->get('/billing-portal', function () { return redirect('/billing'); })->name('billing.portal');

// Admin-Bereich: Admins und Editoren. Was nur Admins dürfen, ist zusätzlich mit 'isAdmin' geschützt.
Route::middleware(['auth', 'isAdmin:staff'])->prefix('admin')->name('admin.')->group(function () {

    Route::impersonate();

    Route::get('/', [AdminController::class, 'index'])->name('index');
    // Development: Entwicklungs-, Wartungs- und Administrationswerkzeuge (nur Admins)
    Route::prefix('development')->name('development.')->middleware('isAdmin')->group(function () {
        Route::get('/', [DevelopmentController::class, 'index'])->name('index');

        Route::get('/wiki-export', [WikiExportAdminController::class, 'index'])->name('wiki-export.index');
        Route::get('/wiki-export/zip', [WikiExportAdminController::class, 'zip'])->name('wiki-export.zip');
        Route::get('/wiki-export/{name}.json', [WikiExportAdminController::class, 'dataset'])
            ->whereIn('name', \App\Services\WikiExporter::DATASETS)
            ->name('wiki-export.dataset');

        // Backups enthalten personenbezogene Daten: der ganze Bereich nur nach erneuter Passwort-Eingabe
        Route::middleware('password.confirm')->group(function () {
            Route::get('/backups', [BackupAdminController::class, 'index'])->name('backups.index');
            Route::post('/backups', [BackupAdminController::class, 'store'])->middleware('throttle:6,60')->name('backups.store');
            Route::post('/backups/upload', [BackupAdminController::class, 'upload'])->middleware('throttle:2000,60')->name('backups.upload');
            Route::post('/backups/{name}/verify', [BackupAdminController::class, 'verify'])->name('backups.verify');
            Route::post('/backups/{name}/restore-test', [BackupAdminController::class, 'restoreTest'])->middleware('throttle:6,60')->name('backups.restore-test');
            Route::post('/backups/{name}/restore-production', [BackupAdminController::class, 'restoreProduction'])->middleware('throttle:3,60')->name('backups.restore-production');
            Route::get('/backups/{name}/download', [BackupAdminController::class, 'download'])->middleware('throttle:10,60')->name('backups.download');
            Route::delete('/backups/{name}', [BackupAdminController::class, 'destroy'])->name('backups.destroy');
        });
    });

    // Alte Adresse des Wiki-Exports weiterleiten
    Route::redirect('/wiki-export', '/admin/development/wiki-export')->middleware('isAdmin');

    Route::get('/submissions', [SubmissionAdminController::class, 'index'])->name('submissions.index');
    Route::post('/submissions/{type}/{id}/approve', [SubmissionAdminController::class, 'approve'])
        ->whereIn('type', ['fish', 'lake', 'river'])->whereNumber('id')->name('submissions.approve');
    Route::post('/submissions/{type}/{id}/reject', [SubmissionAdminController::class, 'reject'])
        ->whereIn('type', ['fish', 'lake', 'river'])->whereNumber('id')->name('submissions.reject');

    Route::get('/corrections', [\App\Http\Controllers\Admin\CorrectionAdminController::class, 'index'])->name('corrections.index');
    Route::post('/corrections/{correction}/resolve', [\App\Http\Controllers\Admin\CorrectionAdminController::class, 'resolve'])->name('corrections.resolve');

    Route::get('/tickets', [TicketAdminController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/{ticket}', [TicketAdminController::class, 'show'])->name('tickets.show');
    Route::delete('/tickets/{ticket}', [TicketAdminController::class, 'destroy'])->name('tickets.destroy');


    Route::resource('lake', LakeAdminController::class)->except(['show']);
    Route::resource('river', RiverAdminController::class)->except(['show']);
    Route::resource('association', AssociationAdminController::class)->except(['show']);


    Route::resource('state', StateAdminController::class)->except(['show', 'update']);
    Route::post('state/{state}/update', [StateAdminController::class, 'update'])->name('state.update');
    Route::delete('state/photo/{mediaId}', [StateAdminController::class, 'deletePhoto'])->name('state.photo.delete');


    Route::resource('fish', FishAdminController::class)->except(['show', 'update']);
    Route::post('fish/{fish}/update', [FishAdminController::class, 'update'])->name('fish.update');
    Route::delete('fish/photo/{mediaId}', [FishAdminController::class, 'deletePhoto'])->name('fish.photo.delete');

    Route::get('user', [UserAdminController::class, 'index'])->name('user.index');
    Route::middleware('isAdmin')->group(function () {
        Route::patch('user/{user}/role', [UserAdminController::class, 'updateRole'])->name('user.role');
        Route::delete('user/{user}', [UserAdminController::class, 'destroy'])->name('user.destroy');
    });

    Route::get('/newsletter', [NewsletterAdminController::class, 'index'])->name('newsletter.index');
    Route::post('/newsletter/send', [NewsletterAdminController::class, 'send'])->name('newsletter.send');
    Route::post('/newsletter/test', [NewsletterAdminController::class, 'sendTest'])->name('newsletter.test');
    Route::post('/newsletter/preview', [NewsletterAdminController::class, 'preview'])->name('newsletter.preview');
});
