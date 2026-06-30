<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
 <!-- Styles -->
 <link rel="stylesheet" href="{{ asset('css/app.css') }}">
 <!-- Bootstrap Icons -->
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
 <!-- Scripts -->
<script src="{{ asset('js/app.js') }}" defer></script>

    <style>
        /* Pill-style nav row — links centered between the brand and the auth control */
        .topbar-nav-wrap {
            display: flex;
            align-items: center;
            width: 100%;
            gap: 8px;
        }
        .nav-pills-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
            flex: 1;
        }
        .nav-auth {
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }
        @media (max-width: 767.98px) {
            .topbar-nav-wrap {
                flex-direction: column;
                align-items: stretch;
            }
            .nav-auth {
                justify-content: center;
                margin-top: 8px;
            }
        }
        .nav-pill {
            display: inline-flex;
            align-items: center;
            padding: 8px 18px;
            border-radius: 999px;
            background: #f1f1ef;
            color: #16181d;
            font-weight: 600;
            font-size: 0.88rem;
            text-decoration: none;
            transition: background 0.15s ease, color 0.15s ease;
        }
        .nav-pill:hover {
            background: #e4e4e0;
            color: #16181d;
        }
        .nav-pill.nav-active {
            background: #16181d;
            color: #fff;
        }
        .nav-pill.nav-active:hover {
            background: #16181d;
            color: #fff;
        }
        .navbar-stats-capsule {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            flex-wrap: wrap;
            padding: 8px 0;
        }
        .nav-stat-badge {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 16px;
            font-size: 0.72rem;
            font-weight: 600;
            white-space: nowrap;
            transition: transform 0.15s ease;
        }
        .nav-stat-badge:hover {
            transform: scale(1.05);
        }
        .nav-stat-badge i {
            font-size: 0.78rem;
        }
        .nav-stat-badge.badge-datetime {
            background: #e8edfb;
            color: #3b5998;
        }
        .nav-stat-badge.badge-attendance {
            background: #e2f8eb;
            color: #16a34a;
        }
        .nav-stat-badge.badge-visitor {
            background: #fff0e0;
            color: #ea580c;
        }
        .nav-stat-divider {
            width: 1px;
            height: 18px;
            background: #d0d5e0;
        }
        @media (max-width: 767.98px) {
            .nav-stat-divider {
                display: none;
            }
        }
        /* Toast notification styles — iPhone-style notification banner (always available for client-side use) */
        .vms-toast {
            position: fixed;
            top: var(--toast-top, 16px);
            left: 50%;
            right: auto;
            transform: translateX(-50%);
            z-index: 99999;
            width: calc(100% - 32px);
            max-width: 400px;
            border-radius: 26px;
            box-shadow: 0 14px 34px rgba(0,0,0,0.22);
            animation: vmsSlideIn 0.55s cubic-bezier(0.18, 1.24, 0.4, 1) forwards;
        }
        .vms-toast.toast-success {
            background: #d7f24a;
        }
        .vms-toast.toast-error {
            background: #ffb4ab;
        }
        .vms-toast-body {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            color: #16181d;
        }
        .vms-toast-icon {
            font-size: 1.3rem;
            flex-shrink: 0;
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #16181d;
            color: #fff;
        }
        .vms-toast-text {
            flex: 1;
            font-weight: 600;
            font-size: 0.92rem;
            line-height: 1.4;
        }
        .vms-toast-close {
            background: none;
            border: none;
            color: rgba(22,24,29,0.45);
            font-size: 1.1rem;
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .vms-toast-close:hover {
            color: #16181d;
            background: rgba(22,24,29,0.1);
        }
        .vms-toast-progress {
            display: none;
        }
        .vms-toast.hide {
            animation: vmsSlideOut 0.35s cubic-bezier(0.4, 0, 0.6, 1) forwards;
        }
        @keyframes vmsSlideIn {
            from { transform: translateX(-50%) translateY(-150%); opacity: 0; }
            to { transform: translateX(-50%) translateY(0); opacity: 1; }
        }
        @keyframes vmsSlideOut {
            from { transform: translateX(-50%) translateY(0); opacity: 1; }
            to { transform: translateX(-50%) translateY(-150%); opacity: 0; }
        }
        html, body {
            height: 100%;
        }
        body {
            display: flex;
            flex-direction: column;
        }
        #app {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            flex: 1;
        }
        main {
            flex: 1;
        }
    </style>
