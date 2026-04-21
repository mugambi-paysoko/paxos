<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme" data-layout="horizontal">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('modernize/assets/images/logos/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('modernize/assets/css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('modernize/assets/fonts/crypto-icons/cryptocoins.css') }}">
    <title>@yield('title', 'Institution') — {{ config('app.name', 'Paxos') }}</title>
    @stack('styles')
    <style>
        body.institution-portal {
            --bs-body-font-weight: 500;
            font-weight: 500;
            -webkit-font-smoothing: antialiased;
        }
        .institution-portal.institution-portal--crypto {
            background-color: var(--bs-body-bg);
            background-image:
                radial-gradient(1000px 520px at 8% -8%, rgba(var(--bs-primary-rgb), 0.14), transparent 55%),
                radial-gradient(880px 480px at 92% -4%, rgba(var(--bs-info-rgb), 0.12), transparent 50%),
                radial-gradient(700px 400px at 50% 108%, rgba(var(--bs-success-rgb), 0.08), transparent 45%);
            background-attachment: fixed;
        }
        .institution-portal .navbar-nav .nav-link,
        .institution-portal .form-label,
        .institution-portal .btn {
            font-weight: 500;
        }
        .institution-portal #main-wrapper .left-sidebar {
            display: none !important;
        }
        .institution-portal #main-wrapper .page-wrapper {
            margin-inline-start: 0 !important;
        }
        .institution-topnav {
            backdrop-filter: blur(10px);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(255, 255, 255, 0.82)) !important;
            border-bottom: 1px solid rgba(var(--bs-primary-rgb), 0.12) !important;
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.6) inset;
        }
        [data-bs-theme="dark"] .institution-topnav {
            background: linear-gradient(180deg, rgba(22, 28, 36, 0.96), rgba(22, 28, 36, 0.88)) !important;
            border-bottom-color: rgba(var(--bs-primary-rgb), 0.22) !important;
            box-shadow: none;
        }
        .institution-portal--crypto .borrower-nav-link--active {
            box-shadow: 0 0 0 1px rgba(var(--bs-primary-rgb), 0.35);
        }
        .institution-page-hero {
            border: 1px solid rgba(var(--bs-primary-rgb), 0.15);
            background: linear-gradient(120deg, rgba(var(--bs-primary-rgb), 0.12), rgba(var(--bs-info-rgb), 0.08));
        }
        [data-bs-theme="dark"] .institution-page-hero {
            border-color: rgba(var(--bs-primary-rgb), 0.28);
            background: linear-gradient(120deg, rgba(var(--bs-primary-rgb), 0.2), rgba(var(--bs-info-rgb), 0.12));
        }
    </style>
</head>
<body class="institution-portal institution-portal--crypto">
    <div id="main-wrapper">
        <div class="page-wrapper">
            <header class="topbar institution-topnav border-bottom shadow-sm">
                <nav class="navbar navbar-expand-xl container-fluid px-lg-4 py-2">
                    <a class="navbar-brand d-flex align-items-center py-1" href="{{ route('dashboard') }}">
                        <span class="round rounded text-bg-primary d-flex align-items-center justify-content-center flex-shrink-0" aria-hidden="true">
                            <i class="ti ti-cpu text-white fs-6"></i>
                        </span>
                        <span class="visually-hidden">{{ config('app.name') }} — Dashboard</span>
                    </a>
                    <button class="navbar-toggler border-0 shadow-none px-2" type="button" data-bs-toggle="collapse" data-bs-target="#institutionNavbar" aria-controls="institutionNavbar" aria-expanded="false" aria-label="Toggle navigation">
                        <i class="ti ti-menu-2 fs-7"></i>
                    </button>
                    <div class="collapse navbar-collapse" id="institutionNavbar">
                        <div class="navbar-nav-scroll mx-xl-4 my-3 my-xl-0 flex-grow-1">
                            @include('layouts.partials.institution-nav')
                        </div>
                        <ul class="navbar-nav flex-row ms-auto align-items-center gap-1">
                            <li class="nav-item nav-icon-hover-bg rounded-circle">
                                <a class="nav-link moon dark-layout" href="javascript:void(0)" aria-label="Dark mode">
                                    <i class="ti ti-moon moon fs-6"></i>
                                </a>
                                <a class="nav-link sun light-layout" href="javascript:void(0)" aria-label="Light mode">
                                    <i class="ti ti-sun sun fs-6"></i>
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link d-flex align-items-center gap-2 pe-2" href="javascript:void(0)" id="institutionUserMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <i class="ti ti-user fs-5"></i>
                                    </span>
                                    <span class="d-none d-md-flex flex-column text-start lh-sm">
                                        <span class="fs-3 fw-semibold text-body">{{ auth()->user()->name }}</span>
                                        <span class="fs-2 text-muted">{{ auth()->user()->roleLabel() }}</span>
                                    </span>
                                    <i class="ti ti-chevron-down fs-5 text-muted"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up shadow rounded-4" aria-labelledby="institutionUserMenu" style="min-width: 220px;">
                                    <div class="px-4 py-3 border-bottom">
                                        <p class="mb-0 fs-3 fw-semibold text-body">{{ auth()->user()->name }}</p>
                                        <p class="mb-0 fs-2 text-muted">{{ auth()->user()->email }}</p>
                                    </div>
                                    <form method="POST" action="{{ route('logout') }}" class="p-2">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger w-100 rounded-3">
                                            <i class="ti ti-logout me-2"></i> Log out
                                        </button>
                                    </form>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>

            <div class="body-wrapper">
                <div class="container-fluid px-lg-4 py-4">
                    <x-in-app-notifications />

                    @if (session('success'))
                        <div class="alert alert-success border-0 rounded-3 shadow-sm mb-4" role="alert">
                            <div class="d-flex align-items-start gap-2">
                                <i class="ti ti-circle-check fs-5 mt-1"></i>
                                <div>{{ session('success') }}</div>
                            </div>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-4" role="alert">
                            <div class="d-flex align-items-start gap-2">
                                <i class="ti ti-alert-circle fs-5 mt-1"></i>
                                <div>{{ session('error') }}</div>
                            </div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-4" role="alert">
                            <div class="d-flex align-items-start gap-2">
                                <i class="ti ti-alert-triangle fs-5 mt-1"></i>
                                <div>
                                    <p class="fw-semibold mb-2">Please fix the following:</p>
                                    <ul class="mb-0 ps-3">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    @hasSection('page_hero')
                        @yield('page_hero')
                    @else
                        <div class="card institution-page-hero shadow-sm rounded-4 overflow-hidden mb-4">
                            <div class="card-body px-4 py-4">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <p class="text-uppercase fs-2 text-muted fw-semibold mb-1" style="letter-spacing: 0.06em;">Institution Console</p>
                                        <h1 class="fw-semibold fs-7 mb-1 text-body">@yield('title')</h1>
                                        @hasSection('subtitle')
                                            <p class="mb-0 text-muted fs-3">@yield('subtitle')</p>
                                        @endif
                                    </div>
                                    <div class="col-auto d-flex flex-wrap gap-2 justify-content-end">
                                        @yield('page_actions')
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('modernize/assets/js/vendor.min.js') }}"></script>
    <script src="{{ asset('modernize/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('modernize/assets/libs/simplebar/dist/simplebar.min.js') }}"></script>
    <script src="{{ asset('modernize/assets/js/theme/app.horizontal.init.js') }}"></script>
    <script src="{{ asset('modernize/assets/js/theme/theme.js') }}"></script>
    <script src="{{ asset('modernize/assets/js/theme/app.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
