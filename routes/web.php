<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EmployeeController as AdminEmployeeController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\LeaveController as AdminLeaveController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Employee\AttendanceController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\DirectoryController;
use App\Http\Controllers\Employee\LeaveController as EmployeeLeaveController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if ($user = Auth::user()) {
        return redirect()->route($user->isAdmin() ? 'admin.dashboard' : 'employee.dashboard');
    }
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/events', [EventController::class, 'index'])->name('events.index');

    Route::get('/me/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/me/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Employee routes
Route::middleware(['auth', 'employee'])->prefix('me')->name('employee.')->group(function () {
    Route::get('/', EmployeeDashboardController::class)->name('dashboard');

    Route::get('/leaves', [EmployeeLeaveController::class, 'index'])->name('leaves');
    Route::post('/leaves', [EmployeeLeaveController::class, 'store'])->name('leaves.store');

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
    Route::post('/attendance/{attendance}/note', [AttendanceController::class, 'updateNote'])->name('attendance.note');
    Route::post('/attendance/check-in',  [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
    Route::post('/attendance/break-in',  [AttendanceController::class, 'breakIn'])->name('attendance.breakin');
    Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut'])->name('attendance.breakout');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');

    Route::get('/directory', DirectoryController::class)->name('directory');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    Route::get('/employees',          [AdminEmployeeController::class, 'index'])->name('employees.index');
    Route::post('/employees',         [AdminEmployeeController::class, 'store'])->name('employees.store');
    Route::put('/employees/{employee}', [AdminEmployeeController::class, 'update'])->name('employees.update');
    Route::get('/employees/{employee}/attendance', [AdminEmployeeController::class, 'attendance'])->name('employees.attendance');
    Route::put('/attendance/{attendance}', [AdminEmployeeController::class, 'updateAttendance'])->name('attendance.update');
    Route::delete('/employees/{employee}', [AdminEmployeeController::class, 'destroy'])->name('employees.destroy');

    Route::get('/directory', DirectoryController::class)->name('directory');

    Route::get('/leaves',                  [AdminLeaveController::class, 'index'])->name('leaves.index');
    Route::post('/leaves/{leave}/approve', [AdminLeaveController::class, 'approve'])->name('leaves.approve');
    Route::post('/leaves/{leave}/reject',  [AdminLeaveController::class, 'reject'])->name('leaves.reject');

    Route::post('/events',           [AdminEventController::class, 'store'])->name('events.store');
    Route::put('/events/{event}',    [AdminEventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [AdminEventController::class, 'destroy'])->name('events.destroy');
});
