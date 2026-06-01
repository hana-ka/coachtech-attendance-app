<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginViewResponse;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CorrectionRequestController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminCorrectionRequestController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;


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

Route::get('/admin/login', function () {
    return app(LoginViewResponse::class);
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/attendance', [AttendanceController::class, 'index']);

    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);

    Route::post('/break/start', [AttendanceController::class, 'breakStart']);

    Route::post('/break/end', [AttendanceController::class, 'breakEnd']);

    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut']);

    Route::get('/attendance/list', [AttendanceController::class, 'list']);

    Route::get(
    '/attendance/detail/{id}',
    [AttendanceController::class, 'show'])
    ->name('attendance.detail');

    Route::post(
    '/attendance/correction/{id}',
    [CorrectionRequestController::class, 'store'])->name('correction.store');

    Route::get(
    '/stamp_correction_request/list',
    [CorrectionRequestController::class, 'index'])->name('request.list');

    Route::get(
    '/admin/attendance/list',
    [AdminAttendanceController::class, 'index'])->name('admin.attendance.list');

    Route::get(
    '/admin/attendance/{id}',
    [AdminAttendanceController::class, 'show'])->name('admin.attendance.detail');

    Route::post(
    '/admin/attendance/{id}',
    [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');

    Route::get(
    '/admin/staff/list',
    [AdminUserController::class, 'index'])
    ->name('admin.staff.list');

    Route::get(
    '/admin/attendance/staff/{id}',
    [AdminUserController::class, 'attendance'])->name('admin.staff.attendance');

    Route::get(
    '/admin/attendance/staff/{id}/csv',
    [AdminUserController::class, 'exportCsv'])->name('admin.staff.csv');


    Route::get(
    '/stamp_correction_request/approve/{attendance_correct_request_id}',
    [AdminCorrectionRequestController::class, 'show'])->name('admin.request.approve');

    Route::post(
    '/stamp_correction_request/approve/{attendance_correct_request_id}',
    [AdminCorrectionRequestController::class, 'approve'])->name('admin.request.approve.update');

});

Route::middleware('auth')->group(function () {

    Route::get('/email/verify', function () {
        return view('auth.verify-email');
        })->name('verification.notice');


    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back();
    })->name('verification.send');


    Route::get(
    '/email/verify/{id}/{hash}',
    function (EmailVerificationRequest $request) {

        $request->fulfill();

        if ($request->user()->role === 'admin') {

            return redirect('/admin/attendance/list');
        }

        return redirect('/attendance');
    })->middleware('signed')
    ->name('verification.verify');
});


