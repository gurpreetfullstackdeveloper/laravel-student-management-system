<?php

use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\AttendanceController;
use App\Http\Controllers\Student\EnrollmentController;
use App\Http\Controllers\Student\FeeController;
use App\Http\Controllers\Student\FeePaymentController;
use App\Http\Controllers\Student\NoticeController;
use App\Http\Controllers\Student\ProfileController;
use App\Http\Controllers\Student\ResultController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', DashboardController::class)->name('student.dashboard');
Route::get('/profile', [ProfileController::class, 'edit'])->name('student.profile.edit');
Route::put('/profile', [ProfileController::class, 'update'])->name('student.profile.update');
Route::get('/enrollments', [EnrollmentController::class, 'index'])->name('student.enrollments.index');
Route::get('/attendance', [AttendanceController::class, 'index'])->name('student.attendance.index');
Route::get('/results', [ResultController::class, 'index'])->name('student.results.index');
Route::get('/results/{examResult}', [ResultController::class, 'show'])->name('student.results.show');
Route::get('/fees', [FeeController::class, 'index'])->name('student.fees.index');
Route::get('/fees/{studentFee}', [FeeController::class, 'show'])->name('student.fees.show');
Route::get('/fee-payments/{feePayment}/receipt', [FeePaymentController::class, 'receipt'])->name('student.fee-payments.receipt');
Route::get('/notices', [NoticeController::class, 'index'])->name('student.notices.index');
Route::get('/notices/{notice}', [NoticeController::class, 'show'])->name('student.notices.show');