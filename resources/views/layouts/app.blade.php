<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Elasticshield') }}</title>

    <!-- Styles -->
    <link href="/css/app.css" rel="stylesheet">

    <!-- Scripts -->
    <script>
        window.Laravel = <?php echo json_encode([
            'csrfToken' => csrf_token(),
        ]); ?>
    </script>
</head>
<body>
    <div id="app">
        <nav class="nav has-shadow">
            <div class="container">
                <div class="nav-left">
                    <a href="{{ url('/elasticshield') }}" class="nav-item is-brand">
                        Elasticshield
                    </a>
                    @if (Auth::user())
                        <a href="{{ url('elasticshield/home') }}" class="nav-item is-tab {{Request::is('elasticshield/home') ? 'is-active' : ''}}">Home</a>
                    @endif
                </div>
                <div class="nav-center">
                    @if (Auth::guest())
                        <a href="{{ url('login') }}" class="nav-item is-tab ">Login</a>
                    @else
                        <a href="{{ url('logout') }}" class="nav-item is-tab ">Log out</a>
                    @endif
                </div>
            </div>
        </nav>

        @yield('content')
    </div>
    @include('elasticshield::footer')
    <!-- Scripts -->
    <script src="/js/app.js"></script>
</body>
</html>
