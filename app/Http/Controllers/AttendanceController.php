<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    // Show the attendance page
    public function index()
    {
        $employees = Employee::with('vehicles')->where('status', 'active')->get();
        $liveAttendances = Attendance::with('employee')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('attendance', compact('employees', 'liveAttendances'));
    }

    // Clock In
    public function clockIn(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $employee = Employee::findOrFail($request->employee_id);

        // Check if already clocked in
        $alreadyClockedIn = Attendance::where('employee_id', $employee->id)
            ->where('status', 'clocked_in')
            ->first();

        if ($alreadyClockedIn) {
            return redirect('/attendance')->with('error', $employee->name . ' is already clocked in!');
        }

        Attendance::create([
            'employee_id' => $employee->id,
            'user_id' => auth()->id(),
            'vehicle_plate' => $request->vehicle_plate,
            'check_in_time' => $request->clock_in_time,
            'status' => 'clocked_in',
        ]);

        return redirect('/attendance')->with('success', $employee->name . ' clocked in successfully!');
    }

    // Clock Out
    public function clockOut(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // Reject if clock-out time is before or equal to clock-in time
        if ($request->clock_out_time <= $attendance->check_in_time) {
            return redirect()->back()->with('error', 'Clock-out time cannot be before or equal to clock-in time.');
        }

        $attendance->check_out_time = $request->clock_out_time;
        $attendance->status = 'clocked_out';
        $attendance->save();

        return redirect('/attendance')->with('success', 'Clocked out successfully!');
    }
}
