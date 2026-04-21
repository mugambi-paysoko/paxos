@extends('layouts.app')

@section('title', 'Crypto withdrawals')

@section('page_actions')
    <a href="{{ route('lender.profiles.index') }}" class="btn btn-outline-primary rounded-3">
        <i class="ti ti-wallet me-1"></i> Choose profile
    </a>
@endsection

@section('content')
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Amount</th>
                        <th>Network</th>
                        <th>Destination</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cryptoWithdrawals as $cryptoWithdrawal)
                        <tr>
                            <td class="fw-semibold text-primary">{{ $cryptoWithdrawal->asset }} {{ $cryptoWithdrawal->amount }}</td>
                            <td>{{ $cryptoWithdrawal->crypto_network }}</td>
                            <td class="font-monospace small text-break">{{ $cryptoWithdrawal->destination_address }}</td>
                            <td>
                                <span class="badge rounded-pill {{ ($cryptoWithdrawal->status ?? '') === 'COMPLETED' ? 'text-bg-success' : 'text-bg-warning' }}">
                                    {{ $cryptoWithdrawal->status ?? 'PENDING' }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $cryptoWithdrawal->created_at->diffForHumans() }}</td>
                            <td class="text-end">
                                <a href="{{ route('lender.crypto-withdrawals.show', $cryptoWithdrawal) }}" class="btn btn-sm btn-outline-primary rounded-3">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">No crypto withdrawals found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@php
    $__transferTerminal = ['COMPLETED', 'FAILED', 'CANCELLED', 'REJECTED', 'SETTLED', 'RETURNED'];
    $__hasActiveCryptoWithdrawals = $cryptoWithdrawals->isNotEmpty()
        && $cryptoWithdrawals->contains(
            fn ($w) => ! in_array(strtoupper((string) ($w->status ?? 'PENDING')), $__transferTerminal, true)
        );
@endphp
@if ($__hasActiveCryptoWithdrawals)
    <x-status-poll
        mode="digest"
        :url="route('lender.status.crypto-withdrawals.digest')"
        :snapshot="['d' => $cryptoWithdrawalsIndexDigest]"
        :interval="15000"
    />
@endif
