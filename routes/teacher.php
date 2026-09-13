<?php

use App\Http\Controllers\Teacher\DashboardController;
use App\Http\Controllers\Teacher\AssignmentController;
use App\Http\Controllers\Teacher\AttendanceController;
use App\Http\Controllers\Teacher\MarkController;
use App\Http\Controllers\Teacher\NoticeController;
use App\Http\Controllers\Teacher\ProfileController;
use App\Http\Controllers\Teacher\ResultController;
use App\Http\Controllers\Teacher\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', DashboardController::class)->name('teacher.dashboard');
Route::get('/profile', [ProfileController::class, 'edit'])->name('teacher.profile.edit');
Route::put('/profile', [ProfileController::class, 'update'])->name('teacher.profile.update');
Route::get('/assignments', [AssignmentController::class, 'index'])->name('teacher.assignments.index');
Route::get('/students', [StudentController::class, 'index'])->name('teacher.students.index');
Route::get('/attendance/{assignment}', [AttendanceController::class, 'edit'])->name('teacher.attendance.edit');
Route::put('/attendance/{assignment}', [AttendanceController::class, 'update'])->name('teacher.attendance.update');
Route::get('/marks/{assignment}', [MarkController::class, 'edit'])->name('teacher.marks.edit');
Route::put('/marks/{assignment}', [MarkController::class, 'update'])->name('teacher.marks.update');
Route::get('/results/{assignment}', [ResultController::class, 'index'])->name('teacher.results.index');
Route::get('/notices', [NoticeController::class, 'index'])->name('teacher.notices.index');