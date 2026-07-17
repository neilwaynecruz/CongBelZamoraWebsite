<?php

use App\Http\Controllers\PublicInquiryController;
use App\Http\Controllers\PublicLegislationController;
use App\Http\Controllers\PublicNewsController;
use App\Http\Controllers\PublicProgramController;
use App\Http\Controllers\PublicProjectController;
use App\Http\Controllers\PublicStaticPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicStaticPageController::class, 'home'])->name('home');
Route::get('/about', [PublicStaticPageController::class, 'about'])->name('public.about');

Route::get('/programs', [PublicProgramController::class, 'index'])->name('public.programs');
Route::get('/programs/gabay', [PublicProgramController::class, 'gabay'])->name('public.programs.gabay');
Route::get('/programs/education/tulong-dunong', [PublicProgramController::class, 'tulongDunong'])->name('public.programs.tulong-dunong');

Route::get('/legislation', [PublicLegislationController::class, 'index'])->name('public.legislation');

Route::get('/projects', [PublicProjectController::class, 'index'])->name('public.projects');

Route::get('/news', [PublicNewsController::class, 'index'])->name('public.news');

Route::get('/contact', [PublicStaticPageController::class, 'contact'])->name('public.contact');

Route::post('/contact/inquiries', [PublicInquiryController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('public.contact.inquiries.store');

Route::get('/privacy', [PublicStaticPageController::class, 'privacy'])->name('public.privacy');
Route::get('/accessibility', [PublicStaticPageController::class, 'accessibility'])->name('public.accessibility');
Route::get('/disclaimer', [PublicStaticPageController::class, 'disclaimer'])->name('public.disclaimer');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
