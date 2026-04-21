@extends('layouts.borrower')

@section('title', 'Account details')

@section('page_actions')
    <a href="{{ route('borrower.accounts.index') }}" class="btn btn-outline-secondary rounded-3">
        <i class="ti ti-arrow-left me-1"></i> Back
    </a>
@endsection

@section('content')
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 p-md-5">
            <div class="row g-4">
                <div class="col-lg-6">
                    <h5 class="fw-semibold mb-4">Account</h5>
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <p class="fs-2 text-muted mb-1">Account type</p>
                            <p class="mb-0 fw-medium">{{ $account->type }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="fs-2 text-muted mb-1">Description</p>
                            <p class="mb-0 fw-medium">{{ $account->description ?? 'N/A' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="fs-2 text-muted mb-1">Paxos account ID</p>
                            <p class="mb-0 fw-medium font-monospace small">{{ $account->paxos_account_id ?? 'N/A' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="fs-2 text-muted mb-1">Reference ID</p>
                            <p class="mb-0 fw-medium font-monospace small">{{ $account->ref_id }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <h5 class="fw-semibold mb-4">Identity</h5>
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <p class="fs-2 text-muted mb-1">Name</p>
                            <p class="mb-0 fw-medium">{{ $account->identity->first_name }} {{ $account->identity->last_name }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="fs-2 text-muted mb-1">Email</p>
                            <p class="mb-0 fw-medium">{{ $account->identity->email }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="fs-2 text-muted mb-1">Verification</p>
                            <span class="badge rounded-pill {{ $account->identity->id_verification_status === 'APPROVED' ? 'text-bg-success' : 'text-bg-warning' }}">
                                {{ $account->identity->id_verification_status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            @if ($account->profile)
                <hr class="my-4 opacity-25">
                <h5 class="fw-semibold mb-3">Profile</h5>
                <div class="row g-4 align-items-center">
                    <div class="col-md-6">
                        <p class="fs-2 text-muted mb-1">Paxos profile ID</p>
                        <p class="mb-0 fw-medium font-monospace">{{ $account->profile->paxos_profile_id ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <a href="{{ route('borrower.profiles.show', $account->profile) }}" class="btn btn-primary rounded-3">
                            View profile <i class="ti ti-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
