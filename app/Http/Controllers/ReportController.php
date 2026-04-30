<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Visit;
use App\Models\Attendance;
use Barryvdh\DomPDF\Facade\Pdf;

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
        public function print(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $type = $request->input('type'); // 'visitor' or 'attendance'

        if ($type == 'visitor') {
            $data = Visit::with(['employee', 'visitors', 'visitors.company'])
                ->whereDate('manual_check_in_time', $date)
                ->orderBy('manual_check_in_time', 'desc')
                ->get();
            $view = 'pdf.visitor_report';
            $filename = "Visitor_Report_{$date}.pdf";
        } else {
            $data = Attendance::with('employee')
                ->whereDate('check_in_time', $date)
                ->orderBy('check_in_time', 'desc')
                ->get();
            $view = 'pdf.attendance_report';
            $filename = "Attendance_Report_{$date}.pdf";
        }

        // Generate the PDF
        $pdf = Pdf::loadView($view, compact('data', 'date'));
        
        // ->stream() OPENS the PDF in the browser (does not auto-download)
        // ->download() would force auto-download
        return $pdf->stream($filename); 
    }

}
