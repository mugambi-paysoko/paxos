@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <h1 class="h4 fw-semibold mb-1">Sign in to your account</h1>
                            <p class="text-muted mb-0">Continue to your Paxos dashboard.</p>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger rounded-3" role="alert">
                                <div class="fw-semibold mb-1">Login failed</div>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('login') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    required
                                    value="{{ old('email') }}"
                                    class="form-control rounded-3 @error('email') is-invalid @enderror"
                                    placeholder="name@example.com"
                                >
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    required
                                    class="form-control rounded-3 @error('password') is-invalid @enderror"
                                    placeholder="Your password"
                                >
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-check mb-4">
                                <input
                                    id="remember"
                                    name="remember"
                                    type="checkbox"
                                    value="1"
                                    class="form-check-input"
                                    {{ old('remember') ? 'checked' : '' }}
                                >
                                <label for="remember" class="form-check-label">Remember me</label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 rounded-3">
                                Sign in
                            </button>

                            <div class="text-center mt-4">
                                <a href="{{ route('register') }}" class="text-decoration-none fw-semibold">
                                    Don't have an account? Register
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
