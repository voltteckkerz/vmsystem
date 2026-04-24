<!DOCTYPE html>
<html>

<head>
    <title>Home - VMS</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>

<body class="bg-light">
    <div class="row g-0">
        <div class="col-12 g-0 p-2 ps-4 pe-4 m-0">
            <nav class="navbar navbar-light mt-2 b-2 p-0">
                <div class="container-fluid p-0">
                    <a class="navbar-brand" href="/"><b>VMS</b></a>
                </div>
                <div class="welcome-text">
                    <span class="text-secondary">Welcome to the Visitor Management System</span>
                </div>
            </nav>
        </div>
        <div class="col-7 text-center g-0"
            style="background: white; border-radius: 10px; margin: 20px auto; padding: 20px;">
            <img style="max-height: 320px;" src="{{ asset('images/myimage.jpg') }}" title="Home Image">

            <div class="mt-3">
                @guest
                    <div class="mb-2">
                        <a class="btn btn-primary w-50" href="/login"><b>Login</b></a>
                    </div>
                    <div>
                        <a class="btn btn-secondary w-50" href="/register"><b>Register</b></a>
                    </div>
                @else
                    <div class="mb-2">
                        <a class="btn btn-success w-50" href="/dashboard"><b>Go to Dashboard</b></a>
                    </div>
                    <div>
                        <a class="btn btn-danger w-50" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <b>Logout</b>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                @endguest
            </div>
        </div>
        <script src="{{ asset('js/app.js') }}"></script>
</body>

</html>