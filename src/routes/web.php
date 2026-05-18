<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth','verified','staff'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index']);
    Route::post('/attendance',[AttendanceController::class, 'store']);
    Route::get('/attendance/list', [AttendanceController::class, 'attendance_list']);
    Route::get('/attendance/detail/{attendance_id}', [AttendanceController::class, 'attendance_detail'])->name('attendance.detail');
    Route::post('/attendance/detail/{attendance_id}', [AttendanceController::class, 'attendance_detail_modify']);
    Route::get('/attendance/detail/date/{date}', [AttendanceController::class, 'attendance_detail_by_date'])->name('attendance.detail.date');
    Route::post('/attendance/detail/date/{date}', [AttendanceController::class, 'attendance_detail_modify_by_date']);
    Route::get('/stamp_correction_request/list', [AttendanceController::class, 'request_list']);
});
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'login']);
});
Route::prefix('admin')->middleware(['auth','admin','verified'])->group(function () {
    Route::get('/attendance/list', [AdminController::class, 'index']);
    Route::get('/attendance/detail/{attendance_id}', [AdminController::class, 'admin_attendance_detail']);
    Route::post('/attendance/detail/{attendance_id}', [AdminController::class, 'admin_attendance_detail_modify']);
    Route::get('/staff/list', [AdminController::class, 'admin_staff_list']);
    Route::get('/attendance/staff/{user_id}', [AdminController::class, 'admin_staff_attendance']);
    Route::get('/attendance/staff/{user_id}/csv', [AdminController::class, 'admin_staff_attendance_csv']);
    Route::get('/stamp_correction_request/list', [AdminController::class, 'admin_request_list']);
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminController::class, 'admin_request_approve']);
    Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminController::class, 'admin_request_approved']);
    Route::get('/logout',[AdminController::class, 'getLogout'])->name('admin.logout');
});
