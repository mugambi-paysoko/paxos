@extends('layouts.borrower')

@section('title', 'Fiat withdrawals')

@section('page_actions')
    <a href="{{ route('borrower.fiat-withdrawals.create') }}" class="btn btn-primary rounded-3">
        <i class="ti ti-plus me-1"></i> New withdrawal
    </a>
@endsection

@section('content')
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Amount</th>
                        <th>Fiat account</th>
                        <th>Transfer type</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($fiatWithdrawals as $fiatWithdrawal)
                        <tr>
                            <td class="fw-semibold text-primary">{{ $fiatWithdrawal->asset }} {{ $fiatWithdrawal->amount }}</td>
                            <td class="font-monospace small">{{ $fiatWithdrawal->fiatAccount->paxos_fiat_account_id ?? 'Account' }}</td>
                            <td>{{ $fiatWithdrawal->transfer_type ?? 'FIAT_WITHDRAWAL' }}</td>
                            <td>
                                <span class="badge rounded-pill {{ ($fiatWithdrawal->status ?? '') === 'COMPLETED' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $fiatWithdrawal->status ?? 'PENDING' }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $fiatWithdrawal->created_at->diffForHumans() }}</td>
                            <td class="text-end">
                                <a href="{{ route('borrower.fiat-withdrawals.show', $fiatWithdrawal) }}" class="btn btn-sm btn-outline-primary rounded-3">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                No fiat withdrawals yet.
                                <a href="{{ route('borrower.fiat-withdrawals.create') }}" class="fw-semibold">Create one</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @php
        $__transferTerminal = ['COMPLETED', 'FAILED', 'CANCELLED', 'REJECTED', 'SETTLED', 'RETURNED'];
        $__hasActiveFiatWithdrawals = $fiatWithdrawals->isNotEmpty()
            && $fiatWithdrawals->contains(
                fn ($w) => ! in_array(strtoupper((string) ($w->status ?? 'PENDING')), $__transferTerminal, true)
            );
    @endphp
    @if ($__hasActiveFiatWithdrawals)
        <x-status-poll
            mode="digest"
            :url="route('borrower.status.fiat-withdrawals.digest')"
            :snapshot="['d' => $fiatWithdrawalsIndexDigest]"
            :interval="15000"
        />
    @endif
@endsection
