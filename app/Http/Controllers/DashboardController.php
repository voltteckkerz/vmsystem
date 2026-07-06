<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Visit;
use App\Models\Attendance;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $year  = now()->year;
        $mon   = now()->month;

        // ── Live visitor table ────────────────────────────────────────────────
        $liveVisits = Visit::with(['employee', 'visitors', 'visitors.company'])
            ->where(function ($q) use ($today) {
                $q->whereDate('created_at', $today);
            })
            ->orWhere(function ($q) {
                $q->where('status', 'active');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // ── KPI cards ─────────────────────────────────────────────────────────
        $visitorsToday = DB::table('visit_visitors')
            ->join('visits', 'visits.id', '=', 'visit_visitors.visit_id')
            ->whereDate('visits.manual_check_in_time', $today)
            ->count();

        $activeNow = Visit::where('status', 'active')->count();

        $attendanceToday = Attendance::whereDate('check_in_time', $today)->count();

        $visitorsThisMonth = DB::table('visit_visitors')
            ->join('visits', 'visits.id', '=', 'visit_visitors.visit_id')
            ->whereYear('visits.manual_check_in_time', $year)
            ->whereMonth('visits.manual_check_in_time', $mon)
            ->whereNotNull('visits.manual_check_in_time')
            ->count();

        $avgDuration = Visit::whereNotNull('manual_check_in_time')
            ->whereNotNull('manual_check_out_time')
            ->whereYear('manual_check_in_time', $year)
            ->whereMonth('manual_check_in_time', $mon)
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, manual_check_in_time, manual_check_out_time)) as avg_dur')
            ->value('avg_dur');
        $avgDuration = $avgDuration ? round($avgDuration) : null;

        // ── Last 7 days trend ─────────────────────────────────────────────────
        $last7 = collect(range(6, 0))->map(fn($i) => now()->subDays($i)->toDateString());

        $visitorTrendMap = Visit::selectRaw('DATE(manual_check_in_time) as date, COUNT(*) as total')
            ->whereDate('manual_check_in_time', '>=', now()->subDays(6)->toDateString())
            ->whereNotNull('manual_check_in_time')
            ->groupBy('date')
            ->pluck('total', 'date');

        $attendTrendMap = Attendance::selectRaw('DATE(check_in_time) as date, COUNT(*) as total')
            ->whereDate('check_in_time', '>=', now()->subDays(6)->toDateString())
            ->groupBy('date')
            ->pluck('total', 'date');

        $trendLabels      = $last7->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->values()->toArray();
        $visitorTrendData = $last7->map(fn($d) => $visitorTrendMap[$d] ?? 0)->values()->toArray();
        $attendTrendData  = $last7->map(fn($d) => $attendTrendMap[$d] ?? 0)->values()->toArray();

        // ── Peak hours this month ─────────────────────────────────────────────
        $peakHourMap = Visit::selectRaw('HOUR(manual_check_in_time) as hour, COUNT(*) as total')
            ->whereYear('manual_check_in_time', $year)
            ->whereMonth('manual_check_in_time', $mon)
            ->whereNotNull('manual_check_in_time')
            ->groupBy('hour')
            ->pluck('total', 'hour');

        $peakHourLabels = [];
        $peakHourData   = [];
        for ($h = 6; $h <= 22; $h++) {
            $peakHourLabels[] = sprintf('%02d:00', $h);
            $peakHourData[]   = $peakHourMap[$h] ?? 0;
        }

        // ── Purpose breakdown this month ──────────────────────────────────────
        $purposes = Visit::selectRaw('purpose, COUNT(*) as total')
            ->whereYear('manual_check_in_time', $year)
            ->whereMonth('manual_check_in_time', $mon)
            ->whereNotNull('manual_check_in_time')
            ->groupBy('purpose')
            ->orderByDesc('total')
            ->get();

        return view('dashboard', compact(
            'liveVisits',
            'visitorsToday', 'activeNow', 'attendanceToday',
            'visitorsThisMonth', 'avgDuration',
            'trendLabels', 'visitorTrendData', 'attendTrendData',
            'peakHourLabels', 'peakHourData', 'purposes'
        ));
    }
}
