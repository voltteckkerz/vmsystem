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
            position: relative;
        }
        /* Sliding dark pill that glides between nav links on click */
        .nav-pill-slider {
            position: absolute;
            background: #16181d;
            border-radius: 999px;
            transition: left 0.38s cubic-bezier(0.34, 1.4, 0.64, 1),
                        top 0.38s cubic-bezier(0.34, 1.4, 0.64, 1),
                        width 0.38s cubic-bezier(0.34, 1.4, 0.64, 1);
            z-index: 0;
            pointer-events: none;
            opacity: 0;
        }
        /* Liquid blob: squash & stretch while travelling, jiggle on landing */
        .nav-pill-slider.sliding {
            animation: vmsBlob 0.5s cubic-bezier(0.36, 0.07, 0.19, 0.97);
        }
        @keyframes vmsBlob {
            0%   { transform: scale(1, 1); }
            30%  { transform: scaleX(1.35) scaleY(0.78); border-radius: 999px; }
            60%  { transform: scaleX(0.88) scaleY(1.14); }
            80%  { transform: scaleX(1.06) scaleY(0.96); }
            100% { transform: scale(1, 1); }
        }
        .nav-pills-row .nav-pill { position: relative; z-index: 1; transition: background 0.15s ease, color 0.28s ease; }
        /* Once the slider takes over, the active pill itself goes transparent */
        .nav-pills-row.slider-on .nav-pill.nav-active,
        .nav-pills-row.slider-on .nav-pill.nav-active:hover {
            background: transparent;
            color: #fff;
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

        /* ── Mobile burger + full-page slide drawer ── */
        .vms-burger {
            display: none;
            width: 42px;
            height: 42px;
            border: none;
            border-radius: 12px;
            background: #f1f1ef;
            color: #16181d;
            font-size: 1.35rem;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            flex-shrink: 0;
            cursor: pointer;
            transition: background 0.15s;
        }
        .vms-burger:active { background: #e4e4e0; }
        @media (max-width: 767.98px) {
            .vms-burger { display: flex; }
            /* Hide the inline pill nav on phones — the drawer replaces it */
            #navbarSupportedContent { display: none !important; }
        }
        .vms-drawer {
            position: fixed;
            inset: 0;
            z-index: 1000002;
            background: rgba(255,255,255,0.97);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            display: flex;
            flex-direction: column;
            padding: 20px 22px;
            transform: translateX(-100%);
            transition: transform 0.34s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
        }
        .vms-drawer.open { transform: translateX(0); }
        .vms-drawer-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
        }
        .vms-drawer-brand {
            font-weight: 800;
            font-size: 1.1rem;
            color: #16181d;
            letter-spacing: 0.02em;
        }
        .vms-drawer-close {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 50%;
            background: #f1f1ef;
            color: #16181d;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .vms-drawer-links {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .vms-drawer-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 15px 18px;
            border-radius: 16px;
            background: #f7f7f5;
            color: #16181d;
            font-weight: 700;
            font-size: 1.02rem;
            text-decoration: none;
            /* Staggered slide-in when the drawer opens */
            opacity: 0;
            transform: translateX(-18px);
            transition: opacity 0.28s ease, transform 0.28s ease, background 0.15s;
        }
        .vms-drawer-link i { font-size: 1.15rem; }
        .vms-drawer-link.active { background: #16181d; color: #fff; }
        .vms-drawer.open .vms-drawer-link { opacity: 1; transform: translateX(0); }
        .vms-drawer.open .vms-drawer-link:nth-child(1) { transition-delay: 0.08s; }
        .vms-drawer.open .vms-drawer-link:nth-child(2) { transition-delay: 0.14s; }
        .vms-drawer.open .vms-drawer-link:nth-child(3) { transition-delay: 0.20s; }
        .vms-drawer.open .vms-drawer-link:nth-child(4) { transition-delay: 0.26s; }
        .vms-drawer-foot {
            margin-top: auto;
            padding-top: 18px;
            border-top: 1px solid #ececea;
        }
        .vms-drawer-foot .vms-drawer-link { opacity: 1; transform: none; }
        .vms-drawer-foot .vms-drawer-link.logout { background: #fee2e2; color: #dc2626; margin-top: 10px; }
        .vms-drawer-user {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            color: #16181d;
            padding: 4px 6px;
        }
        .vms-drawer-user i { font-size: 1.4rem; color: #6b6f78; }
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
        /* ── Page transition: fake skeleton layout ── */
        .vms-loader {
            position: fixed;
            inset: 0;
            z-index: 999999;
            /* Opaque fallback so the page underneath never shows through
               while the photo is still decoding on a fresh paint */
            background-color: #e9ebee;
            /* Same background as the body so the swap is seamless.
               No background-attachment:fixed here — a fixed-attachment bg inside a
               position:fixed layer makes some browsers flash black while compositing.
               The element spans the exact viewport, so cover/center aligns anyway. */
            background-image: url('{{ asset(str_replace(' ', '%20', 'images/login background.jpg')) }}?v={{ filemtime(public_path('images/login background.jpg')) }}');
            background-size: cover;
            background-position: center;
            overflow: hidden;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.28s ease;
            will-change: opacity;
        }
        .vms-loader.show {
            opacity: 1;
            pointer-events: auto;
        }
        .vms-skel-wrap {
            max-width: 1140px;
            margin: 0 auto;
            /* Starts below the real navbar (set by script, same as toasts) */
            padding: var(--toast-top, 130px) 12px 0;
        }
        /* Shimmering placeholder bar */
        .vms-skel {
            background: linear-gradient(90deg, #e8e8e5 25%, #f7f7f5 37%, #e8e8e5 63%);
            background-size: 400% 100%;
            animation: vmsShimmer 1.3s ease infinite;
            border-radius: 6px;
        }
        @keyframes vmsShimmer {
            0%   { background-position: 100% 0; }
            100% { background-position: 0 0; }
        }
        /* Fake content cards */
        .vms-skel-card {
            background: rgba(255,255,255,0.90);
            border: 1px solid rgba(255,255,255,0.65);
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(16,24,40,0.12);
            padding: 20px 24px;
            margin-top: 20px;
        }
        .vms-skel-title { width: 200px; height: 16px; margin-bottom: 18px; }
        .vms-skel-row   { height: 14px; margin-bottom: 13px; }
        .vms-skel-row:nth-child(3) { width: 92%; }
        .vms-skel-row:nth-child(4) { width: 97%; }
        .vms-skel-row:nth-child(5) { width: 88%; }
        .vms-skel-row:nth-child(6) { width: 95%; }
        .vms-skel-row:last-child   { margin-bottom: 0; }
        .vms-skel-grid { display: flex; gap: 16px; }
        .vms-skel-grid .vms-skel-card { flex: 1; }
        .vms-skel-block { height: 130px; border-radius: 12px; }
        @media (max-width: 767.98px) {
            .vms-skel-grid { flex-direction: column; }
        }
        /* Toast notification styles — iPhone-style notification banner (always available for client-side use) */
        .vms-toast {
            position: fixed;
            top: 16px;
            left: 50%;
            right: auto;
            transform: translateX(-50%);
            z-index: 1000001;
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
        /* Reserve scrollbar space so the layout doesn't shift between short and tall pages */
        html {
            scrollbar-gutter: stable;
        }
        body {
            background-image: url('{{ asset(str_replace(' ', '%20', 'images/login background.jpg')) }}?v={{ filemtime(public_path('images/login background.jpg')) }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        /* #app alone handles the sticky footer (min-height + main flex:1).
           Don't make body a flex parent of #app: a flex:1 #app can collapse
           to one viewport tall, which caps position:sticky at one screen. */
        #app {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        main {
            flex: 1;
        }

        /* ═══════════ GLOBAL DESIGN SYSTEM ═══════════
           Glassy cards over the photo background, dark charcoal primary,
           pill shapes everywhere. Pure restyling — no markup/JS depends on it. */

        /* — Top bar: floating glass strip — */
        /* Kept above the page-transition skeleton so the real navbar never gets faked over */
        #app-topbar-nav, #app-topbar-stats {
            position: relative;
            z-index: 1000000;
        }
        /* Whole top bar (nav + date/stats strip) follows the page while scrolling.
           flow-root keeps the strips' own margins inside the sticky box. */
        #app-topbar-sticky {
            position: sticky;
            top: 0;
            z-index: 1000000;
            display: flow-root;
        }
        #app-topbar-nav {
            background: rgba(255,255,255,0.86);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255,255,255,0.65);
            border-radius: 20px;
            box-shadow: 0 8px 28px rgba(16,24,40,0.10);
            margin-top: 12px;
            padding-left: 20px !important;
            padding-right: 20px !important;
        }
        #app-topbar-nav .navbar-brand {
            font-weight: 800;
            color: #16181d;
            letter-spacing: 0.02em;
        }
        #app-topbar-stats {
            margin-top: 8px;
        }
        #app-topbar-stats .navbar-stats-capsule {
            background: rgba(255,255,255,0.78);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.6);
            border-radius: 999px;
            box-shadow: 0 4px 18px rgba(16,24,40,0.08);
            padding: 8px 18px;
            width: fit-content;
            margin: 0 auto;
        }

        /* — Cards: frosted glass — */
        .card {
            background: rgba(255,255,255,0.90);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.65) !important;
            border-radius: 18px !important;
            box-shadow: 0 8px 32px rgba(16,24,40,0.12) !important;
            overflow: hidden;
        }
        .card-header {
            background: transparent !important;
            border-bottom: none !important;
        }

        /* — Tables — */
        .table thead.table-dark th,
        .table thead.table-light th {
            background: #16181d;
            color: #fff;
            border-color: #2a2d36;
            font-size: 0.76rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding-top: 10px;
            padding-bottom: 10px;
        }
        .table td { vertical-align: middle; }
        .table { --bs-table-striped-bg: rgba(22,24,29,0.03); }

        /* — Buttons: charcoal primary, pill radius — */
        .btn { border-radius: 10px; font-weight: 600; }
        .btn-primary {
            --bs-btn-bg: #16181d;
            --bs-btn-border-color: #16181d;
            --bs-btn-hover-bg: #2a2d36;
            --bs-btn-hover-border-color: #2a2d36;
            --bs-btn-active-bg: #000;
            --bs-btn-active-border-color: #000;
            --bs-btn-disabled-bg: #6b6f78;
            --bs-btn-disabled-border-color: #6b6f78;
        }
        .btn-outline-primary {
            --bs-btn-color: #16181d;
            --bs-btn-border-color: #16181d;
            --bs-btn-hover-bg: #16181d;
            --bs-btn-hover-border-color: #16181d;
            --bs-btn-active-bg: #16181d;
            --bs-btn-active-border-color: #16181d;
        }

        /* — Forms: match login page style — */
        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid #e6e7eb;
            background-color: #fcfcfd;
        }
        .form-control:focus, .form-select:focus {
            border-color: #16181d;
            box-shadow: 0 0 0 3px rgba(22,24,29,0.08);
            background-color: #fff;
        }

        /* — Modals — */
        .modal-content {
            border: none !important;
            border-radius: 20px !important;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(0,0,0,0.28) !important;
        }
        .modal-header { border-bottom: none; }
        .modal-footer { border-top: none; }
        .modal-backdrop.show { opacity: 0.35; }

        /* — Badges: pill — */
        .badge { border-radius: 999px; font-weight: 600; }

        /* — Confirmation modal redesign: icon-first, no colored header bars — */
        .vms-modal .modal-content {
            border-radius: 24px !important;
            padding: 6px;
        }
        /* Smooth scale-up-from-center popup instead of Bootstrap's default slide-down */
        .vms-modal.fade .modal-dialog {
            transform: scale(0.9);
            opacity: 0;
            transition: transform 0.25s cubic-bezier(0.18, 1.24, 0.4, 1), opacity 0.2s ease-out;
        }
        .vms-modal.show .modal-dialog {
            transform: scale(1);
            opacity: 1;
        }
        .vms-modal .vms-modal-close {
            position: absolute;
            top: 16px;
            right: 16px;
            z-index: 5;
            width: 34px;
            height: 34px;
            border: none;
            border-radius: 50%;
            background: #f1f1ef;
            color: #6b6f78;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.15s;
        }
        .vms-modal .vms-modal-close:hover { background: #e4e4e0; color: #16181d; }
        .vms-modal-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin: 6px auto 14px;
        }
        .vms-modal-icon.icon-success { background: #e2f8eb; color: #16a34a; }
        .vms-modal-icon.icon-danger  { background: #fee2e2; color: #dc2626; }
        .vms-modal-icon.icon-warning { background: #fff0e0; color: #ea580c; }
        .vms-modal-icon.icon-dark    { background: #f1f1ef; color: #16181d; }
        .vms-modal-title {
            font-weight: 800;
            font-size: 1.2rem;
            color: #16181d;
            margin-bottom: 4px;
            text-align: center;
        }
        .vms-modal-sub { color: #6b6f78; font-size: 0.9rem; }
        .vms-modal .btn {
            border-radius: 999px;
            font-weight: 700;
            padding: 9px 26px;
        }
        .vms-modal .btn-secondary {
            --bs-btn-bg: #f1f1ef;
            --bs-btn-border-color: #f1f1ef;
            --bs-btn-color: #16181d;
            --bs-btn-hover-bg: #e4e4e0;
            --bs-btn-hover-border-color: #e4e4e0;
            --bs-btn-hover-color: #16181d;
            --bs-btn-active-bg: #dcdcd8;
            --bs-btn-active-border-color: #dcdcd8;
            --bs-btn-active-color: #16181d;
        }
        .vms-modal .modal-footer { padding-bottom: 22px; gap: 8px; }

        /* — Scrollbars (WebKit) — */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(22,24,29,0.22); border-radius: 999px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(22,24,29,0.38); }

        /* — Footer — */
        #app > footer {
            color: rgba(22,24,29,0.6) !important;
        }
    </style>
</head>
<body>
    {{-- Page transition: fake skeleton layout — visible during first paint, fades out when ready --}}
    <div class="vms-loader show" id="vms-loader" aria-hidden="true">
        <div class="vms-skel-wrap">
            <div class="vms-skel-card" style="margin-top:0;">
                <div class="vms-skel vms-skel-title"></div>
                <div class="vms-skel vms-skel-row"></div>
                <div class="vms-skel vms-skel-row"></div>
                <div class="vms-skel vms-skel-row"></div>
                <div class="vms-skel vms-skel-row"></div>
                <div class="vms-skel vms-skel-row"></div>
            </div>
            <div class="vms-skel-grid">
                <div class="vms-skel-card">
                    <div class="vms-skel vms-skel-title" style="width:150px;"></div>
                    <div class="vms-skel vms-skel-block"></div>
                </div>
                <div class="vms-skel-card">
                    <div class="vms-skel vms-skel-title" style="width:150px;"></div>
                    <div class="vms-skel vms-skel-block"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="app">
        <?php
        $navLinks = [
            ['name' => 'Dashboard',  'route' => 'dashboard.index',  'icon' => 'bi-speedometer2'],
            ['name' => 'Visitor',    'route' => 'visitor.index',    'icon' => 'bi-people-fill'],
            ['name' => 'Attendance', 'route' => 'attendance.index', 'icon' => 'bi-person-check-fill'],
            ['name' => 'Reports',    'route' => 'report.index',     'icon' => 'bi-file-earmark-text-fill'],
        ];
        ?>
        <div id="app-topbar-sticky">
        <div id="app-topbar-nav" class="container navbar-expand-md d-flex align-items-center flex-wrap py-2">
            <button class="vms-burger" id="vms-burger" type="button" aria-label="Open menu">
                <i class="bi bi-list"></i>
            </button>

            <a class="navbar-brand">
                {{ config('app.name', 'Laravel') }}
            </a>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
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
        </div>{{-- end app-topbar-sticky --}}

        {{-- Mobile slide-in navigation drawer --}}
        <div class="vms-drawer" id="vms-drawer" aria-hidden="true">
            <div class="vms-drawer-head">
                <span class="vms-drawer-brand">{{ config('app.name', 'Laravel') }}</span>
                <button class="vms-drawer-close" id="vms-drawer-close" type="button" aria-label="Close menu">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <nav class="vms-drawer-links">
                @auth
                    @foreach($navLinks as $link)
                        <a class="vms-drawer-link {{ request()->routeIs($link['route']) ? 'active' : '' }}" href="{{ route($link['route']) }}">
                            <i class="bi {{ $link['icon'] }}"></i>{{ $link['name'] }}
                        </a>
                    @endforeach
                @endauth
                @guest
                    @if (Route::has('login'))
                        <a class="vms-drawer-link" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right"></i>{{ __('Login') }}
                        </a>
                    @endif
                @endguest
            </nav>
            @auth
            <div class="vms-drawer-foot">
                <div class="vms-drawer-user"><i class="bi bi-person-circle"></i>{{ Auth::user()->name }}</div>
                <a class="vms-drawer-link logout" href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();">
                    <i class="bi bi-box-arrow-left"></i>{{ __('Logout') }}
                </a>
                <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
            @endauth
        </div>

        <main class="py-4">
            @yield('content')
        </main>

        <footer class="text-center py-3 text-muted" style="font-size: 0.85rem;">
            &copy; {{ date('Y') }} Lembah Sari Sdn Bhd. All rights reserved.
        </footer>
    </div>

    {{-- Sets --toast-top for the page-transition skeleton layout (kept below the navbar) --}}
    <script>
        function vmsSetToastTop() {
            const header = document.getElementById('app-topbar-stats') || document.getElementById('app-topbar-nav');
            const top = header ? header.getBoundingClientRect().bottom + 12 : 16;
            document.documentElement.style.setProperty('--toast-top', top + 'px');
        }
        vmsSetToastTop();
        window.addEventListener('resize', vmsSetToastTop);

        // ── Page loader: fade out once the page has rendered ──
        (function() {
            const loader = document.getElementById('vms-loader');
            if (!loader) return;
            // Small minimum display so it reads as a smooth transition, not a flicker
            setTimeout(() => loader.classList.remove('show'), 350);
            // Coming back via the browser back/forward cache
            window.addEventListener('pageshow', function(e) {
                if (e.persisted) loader.classList.remove('show');
            });
        })();
        function vmsShowLoader() {
            const loader = document.getElementById('vms-loader');
            if (loader) loader.classList.add('show');
        }

        // ── Mobile navigation drawer ──
        (function() {
            const drawer = document.getElementById('vms-drawer');
            const burger = document.getElementById('vms-burger');
            if (!drawer || !burger) return;

            function openDrawer() {
                drawer.classList.add('open');
                drawer.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }
            function closeDrawer() {
                drawer.classList.remove('open');
                drawer.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }

            burger.addEventListener('click', openDrawer);
            document.getElementById('vms-drawer-close').addEventListener('click', closeDrawer);

            drawer.querySelectorAll('a.vms-drawer-link').forEach(link => {
                link.addEventListener('click', function() {
                    if (!this.classList.contains('logout')) vmsShowLoader();
                    closeDrawer();
                });
            });
        })();
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

    {{-- Sliding nav pill animation --}}
    @auth
    <script>
        (function() {
            const row = document.querySelector('.nav-pills-row');
            if (!row) return;
            const pills = row.querySelectorAll('.nav-pill');
            if (!pills.length) return;

            const slider = document.createElement('div');
            slider.className = 'nav-pill-slider';
            row.prepend(slider);
            row.classList.add('slider-on');

            function moveTo(el, instant) {
                if (instant) slider.style.transition = 'none';
                slider.style.width  = el.offsetWidth  + 'px';
                slider.style.height = el.offsetHeight + 'px';
                slider.style.left   = el.offsetLeft   + 'px';
                slider.style.top    = el.offsetTop    + 'px';
                slider.style.opacity = '1';
                if (instant) {
                    requestAnimationFrame(() => { slider.style.transition = ''; });
                } else {
                    // Liquid squash & stretch while travelling
                    slider.classList.remove('sliding');
                    void slider.offsetWidth; // restart the animation
                    slider.classList.add('sliding');
                    setTimeout(() => slider.classList.remove('sliding'), 520);
                }
            }

            const active = row.querySelector('.nav-pill.nav-active');
            if (active) moveTo(active, true);

            pills.forEach(pill => {
                pill.addEventListener('click', function(e) {
                    if (this.classList.contains('nav-active')) { e.preventDefault(); return; }
                    e.preventDefault();
                    pills.forEach(p => p.classList.remove('nav-active'));
                    this.classList.add('nav-active');
                    moveTo(this);
                    const href = this.href;
                    // Fade the skeleton in as the blob lands, then navigate
                    setTimeout(() => vmsShowLoader(), 260);
                    setTimeout(() => { window.location.href = href; }, 480);
                });
            });

            // Re-align after fonts/layout settle and on resize
            function realign() {
                const cur = row.querySelector('.nav-pill.nav-active');
                if (cur) moveTo(cur, true);
            }
            window.addEventListener('load', realign);
            window.addEventListener('resize', realign);
        })();
    </script>
    @endauth

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
