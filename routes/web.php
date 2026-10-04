<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/projects', [ProjectController::class, 'index'])->name('project');
Route::get('/projects/{project:slug}', [ProjectController::class, 'show'])->name('project.show');
Route::get('/certificates', [CertificateController::class, 'index'])->name('certificate');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:contact')
    ->name('contact.store');

Route::get('/sitemap.xml', [ProjectController::class, 'sitemap'])->name('sitemap');

/*
|--------------------------------------------------------------------------
| Admin login
|--------------------------------------------------------------------------
|
| Authenticated routes live in routes/auth.php. Self-registration is disabled
| there by default; see the note in that file.
|
*/

/*
|--------------------------------------------------------------------------
| Authenticated admin routes
|--------------------------------------------------------------------------
|
| Every existing route name is preserved verbatim so no Blade reference or
| bookmark breaks. The controllers are now split per resource instead of living
| inside one DashboardController.
|
*/

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Skill
    Route::get('/skill', [Admin\SkillController::class, 'index'])->name('skill.index');
    Route::get('/skill/create', [Admin\SkillController::class, 'create'])->name('skill.create');
    Route::post('/skill', [Admin\SkillController::class, 'store'])->name('skill.store');
    Route::get('/skill/{skill}/edit', [Admin\SkillController::class, 'edit'])->name('skill.edit');
    Route::put('/skill/{skill}', [Admin\SkillController::class, 'update'])->name('skill.update');
    Route::delete('/skill/{skill}', [Admin\SkillController::class, 'destroy'])->name('skill.destroy');

    // Project
    Route::get('/project', [Admin\ProjectController::class, 'index'])->name('project.index');
    Route::get('/project/create', [Admin\ProjectController::class, 'create'])->name('project.create');
    Route::post('/project', [Admin\ProjectController::class, 'store'])->name('project.store');
    Route::get('/project/{project}/edit', [Admin\ProjectController::class, 'edit'])->name('project.edit');
    Route::put('/project/{project}', [Admin\ProjectController::class, 'update'])->name('project.update');
    Route::delete('/project/{project}', [Admin\ProjectController::class, 'destroy'])->name('project.destroy');

    // Certificate
    Route::get('/certificate', [Admin\CertificateController::class, 'index'])->name('certificate.index');
    Route::get('/certificate/create', [Admin\CertificateController::class, 'create'])->name('certificate.create');
    Route::post('/certificate', [Admin\CertificateController::class, 'store'])->name('certificate.store');
    Route::get('/certificate/{certificate}/edit', [Admin\CertificateController::class, 'edit'])->name('certificate.edit');
    Route::put('/certificate/{certificate}', [Admin\CertificateController::class, 'update'])->name('certificate.update');
    Route::delete('/certificate/{certificate}', [Admin\CertificateController::class, 'destroy'])->name('certificate.destroy');

    // Contact messages
    Route::get('/messages', [Admin\ContactMessageController::class, 'index'])->name('message.index');
    Route::patch('/messages/{contactMessage}/read', [Admin\ContactMessageController::class, 'toggleRead'])->name('message.read');
    Route::delete('/messages/{contactMessage}', [Admin\ContactMessageController::class, 'destroy'])->name('message.destroy');
});

require __DIR__.'/auth.php';
