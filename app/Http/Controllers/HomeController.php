<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $liveVisits = Visit::with(['employee', 'visitors', 'visitors.company'])
            ->where('status', 'active')
            ->get();

        return view('dashboard', compact('liveVisits'));
    }
}
