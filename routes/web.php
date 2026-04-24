<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/visitor', [App\Http\Controllers\VisitController::class, 'create'])->name('visitor.index')->middleware('auth');

Route::get('/attendance', function () {
    return view('attendance');
})->name('attendance.index');

Route::get('/report', function () {
    return view('report');
})->name('report.index');


Route::get('/dashboard', function () {
    // Fetch all visits, newest first
    $liveVisits = App\Models\Visit::with(['employee', 'visitors', 'visitors.company'])->orderBy('created_at', 'desc')->get();

    return view('dashboard', compact('liveVisits'));
})->middleware('auth');

Route::post('/visitor', [App\Http\Controllers\VisitController::class, 'store'])->name('visit.store')->middleware('auth');
Route::post('/visit/{id}/checkout', [App\Http\Controllers\VisitController::class, 'checkout'])->name('visit.checkout')->middleware('auth');

Route::get('/api/visitor/{nric}',
    [App\Http\Controllers\VisitController::class, 'findVisitor'
    ]);


Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