</head>
<body>
    <div id="app">
        <div id="app-topbar-nav" class="container navbar-expand-md d-flex align-items-center flex-wrap py-2">
            <a class="navbar-brand">
                {{ config('app.name', 'Laravel') }}
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <?php
                $navLinks = [
                    ['name' => 'Dashboard', 'route' => 'dashboard.index'],
                    ['name' => 'Visitor', 'route' => 'visitor.index'],
                    ['name' => 'Attendance', 'route' => 'attendance.index'],
                    ['name' => 'Reports', 'route' => 'report.index'],
                ];
                ?>
                <div class="topbar-nav-wrap">
                    <div class="nav-pills-row">
                        @auth
                            @foreach($navLinks as $link)
                                <a class="nav-pill {{ request()->routeIs($link['route']) ? 'nav-active' : '' }}" href="{{ route($link['route']) }}">{{$link['name']}}</a>
                            @endforeach
                        @endauth
                    </div>

                    <div class="nav-auth">
                        @guest
                            @if (Route::has('login'))
                                <a class="nav-pill" href="{{ route('login') }}">{{ __('Login') }}</a>
                            @endif
                        @else
                            <div class="dropdown">
                                <a id="navbarDropdown" class="nav-pill dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </div>
                        @endguest
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats badges --}}
        @auth
        <div id="app-topbar-stats" class="container">
            <div class="navbar-stats-capsule">
                <span class="nav-stat-badge badge-datetime">
                    <i class="bi bi-calendar3"></i>
                    <span id="nav-date"></span>
                </span>
                <div class="nav-stat-divider"></div>
                <span class="nav-stat-badge badge-datetime">
                    <i class="bi bi-clock"></i>
                    <span id="nav-time"></span>
                </span>
                <div class="nav-stat-divider"></div>
                <span class="nav-stat-badge badge-attendance">
                    <i class="bi bi-person-check-fill"></i>
                    Attendance: {{ $navTodayAttendance ?? 0 }}
                </span>
                <div class="nav-stat-divider"></div>
                <span class="nav-stat-badge badge-visitor">
                    <i class="bi bi-people-fill"></i>
                    Visitors: {{ $navTodayVisitors ?? 0 }}
                </span>
            </div>
        </div>
        @endauth

        <main class="py-4">
            @yield('content')
        </main>

        <footer class="text-center py-3 text-muted" style="font-size: 0.85rem;">
            &copy; {{ date('Y') }} Lembah Sari Sdn Bhd. All rights reserved.
        </footer>
    </div>

    {{-- Keep toast notifications clear of the navbar so they never block its links --}}
    <script>
        function vmsSetToastTop() {
            const header = document.getElementById('app-topbar-stats') || document.getElementById('app-topbar-nav');
            const top = header ? header.getBoundingClientRect().bottom + 12 : 16;
            document.documentElement.style.setProperty('--toast-top', top + 'px');
        }
        vmsSetToastTop();
        window.addEventListener('resize', vmsSetToastTop);
    </script>

    {{-- Global Toast Notifications --}}
    @if(session('success') || session('error'))

    @if(session('success'))
    <div class="vms-toast toast-success" id="vms-toast">
        <div class="vms-toast-body">
            <div class="vms-toast-icon"><i class="bi bi-check-circle-fill"></i></div>
            <div class="vms-toast-text">{{ session('success') }}</div>
            <button class="vms-toast-close" onclick="dismissToast()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="vms-toast-progress"><div class="vms-toast-progress-bar"></div></div>
    </div>
    @endif

    @if(session('error'))
    <div class="vms-toast toast-error" id="vms-toast">
        <div class="vms-toast-body">
            <div class="vms-toast-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="vms-toast-text">{{ session('error') }}</div>
            <button class="vms-toast-close" onclick="dismissToast()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="vms-toast-progress"><div class="vms-toast-progress-bar"></div></div>
    </div>
    @endif

    <script>
        function dismissToast() {
            const t = document.getElementById('vms-toast');
            if (t) { t.classList.add('hide'); setTimeout(() => t.remove(), 350); }
        }
        setTimeout(dismissToast, 4000);
    </script>
    @endif

    {{-- Live Clock Script --}}
    @auth
    <script>
        function updateNavClock() {
            const now = new Date();
            const dateStr = now.toLocaleDateString('en-GB', {
                day: '2-digit', month: 'short', year: 'numeric'
            });
            const timeStr = now.toLocaleTimeString('en-GB', {
                hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
            }).toUpperCase();

            const dateEl = document.getElementById('nav-date');
            const timeEl = document.getElementById('nav-time');
            if (dateEl) dateEl.textContent = dateStr;
            if (timeEl) timeEl.textContent = timeStr;
        }
        updateNavClock();
        setInterval(updateNavClock, 1000);
    </script>
    @endauth
</body>
</html>
