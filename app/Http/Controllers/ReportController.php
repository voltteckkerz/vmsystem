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
        // Get date range from the form, or default to today
        $from_date = $request->input('from_date', now()->toDateString());
        $to_date = $request->input('to_date', now()->toDateString());
        $name = $request->input('name');

        //Visitor Report
        $visits = Visit::with(['employee', 'visitors', 'visitors.company'])
            ->whereDate('manual_check_in_time', '>=', $from_date)
            ->whereDate('manual_check_in_time', '<=', $to_date)
            ->when($name, function ($query, $name) {
                return $query->whereHas('visitors', function ($q) use ($name) {
                    $q->where('name', 'like', "%{$name}%");
                });
            })
            ->orderBy('manual_check_in_time', 'desc')
            ->get();

        // Filter to only show matching visitors within each visit
        if ($name) {
            $visits->each(function ($visit) use ($name) {
                $filtered = $visit->visitors->filter(function ($visitor) use ($name) {
                    return str_contains(strtolower($visitor->name), strtolower($name));
                });
                $visit->setRelation('visitors', $filtered);
            });
        }

            // Attendance Report
            $attendances = Attendance::with('employee')
                ->whereDate('check_in_time', '>=', $from_date)
                ->whereDate('check_in_time', '<=', $to_date)
                ->when($name, function ($query, $name) {
                    return $query->whereHas('employee', function ($q) use ($name) {
                        $q->where('name', 'like', "%{$name}%");
                    });
                })
                ->orderBy('check_in_time', 'desc')
                ->get();

                return view('report', compact('visits', 'attendances', 'from_date', 'to_date'));
                
    }
        public function print(Request $request, string $filename)
    {
        $from_date = $request->input('from_date', now()->toDateString());
        $to_date = $request->input('to_date', now()->toDateString());
        $type = $request->input('type');

        if ($type == 'visitor') {
            // Flatten visitors into individual rows, sorted A-Z
            $data = Visit::with(['employee', 'visitors', 'visitors.company'])
                ->whereDate('manual_check_in_time', '>=', $from_date)
                ->whereDate('manual_check_in_time', '<=', $to_date)
                ->get()
                ->flatMap(fn($visit) => $visit->visitors->map(fn($visitor) => (object)[
                    'name'     => $visitor->name,
                    'date'     => $visit->manual_check_in_time,
                    'company'  => $visitor->company->name ?? '-',
                    'time_in'  => $visit->manual_check_in_time,
                    'time_out' => $visit->manual_check_out_time,
                    'pass_id'  => $visitor->pivot->pass_id ?? null,
                ]))
                ->sortBy(fn($r) => strtolower($r->name))
                ->values();

            $view = 'pdf.visitor_report';
            $filename = "Visitor_Report_{$from_date}_to_{$to_date}.pdf";
        } else {
            // Sort attendance A-Z by employee name
            $data = Attendance::with('employee')
                ->whereDate('check_in_time', '>=', $from_date)
                ->whereDate('check_in_time', '<=', $to_date)
                ->get()
                ->sortBy(fn($a) => strtolower($a->employee->name ?? ''))
                ->values();

            $view = 'pdf.attendance_report';
            $filename = "Attendance_Report_{$from_date}_to_{$to_date}.pdf";
        }

        $pdf = Pdf::loadView($view, compact('data', 'from_date', 'to_date'));

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

}
