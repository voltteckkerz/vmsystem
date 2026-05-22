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
        .nav-active {
            background-color: rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            font-weight: 600;
            color: #005eeb !important;
        }
        /* Toast notification styles (always available for client-side use) */
        .vms-toast {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 99999;
            min-width: 340px;
            max-width: 440px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            animation: vmsSlideIn 0.4s cubic-bezier(0.22, 1, 0.36, 1);
            backdrop-filter: blur(8px);
        }
        .vms-toast.toast-success {
            background: linear-gradient(135deg, #16a34a, #15803d);
        }
        .vms-toast.toast-error {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
        }
        .vms-toast-body {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 20px;
            color: #fff;
        }
        .vms-toast-icon {
            font-size: 1.6rem;
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.2);
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
            color: rgba(255,255,255,0.7);
            font-size: 1.1rem;
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .vms-toast-close:hover {
            color: #fff;
            background: rgba(255,255,255,0.15);
        }
        .vms-toast-progress {
            height: 3px;
            background: rgba(255,255,255,0.3);
        }
        .vms-toast-progress-bar {
            height: 100%;
            background: rgba(255,255,255,0.7);
            animation: vmsProgress 4s linear forwards;
        }
        .vms-toast.hide {
            animation: vmsSlideOut 0.35s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }
        @keyframes vmsSlideIn {
            from { transform: translateX(120%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes vmsSlideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(120%); opacity: 0; }
        }
        @keyframes vmsProgress {
            from { width: 100%; }
            to { width: 0%; }
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">

                    </ul>

                    <!-- CENTER LINKS -->
                    <?php
                    $navLinks = [
                        ['name' => 'Dashboard', 'route' => 'dashboard.index'],
                        ['name' => 'Visitor', 'route' => 'visitor.index'],
                        ['name' => 'Attendance', 'route' => 'attendance.index'],
                        ['name' => 'Reports', 'route' => 'report.index'],
                    ];
                    ?>
                    <!-- *** START OF CENTER LINKS *** -->
                    @auth
                    <ul class="navbar-nav mx-auto">  {{-- Use mx-auto to center it --}}
                        @foreach($navLinks as $link)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs($link['route']) ? 'nav-active' : '' }}" href="{{ route($link['route']) }}">{{$link['name']}}</a>
                        </li>
                        @endforeach

                        {{-- Admin-only Import link --}}
                        @if(auth()->user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('import.index') ? 'nav-active' : '' }}" href="{{ route('import.index') }}">Import</a>
                        </li>
                        @endif
                    </ul>
                    @endauth
                    <!-- *** END OF CENTER LINKS *** -->



                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif



                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
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
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>

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
</body>
</html>
