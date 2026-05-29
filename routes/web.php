<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/visitor', [App\Http\Controllers\VisitController::class, 'create'])->name('visitor.index')->middleware('auth');

Route::post('/employees', [App\Http\Controllers\EmployeeController::class, 'store'])->name('employees.store')->middleware('auth');
Route::delete('/employees/{id}', [App\Http\Controllers\EmployeeController::class, 'destroy'])->name('employees.destroy')->middleware('auth');

// Attendance Routes
Route::get('/attendance', [App\Http\Controllers\AttendanceController::class, 'index'])->name('attendance.index')->middleware('auth');
Route::post('/attendance/clock-in', [App\Http\Controllers\AttendanceController::class, 'clockIn'])->name('attendance.clockIn')->middleware('auth');
Route::post('/attendance/{id}/clock-out', [App\Http\Controllers\AttendanceController::class, 'clockOut'])->name('attendance.clockOut')->middleware('auth');
Route::post('/attendance/{id}/update-checkin', [App\Http\Controllers\AttendanceController::class, 'updateCheckIn'])->name('attendance.updateCheckIn')->middleware('auth');
Route::delete('/attendance/{id}', [App\Http\Controllers\AttendanceController::class, 'destroy'])->name('attendance.destroy')->middleware('auth');

// Report Routes
Route::get('/report', [App\Http\Controllers\ReportController::class, 'index'])->name('report.index')->middleware('auth');

Route::get('/report/print/{filename}', [App\Http\Controllers\ReportController::class,'print'])->name('report.print')->middleware('auth');

// Import Routes
Route::get('/import', [App\Http\Controllers\ImportController::class, 'index'])->name('import.index')->middleware('auth');
Route::post('/import', [App\Http\Controllers\ImportController::class, 'import'])->name('import.upload')->middleware('auth');


// Dashboard Route
Route::get('/dashboard', function () {
    // Show today's visits + any still-active visits from previous days
    $today = now()->toDateString();
    $liveVisits = App\Models\Visit::with(['employee', 'visitors', 'visitors.company'])
        ->where(function ($query) use ($today) {
            // Today's visits (both active and completed)
            $query->whereDate('created_at', $today);
        })
        ->orWhere(function ($query) {
            // Still active from previous days (not checked out yet)
            $query->where('status', 'active');
        })
        ->orderBy('created_at', 'desc')
        ->get();

    return view('dashboard', compact('liveVisits'));
})->name('dashboard.index')->middleware('auth');

// Visitor Routes
Route::post('/visitor', [App\Http\Controllers\VisitController::class, 'store'])->name('visit.store')->middleware('auth');
Route::put('/visitor/{id}', [App\Http\Controllers\VisitController::class, 'updateVisitor'])->name('visit.updateVisitor')->middleware('auth');
Route::delete('/visitor/{id}', [App\Http\Controllers\VisitController::class, 'destroyVisitor'])->name('visit.destroyVisitor')->middleware('auth');
Route::post('/visit/{id}/checkout', [App\Http\Controllers\VisitController::class, 'checkout'])->name('visit.checkout')->middleware('auth');
Route::post('/visit/{id}/update-checkin', [App\Http\Controllers\VisitController::class, 'updateVisitCheckIn'])->name('visit.updateCheckIn')->middleware('auth');
Route::delete('/visit/{id}', [App\Http\Controllers\VisitController::class, 'destroyVisit'])->name('visit.destroyVisit')->middleware('auth');

Route::get('/api/visitor/{nric}',
    [App\Http\Controllers\VisitController::class, 'findVisitor'
    ]);




Auth::routes(['register' => false, 'reset' => false, 'confirm' => false, 'verify' => false]);

Route::get('/home', function () {
    return redirect('/dashboard');
})->name('home');

