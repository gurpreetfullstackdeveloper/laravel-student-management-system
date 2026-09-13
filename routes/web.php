<?php

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

Route::controller(\App\Http\Controllers\Public\PublicSiteController::class)->group(function () {
    Route::get('/', 'home')->name('public.home');
    Route::get('/about', 'about')->name('public.about');
    Route::get('/academics', 'academics')->name('public.academics');
    Route::get('/admissions', 'admissions')->name('public.admissions');
    Route::post('/admissions', 'submitAdmission')->middleware('throttle:5,1')->name('public.admissions.submit');
    Route::get('/facilities', 'facilities')->name('public.facilities');
    Route::get('/faculty', 'faculty')->name('public.faculty');
    Route::get('/events', 'events')->name('public.events');
    Route::get('/events/{slug}', 'event')->name('public.events.show');
    Route::get('/news', 'news')->name('public.news');
    Route::get('/news/{slug}', 'article')->name('public.news.show');
    Route::get('/gallery', 'gallery')->name('public.gallery');
    Route::get('/notices', 'notices')->name('public.notices');
    Route::get('/contact', 'contact')->name('public.contact');
    Route::get('/faq', 'faq')->name('public.faq');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::get('/student/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])
        ->defaults('role', 'student')
        ->name('student.login');
    Route::get('/teacher/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])
        ->defaults('role', 'teacher')
        ->name('teacher.login');
    Route::post('/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');
});

Route::post('/logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('student')->middleware(['auth', 'role:student'])->group(base_path('routes/student.php'));
Route::prefix('teacher')->middleware(['auth', 'role:teacher'])->group(base_path('routes/teacher.php'));
