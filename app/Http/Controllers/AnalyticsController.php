<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Visit;
use App\Models\Attendance;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        [$year, $mon] = explode('-', $month);
        $year = (int) $year;
        $mon  = (int) $mon;

        $daysInMonth = \Carbon\Carbon::createFromDate($year, $mon, 1)->daysInMonth;
        $dayLabels   = range(1, $daysInMonth);

        // ── Visitor KPIs ─────────────────────────────────────────────────────
        $totalVisitorsMonth = DB::table('visit_visitors')
            ->join('visits', 'visits.id', '=', 'visit_visitors.visit_id')
            ->whereYear('visits.manual_check_in_time', $year)
            ->whereMonth('visits.manual_check_in_time', $mon)
            ->whereNotNull('visits.manual_check_in_time')
            ->count();

        $totalVisitorsToday = DB::table('visit_visitors')
            ->join('visits', 'visits.id', '=', 'visit_visitors.visit_id')
            ->whereDate('visits.manual_check_in_time', today())
            ->count();

        $avgDurationMinutes = Visit::whereNotNull('manual_check_in_time')
            ->whereNotNull('manual_check_out_time')
            ->whereYear('manual_check_in_time', $year)
            ->whereMonth('manual_check_in_time', $mon)
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, manual_check_in_time, manual_check_out_time)) as avg_dur')
            ->value('avg_dur');
        $avgDurationMinutes = $avgDurationMinutes ? round($avgDurationMinutes) : null;

        // ── Attendance KPIs ───────────────────────────────────────────────────
        $totalAttendanceMonth = Attendance::whereYear('check_in_time', $year)
            ->whereMonth('check_in_time', $mon)
            ->count();

        $totalAttendanceToday = Attendance::whereDate('check_in_time', today())->count();

        // ── Daily visitor trend ───────────────────────────────────────────────
        $dailyVisitorMap = Visit::selectRaw('DATE(manual_check_in_time) as date, COUNT(*) as total')
            ->whereYear('manual_check_in_time', $year)
            ->whereMonth('manual_check_in_time', $mon)
            ->whereNotNull('manual_check_in_time')
            ->groupBy('date')
            ->pluck('total', 'date');

        $dailyVisitorData = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = sprintf('%04d-%02d-%02d', $year, $mon, $d);
            $dailyVisitorData[] = $dailyVisitorMap[$date] ?? 0;
        }

        // ── Daily attendance trend ────────────────────────────────────────────
        $dailyAttMap = Attendance::selectRaw('DATE(check_in_time) as date, COUNT(*) as total')
            ->whereYear('check_in_time', $year)
            ->whereMonth('check_in_time', $mon)
            ->groupBy('date')
            ->pluck('total', 'date');

        $dailyAttendanceData = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = sprintf('%04d-%02d-%02d', $year, $mon, $d);
            $dailyAttendanceData[] = $dailyAttMap[$date] ?? 0;
        }

        // ── Peak visitor hours (0–23) ─────────────────────────────────────────
        $peakHourMap = Visit::selectRaw('HOUR(manual_check_in_time) as hour, COUNT(*) as total')
            ->whereYear('manual_check_in_time', $year)
            ->whereMonth('manual_check_in_time', $mon)
            ->whereNotNull('manual_check_in_time')
            ->groupBy('hour')
            ->pluck('total', 'hour');

        $peakHourData   = [];
        $peakHourLabels = [];
        for ($h = 6; $h <= 22; $h++) {
            $peakHourData[]   = $peakHourMap[$h] ?? 0;
            $peakHourLabels[] = sprintf('%02d:00', $h);
        }

        // ── Visit purpose breakdown ───────────────────────────────────────────
        $purposes = Visit::selectRaw('purpose, COUNT(*) as total')
            ->whereYear('manual_check_in_time', $year)
            ->whereMonth('manual_check_in_time', $mon)
            ->whereNotNull('manual_check_in_time')
            ->groupBy('purpose')
            ->orderByDesc('total')
            ->get();

        // ── Top 6 visiting companies ──────────────────────────────────────────
        $topCompanies = DB::table('visit_visitors')
            ->join('visits', 'visits.id', '=', 'visit_visitors.visit_id')
            ->selectRaw("COALESCE(NULLIF(TRIM(visit_visitors.visitor_company),''), 'Unknown') as company, COUNT(*) as total")
            ->whereYear('visits.manual_check_in_time', $year)
            ->whereMonth('visits.manual_check_in_time', $mon)
            ->whereNotNull('visits.manual_check_in_time')
            ->groupBy('company')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        // ── Most frequent visitors (top 10) ───────────────────────────────────
        $frequentVisitors = DB::table('visit_visitors')
            ->join('visits', 'visits.id', '=', 'visit_visitors.visit_id')
            ->join('visitors', 'visitors.id', '=', 'visit_visitors.visitor_id')
            ->selectRaw("COALESCE(NULLIF(TRIM(visit_visitors.visitor_name),''), visitors.name) as vname,
                         COALESCE(NULLIF(TRIM(visit_visitors.visitor_company),''), 'Unknown') as vcompany,
                         COUNT(*) as visit_count")
            ->whereYear('visits.manual_check_in_time', $year)
            ->whereMonth('visits.manual_check_in_time', $mon)
            ->whereNotNull('visits.manual_check_in_time')
            ->groupBy('visit_visitors.visitor_id', 'vname', 'vcompany')
            ->orderByDesc('visit_count')
            ->limit(10)
            ->get();

        return view('analytics', compact(
            'month', 'year', 'mon', 'dayLabels',
            'totalVisitorsMonth', 'totalVisitorsToday',
            'avgDurationMinutes', 'totalAttendanceMonth', 'totalAttendanceToday',
            'dailyVisitorData', 'dailyAttendanceData',
            'peakHourData', 'peakHourLabels',
            'purposes', 'topCompanies', 'frequentVisitors'
        ));
    }
}
