@extends('layouts.app')

@section('title', 'Fiat account details')

@section('page_actions')
    <form action="{{ route('lender.fiat-accounts.refresh', $fiatAccount) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-success rounded-3">
            <i class="ti ti-refresh me-1"></i> Refresh status
        </button>
    </form>
    <a href="{{ route('lender.fiat-accounts.index') }}" class="btn btn-outline-secondary rounded-3">
        <i class="ti ti-arrow-left me-1"></i> All accounts
    </a>
@endsection

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="fs-2 text-muted mb-1">Paxos fiat account ID</p>
                    <p class="mb-0 fw-semibold font-monospace small text-break">{{ $fiatAccount->paxos_fiat_account_id }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="fs-2 text-muted mb-1">Status</p>
                    <span class="badge rounded-pill {{ $fiatAccount->status === 'APPROVED' ? 'text-bg-success' : 'text-bg-warning' }}">{{ $fiatAccount->status }}</span>
                    <p class="mb-0 mt-3 fs-2 text-muted">Created</p>
                    <p class="mb-0 fw-medium">{{ $fiatAccount->created_at->format('Y-m-d H:i:s') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="fs-2 text-muted mb-1">Identity</p>
                    <p class="mb-0 fw-semibold">{{ $fiatAccount->identity->first_name }} {{ $fiatAccount->identity->last_name }}</p>
                    @if ($fiatAccount->paxos_identity_id)
                        <p class="mb-0 mt-3 fs-2 text-muted">Paxos identity ID</p>
                        <p class="mb-0 font-monospace small text-break">{{ $fiatAccount->paxos_identity_id }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-4 p-md-5">
            <h5 class="fw-semibold mb-3">Account metadata</h5>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Field</th>
                            <th>Value</th>
                            <th>Field</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-muted">Paxos account ID</td>
                            <td class="font-monospace small">{{ $fiatAccount->paxos_account_id ?? 'N/A' }}</td>
                            <td class="text-muted">Paxos created at</td>
                            <td>{{ $fiatAccount->paxos_created_at ? $fiatAccount->paxos_created_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($fiatAccount->fiat_account_owner)
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body p-4 p-md-5">
                <h5 class="fw-semibold mb-3">Account owner</h5>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($fiatAccount->fiat_account_owner['person_details']))
                                <tr>
                                    <td class="text-muted">Person</td>
                                    <td>
                                        {{ $fiatAccount->fiat_account_owner['person_details']['first_name'] ?? 'N/A' }}
                                        {{ $fiatAccount->fiat_account_owner['person_details']['last_name'] ?? '' }}
                                    </td>
                                </tr>
                            @endif
                            @if (isset($fiatAccount->fiat_account_owner['institution_details']))
                                <tr>
                                    <td class="text-muted">Institution</td>
                                    <td>{{ $fiatAccount->fiat_account_owner['institution_details']['name'] ?? 'N/A' }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if ($fiatAccount->fiat_network_instructions)
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4 p-md-5">
                <h5 class="fw-semibold mb-3">Network instructions</h5>
                <pre class="p-4 rounded-3 bg-body-secondary mb-0 small overflow-auto">{{ json_encode($fiatAccount->fiat_network_instructions, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    @endif
@endsection
