@extends('layouts.app')

@section('title', 'Profile details')

@section('page_actions')
    <a href="{{ route('lender.profiles.fiat-deposit-instructions.create', $profile) }}" class="btn btn-primary rounded-3">
        <i class="ti ti-building-bank me-1"></i> Fiat deposit
    </a>
    <a href="{{ route('lender.profiles.deposit-addresses.create', $profile) }}" class="btn btn-outline-primary rounded-3">
        <i class="ti ti-qrcode me-1"></i> Crypto address
    </a>
    <a href="{{ route('lender.fiat-withdrawals.create') }}" class="btn btn-outline-primary rounded-3">
        <i class="ti ti-arrow-up-right me-1"></i> Fiat withdrawal
    </a>
    <a href="{{ route('lender.profiles.crypto-withdrawals.create', $profile) }}" class="btn btn-outline-primary rounded-3">
        <i class="ti ti-currency-bitcoin me-1"></i> Crypto withdrawal
    </a>
    <a href="{{ route('lender.profiles.index') }}" class="btn btn-outline-secondary rounded-3">Back</a>
@endsection

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="fs-2 text-muted mb-1">Paxos profile ID</p>
                    <p class="mb-0 fw-semibold font-monospace text-break">{{ $profile->paxos_profile_id ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="fs-2 text-muted mb-1">Account</p>
                    <p class="mb-0 fw-semibold">{{ $profile->account->type }}</p>
                    <p class="mb-0 mt-1 fs-3 text-muted">{{ $profile->account->description ?? 'No description' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="fs-2 text-muted mb-1">Identity</p>
                    <p class="mb-0 fw-semibold">{{ $profile->account->identity->first_name }} {{ $profile->account->identity->last_name }}</p>
                    <p class="mb-0 mt-1 fs-3 text-muted text-break">{{ $profile->account->identity->email }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                <h5 class="fw-semibold mb-0">Profile links</h5>
                <a href="{{ route('lender.accounts.show', $profile->account) }}" class="btn btn-sm btn-outline-primary rounded-3">View account</a>
            </div>
            <p class="mb-0 text-muted">This profile is used for all institution transfer flows. Use the action buttons above to issue instructions and initiate transfers.</p>
        </div>
    </div>

    @if ($profile->depositAddresses && $profile->depositAddresses->count() > 0)
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <h5 class="fw-semibold mb-0">Crypto deposit addresses</h5>
                    <a href="{{ route('lender.deposit-addresses.index') }}" class="btn btn-sm btn-outline-primary rounded-3">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Network</th>
                                <th>Address</th>
                                <th>Conversion target</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($profile->depositAddresses as $addr)
                                <tr>
                                    <td class="fw-semibold text-primary">{{ $addr->crypto_network }}</td>
                                    <td class="font-monospace small text-break">{{ $addr->address }}</td>
                                    <td>{{ $addr->conversion_target_asset ?? 'None' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if ($profile->fiatDepositInstructions && $profile->fiatDepositInstructions->count() > 0)
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body p-4 p-md-5">
                <h5 class="fw-semibold mb-3">Fiat deposit instructions</h5>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Network</th>
                                <th>Memo ID</th>
                                <th>Reference</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($profile->fiatDepositInstructions as $instruction)
                                <tr>
                                    <td class="fw-semibold text-primary">{{ $instruction->fiat_network }}</td>
                                    <td class="font-monospace">{{ $instruction->memo_id ?? 'N/A' }}</td>
                                    <td class="font-monospace">{{ $instruction->ref_id ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge rounded-pill {{ ($instruction->status ?? '') === 'VALID' ? 'text-bg-success' : 'text-bg-warning' }}">
                                            {{ $instruction->status ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('lender.fiat-deposit-instructions.show', $instruction) }}" class="btn btn-sm btn-outline-primary rounded-3">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 p-md-5">
            <h5 class="fw-semibold mb-3">Transfers</h5>

            @if (isset($transfersError))
                <div class="alert alert-danger border-0 rounded-3">{{ $transfersError }}</div>
            @endif

            @if (isset($transfers) && count($transfers) > 0)
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Transfer ID</th>
                                <th>Type</th>
                                <th>Asset</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Memo/Ref</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transfers as $transfer)
                                @php
                                    $status = (string) ($transfer['status'] ?? 'PENDING');
                                    $memoOrRef = $transfer['memo'] ?? $transfer['memo_id'] ?? $transfer['ref_id'] ?? 'N/A';
                                @endphp
                                <tr>
                                    <td class="font-monospace small">{{ $transfer['id'] ?? 'N/A' }}</td>
                                    <td>{{ $transfer['type'] ?? 'N/A' }}</td>
                                    <td>{{ $transfer['asset'] ?? 'N/A' }}</td>
                                    <td>{{ number_format((float) ($transfer['amount'] ?? 0), 2) }}</td>
                                    <td>
                                        <span class="badge rounded-pill {{ $status === 'COMPLETED' ? 'text-bg-success' : ($status === 'PENDING' ? 'text-bg-warning' : 'text-bg-secondary') }}">
                                            {{ $status }}
                                        </span>
                                    </td>
                                    <td class="font-monospace small">{{ $memoOrRef }}</td>
                                    <td class="text-muted">
                                        @if (isset($transfer['created_at']))
                                            {{ \Carbon\Carbon::parse($transfer['created_at'])->diffForHumans() }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5 rounded-3 bg-body-secondary">
                    <p class="text-muted mb-1">No transfers found for this profile.</p>
                    <p class="fs-3 text-muted mb-0">Transfers appear here after profile activity is initiated.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
