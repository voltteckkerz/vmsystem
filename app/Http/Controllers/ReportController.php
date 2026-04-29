<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Visit;
use App\Models\Attendance;

class ReportController extends Controller
{
    public function index(Request $request)
    {

        // Get the selected date from the form, or default to today
        $date = $request->input('date', now()->toDateString());

        //Visitor Report
        $visits = Visit::with(['employee', 'visitors', 'visitors.company'])
            ->whereDate('manual_check_in_time', $date)
            ->orderBy('manual_check_in_time', 'desc')
            ->get();

            // Attendance Report
            $attendances = Attendance::with('employee')
                ->whereDate('check_in_time', $date)
                ->orderBy('check_in_time', 'desc')
                ->get();

                return view('report', compact('visits', 'attendances', 'date'));
                
    }
}
