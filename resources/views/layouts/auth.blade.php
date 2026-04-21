<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('modernize/assets/images/logos/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('modernize/assets/css/styles.css') }}">
    <title>@yield('title', 'Auth') — {{ config('app.name', 'Paxos') }}</title>
    <style>
        body.auth-portal {
            min-height: 100vh;
            background-color: var(--bs-body-bg);
            background-image:
                radial-gradient(900px 460px at 5% -10%, rgba(var(--bs-primary-rgb), 0.14), transparent 55%),
                radial-gradient(760px 420px at 95% -5%, rgba(var(--bs-info-rgb), 0.1), transparent 50%);
            background-attachment: fixed;
        }
    </style>
    @stack('styles')
</head>
<body class="auth-portal">
    <main class="min-vh-100 d-flex align-items-center">
        @yield('content')
    </main>

    <script src="{{ asset('modernize/assets/js/vendor.min.js') }}"></script>
    <script src="{{ asset('modernize/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('modernize/assets/js/theme/theme.js') }}"></script>
    @stack('scripts')
</body>
</html>
