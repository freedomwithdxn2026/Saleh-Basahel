<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LeadController as AdminLeadController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\CalendlyWebhookController;
use App\Http\Middleware\AdminAuthenticated;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/en')->name('home');

Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.store');

Route::prefix('admin')->name('admin.')->middleware(AdminAuthenticated::class)->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    Route::get('/leads', [AdminLeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/export', [AdminLeadController::class, 'export'])->name('leads.export');
    Route::get('/leads/{lead}', [AdminLeadController::class, 'show'])->name('leads.show');
    Route::post('/leads/{lead}', [AdminLeadController::class, 'update'])->name('leads.update');
    Route::delete('/leads/{lead}', [AdminLeadController::class, 'destroy'])->name('leads.destroy');
    Route::get('/content', [AdminContentController::class, 'edit'])->name('content.edit');
    Route::post('/content', [AdminContentController::class, 'update'])->name('content.update');
    Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
});

Route::pattern('locale', 'en|ar');
Route::pattern('section', 'video-overview|how-it-works|success-stories|wellness-lifestyle|business-opportunity|about-saleh|faq|contact');

Route::get('/{locale}/privacy-policy', function (string $locale) {
    App::setLocale($locale);
    session(['locale' => $locale]);

    return view('privacy-policy', [
        'locale' => $locale,
        'isRtl' => $locale === 'ar',
    ]);
})->name('privacy-policy');
Route::get('/{locale}/{section?}', function (string $locale, ?string $section = null) {
    App::setLocale($locale);
    session(['locale' => $locale]);

    return view('saleh-basahel-landing-page', [
        'locale' => $locale,
        'isRtl' => $locale === 'ar',
        'section' => $section,
    ]);
})->name('home.localized');

Route::post('/{locale}/leads', [LeadController::class, 'store'])->name('leads.store');
Route::post('/api/leads', [LeadController::class, 'webhook'])->name('leads.webhook');
Route::post('/api/webhooks/calendly', CalendlyWebhookController::class)->name('webhooks.calendly');
