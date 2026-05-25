<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Attendance;
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
        // Share today's attendance & visitor counts with the navbar
        View::composer('layouts.app', function ($view) {
            $today = Carbon::today();

            $view->with([
                'navTodayAttendance' => Attendance::whereDate('check_in_time', $today)->count(),
                'navTodayVisitors'   => Visit::whereDate('created_at', $today)->count(),
            ]);
        });
    }
}
