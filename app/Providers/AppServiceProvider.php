<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Attendance;
use App\Models\Visitor;
use App\Models\Visit;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share today's unique person counts with the navbar
        View::composer('layouts.app', function ($view) {
            $today = Carbon::today();

            // Count distinct employees who checked in today
            $attendanceCount = Attendance::whereDate('check_in_time', $today)
                ->distinct('employee_id')
                ->count('employee_id');

            // Count distinct visitors from today's visits
            $todayVisitIds = Visit::whereDate('created_at', $today)->pluck('id');
            $visitorCount = Visitor::whereHas('visits', function ($query) use ($todayVisitIds) {
                $query->whereIn('visits.id', $todayVisitIds);
            })->count();

            $view->with([
                'navTodayAttendance' => $attendanceCount,
                'navTodayVisitors'   => $visitorCount,
            ]);
        });
    }
}
