@extends('layouts.auth')

@section('title', 'Register')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-9 col-lg-6">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <h1 class="h4 fw-semibold mb-1">Create your account</h1>
                            <p class="text-muted mb-0">Start using Paxos workflows in minutes.</p>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger rounded-3" role="alert">
                                <div class="fw-semibold mb-1">Registration failed</div>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('register') }}" method="POST">
                            @csrf

                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="name" class="form-label">Name</label>
                                    <input
                                        id="name"
                                        name="name"
                                        type="text"
                                        required
                                        value="{{ old('name') }}"
                                        class="form-control rounded-3 @error('name') is-invalid @enderror"
                                        placeholder="Your name"
                                    >
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
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

                                <div class="col-md-6">
                                    <label for="password" class="form-label">Password</label>
                                    <input
                                        id="password"
                                        name="password"
                                        type="password"
                                        required
                                        class="form-control rounded-3 @error('password') is-invalid @enderror"
                                        placeholder="Password"
                                    >
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label">Confirm password</label>
                                    <input
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        type="password"
                                        required
                                        class="form-control rounded-3"
                                        placeholder="Confirm password"
                                    >
                                </div>

                                <div class="col-12">
                                    <label for="role" class="form-label">Role</label>
                                    <select
                                        id="role"
                                        name="role"
                                        required
                                        class="form-select rounded-3 @error('role') is-invalid @enderror"
                                    >
                                        <option value="institution" {{ old('role') == 'institution' ? 'selected' : '' }}>Institution</option>
                                        <option value="individual" {{ old('role') == 'individual' ? 'selected' : '' }}>Individual</option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 rounded-3 mt-4">
                                Register
                            </button>

                            <div class="text-center mt-4">
                                <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">
                                    Already have an account? Login
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
