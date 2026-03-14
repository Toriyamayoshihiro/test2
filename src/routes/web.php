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

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/attendance', [AdminController::class, 'index']);
    Route::get('/attendance/list', [AdminController::class, 'attendance_list']);
    Route::get('//attendance/detail/{attendacne_id}', [AdminController::class, 'attendance_detail']);
    Route::get('/stamp_correction_request/list', [AdminController::class, 'request_list']);
});
Route::prefix('admin')->middleware('guest')->group(function () {
    Route::get('/admin/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AdminController::class, 'login']);
});
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/attendance/list', [AdminController::class, 'index']);
    Route::get('/admin/attendance/{attendance_id}', [AdminController::class, 'admin_attendance_detail']);
    Route::get('/admin/staff/list', [AdminController::class, 'admin_staff_list']);
    Route::get('/admin/attendance/staff/{user_id}', [AdminController::class, 'admin_staff_attendance']);
    Route::get('/stamp_correction_request/list', [AdminController::class, 'admin_request_list']);
    Route::get('//stamp_correction_request/approve/{attendance_correct_request_id}', [AdminController::class, 'admin_request_approve']);
});
