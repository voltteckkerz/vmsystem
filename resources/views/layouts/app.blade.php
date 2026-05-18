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
 <!-- Scripts -->
<script src="{{ asset('js/app.js') }}" defer></script>

    <style>
        .nav-active {
            background-color: rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            font-weight: 600;
            color: #005eeb !important;
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
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        @if(session('success'))
        <div class="toast align-items-center text-bg-success border-0 show" role="alert" id="toast-success">
            <div class="d-flex">
                <div class="toast-body fw-bold">✅ {{ session('success') }}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
        @endif
        @if(session('error'))
        <div class="toast align-items-center text-bg-danger border-0 show" role="alert" id="toast-error">
            <div class="d-flex">
                <div class="toast-body fw-bold">❌ {{ session('error') }}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
        @endif
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.toast.show').forEach(function(el) {
                setTimeout(function() { el.classList.remove('show'); }, 4000);
            });
        });
    </script>
    @endif
</body>
</html>
