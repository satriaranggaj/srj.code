<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'bio'])->name('about');
Route::get('/projects', [HomeController::class, 'projects'])->name('project');
Route::get('/certificates', [HomeController::class, 'certificate'])->name('certificate');
Route::get('/resume', [HomeController::class, 'resume'])->name('resume');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
// Route::get('/blog', [HomeController::class, 'blog'])->name('blog');
// Route::get('/blog/{slug}', [HomeController::class, 'blogPost'])->name('blog-post');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Routes Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Routes Skill
    Route::get('/skill', [DashboardController::class, 'indexSkill'])->name('skill.index');
    Route::get('/skill/create', [DashboardController::class, 'createSkill'])->name('skill.create');
    Route::post('/skill', [DashboardController::class, 'storeSkill'])->name('skill.store');
    Route::get('/skill/{skill}/edit', [DashboardController::class, 'editSkill'])->name('skill.edit');
    Route::put('/skill/{skill}', [DashboardController::class, 'updateSkill'])->name('skill.update');
    Route::delete('/skill/{skill}', [DashboardController::class, 'destroySkill'])->name('skill.destroy');

    // Routes Project
    Route::get('/project', [DashboardController::class, 'indexProject'])->name('project.index');
    Route::get('/project/create', [DashboardController::class, 'createProject'])->name('project.create');
    Route::post('/project', [DashboardController::class, 'storeProject'])->name('project.store');
    Route::get('/project/{project}/edit', [DashboardController::class, 'editProject'])->name('project.edit');
    Route::put('/project/{project}', [DashboardController::class, 'updateProject'])->name('project.update');
    Route::delete('/project/{project}', [DashboardController::class, 'destroyProject'])->name('project.destroy');

    // Routes Certificate
    Route::get('/certificate', [DashboardController::class, 'indexCertificate'])->name('certificate.index');
    Route::get('/certificate/create', [DashboardController::class, 'createCertificate'])->name('certificate.create');
    Route::post('/certificate', [DashboardController::class, 'storeCertificate'])->name('certificate.store');
    Route::get('/certificate/{certificate}/edit', [DashboardController::class, 'editCertificate'])->name('certificate.edit');
    Route::put('/certificate/{certificate}', [DashboardController::class, 'updateCertificate'])->name('certificate.update');
    Route::delete('/certificate/{certificate}', [DashboardController::class, 'destroyCertificate'])->name('certificate.destroy');
});

require __DIR__.'/auth.php';
