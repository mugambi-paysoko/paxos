@extends('layouts.borrower')

@section('title', 'Fiat withdrawal details')

@section('page_actions')
    <a href="{{ route('borrower.fiat-withdrawals.index') }}" class="btn btn-outline-secondary rounded-3">
        <i class="ti ti-arrow-left me-1"></i> All withdrawals
    </a>
@endsection

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="fs-2 text-muted mb-1">Paxos transfer ID</p>
                    <p class="mb-0 fw-semibold font-monospace small text-break">{{ $fiatWithdrawal->paxos_transfer_id }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="fs-2 text-muted mb-1">Status</p>
                    <p class="mb-0 fw-medium" data-status-poll-target="transfer-status">{{ $fiatWithdrawal->status ?? 'PENDING' }}</p>
                    <p class="mb-0 mt-3 fs-2 text-muted">Transfer type</p>
                    <p class="mb-0 fw-medium">{{ $fiatWithdrawal->transfer_type ?? 'FIAT_WITHDRAWAL' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="fs-2 text-muted mb-1">Amount</p>
                    <p class="mb-0 fw-semibold text-primary">{{ $fiatWithdrawal->asset }} {{ $fiatWithdrawal->amount }}</p>
                    <p class="mb-0 mt-3 fs-2 text-muted">Reference ID</p>
                    <p class="mb-0">{{ $fiatWithdrawal->ref_id ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 p-md-5">
            <h5 class="fw-semibold mb-3">Withdrawal details</h5>
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
                            <td class="text-muted">Profile</td>
                            <td class="font-monospace small">{{ $fiatWithdrawal->profile->paxos_profile_id ?? 'N/A' }}</td>
                            <td class="text-muted">Fiat account</td>
                            <td class="font-monospace small">{{ $fiatWithdrawal->fiatAccount->paxos_fiat_account_id ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Memo</td>
                            <td>{{ $fiatWithdrawal->memo ?? 'N/A' }}</td>
                            <td class="text-muted">Created</td>
                            <td>{{ $fiatWithdrawal->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if ($fiatWithdrawal->metadata)
                <hr class="my-4 opacity-25">
                <h6 class="fw-semibold mb-3">Metadata</h6>
                <pre class="p-4 rounded-3 bg-body-secondary mb-0 small overflow-auto">{{ json_encode($fiatWithdrawal->metadata, JSON_PRETTY_PRINT) }}</pre>
            @endif
        </div>
    </div>

    @php
        $__fw = strtoupper((string) ($fiatWithdrawal->status ?? 'PENDING'));
    @endphp
    @if (! in_array($__fw, ['COMPLETED', 'FAILED', 'CANCELLED', 'REJECTED', 'SETTLED', 'RETURNED'], true))
        <x-status-poll
            mode="transfer"
            :url="route('borrower.status.fiat-withdrawal', $fiatWithdrawal)"
            :snapshot="['st' => $fiatWithdrawal->status ?? 'PENDING']"
            :interval="15000"
            :reloadOnChange="true"
        />
    @endif
@endsection
