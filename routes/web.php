<?php

use App\Models\Post;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

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

Route::get('/', [PostController::class, 'index']);

Route::get('/about', [PostController::class, 'bio']);

Route::get('/projects', [PostController::class, 'projects']);

Route::get('/blog', [PostController::class, 'blog']);

Route::get('/certificates', [PostController::class, 'certificates']);

Route::get('/resume', [PostController::class, 'resume']);

Route::get('/contact', [PostController::class, 'contact']);

Route::get('/{post:slug}', [PostController::class, 'blogPost']);


