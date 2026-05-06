<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/visitor', [App\Http\Controllers\VisitController::class, 'create'])->name('visitor.index')->middleware('auth');

// Attendance Routes
Route::get('/attendance', [App\Http\Controllers\AttendanceController::class, 'index'])->name('attendance.index')->middleware('auth');
Route::post('/attendance/clock-in', [App\Http\Controllers\AttendanceController::class, 'clockIn'])->name('attendance.clockIn')->middleware('auth');
Route::post('/attendance/{id}/clock-out', [App\Http\Controllers\AttendanceController::class, 'clockOut'])->name('attendance.clockOut')->middleware('auth');

// Report Routes
Route::get('/report', [App\Http\Controllers\ReportController::class, 'index'])->name('report.index')->middleware('auth');

Route::get('/report/print', [App\Http\Controllers\ReportController::class,'print'])->name('report.print')->middleware('auth');


// Dashboard Route
Route::get('/dashboard', function () {
    // Fetch all visits, newest first
    $liveVisits = App\Models\Visit::with(['employee', 'visitors', 'visitors.company'])->orderBy('created_at', 'desc')->get();

    return view('dashboard', compact('liveVisits'));
})->name('dashboard.index')->middleware('auth');

// Visitor Routes
Route::post('/visitor', [App\Http\Controllers\VisitController::class, 'store'])->name('visit.store')->middleware('auth');
Route::post('/visit/{id}/checkout', [App\Http\Controllers\VisitController::class, 'checkout'])->name('visit.checkout')->middleware('auth');

Route::get('/api/visitor/{nric}',
    [App\Http\Controllers\VisitController::class, 'findVisitor'
    ]);




Auth::routes(['register' => false, 'reset' => false]);

Route::get('/home', function () {
    return redirect('/dashboard');
})->name('home');

