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

        // Show today's attendance + any still clocked-in from previous days
        $today = \Carbon\Carbon::today();
        $liveAttendances = Attendance::with('employee')
            ->where(function ($query) use ($today) {
                // Today's records (both clocked in and clocked out)
                $query->whereDate('check_in_time', $today);
            })
            ->orWhere(function ($query) {
                // Still clocked in from previous days
                $query->where('status', 'clocked_in');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $canOverrideDate = auth()->user()->canOverrideDate();

        return view('attendance', compact('employees', 'liveAttendances', 'canOverrideDate'));
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
            'employee_id'   => $employee->id,
            'user_id'       => auth()->id(),
            'vehicle_plate' => $request->vehicle_plate,
            'check_in_time' => $request->clock_in_time,
            'status'        => 'clocked_in',
        ]);

        return redirect('/attendance')->with('success', $employee->name . ' clocked in successfully!');
    }

    // Clock Out
    public function clockOut(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $checkIn  = \Carbon\Carbon::parse($attendance->check_in_time);
        $checkOut = \Carbon\Carbon::parse($request->clock_out_time);

        // Reject if clock-out time is before or equal to clock-in time
        if ($checkOut->lte($checkIn)) {
            return redirect()->back()->with('error', 'Clock-out time cannot be before or equal to clock-in time.');
        }

        $attendance->check_out_time = $request->clock_out_time;
        $attendance->status = 'clocked_out';
        $attendance->save();

        return redirect('/attendance')->with('success', 'Clocked out successfully!');
    }

    // Update clock-in time (correction for accidental wrong time)
    public function updateCheckIn(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $request->validate([
            'check_in_time' => 'required|date',
        ]);

        $newCheckIn = \Carbon\Carbon::parse($request->check_in_time);

        // If already clocked out, ensure new check-in is still before check-out
        if ($attendance->check_out_time) {
            $checkOut = \Carbon\Carbon::parse($attendance->check_out_time);
            if ($newCheckIn->gte($checkOut)) {
                return redirect()->back()->with('error',
                    'Corrected clock-in time must be before the existing clock-out time (' . $checkOut->format('H:i') . ').');
            }
        }

        $attendance->check_in_time = $newCheckIn;
        $attendance->save();

        return redirect('/attendance')->with('success',
            'Clock-in time corrected to ' . $newCheckIn->format('d M Y, h:i A') . '.');
    }

    // Delete an attendance record (cancel a wrong clock-in entry)
    public function destroy($id)
    {
        $attendance   = Attendance::findOrFail($id);
        $employeeName = $attendance->employee->name;
        $attendance->delete();

        return redirect('/attendance')->with('success', $employeeName . "\'s attendance record has been removed.");
    }
}
