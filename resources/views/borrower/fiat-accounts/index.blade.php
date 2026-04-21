@extends('layouts.borrower')

@section('title', 'Fiat accounts')

@section('page_actions')
    <a href="{{ route('borrower.fiat-accounts.create') }}" class="btn btn-primary rounded-3">
        <i class="ti ti-plus me-1"></i> Create fiat account
    </a>
@endsection

@section('content')
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Account</th>
                        <th>Identity</th>
                        <th>Owner</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($fiatAccounts as $fiatAccount)
                        <tr>
                            <td class="fw-semibold text-primary font-monospace small">{{ $fiatAccount->paxos_fiat_account_id }}</td>
                            <td>{{ $fiatAccount->identity->first_name }} {{ $fiatAccount->identity->last_name }}</td>
                            <td>
                                @if ($fiatAccount->fiat_account_owner && isset($fiatAccount->fiat_account_owner['person_details']))
                                    {{ $fiatAccount->fiat_account_owner['person_details']['first_name'] ?? '' }}
                                    {{ $fiatAccount->fiat_account_owner['person_details']['last_name'] ?? '' }}
                                @elseif ($fiatAccount->fiat_account_owner && isset($fiatAccount->fiat_account_owner['institution_details']))
                                    {{ $fiatAccount->fiat_account_owner['institution_details']['name'] ?? 'N/A' }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                <span class="badge rounded-pill {{ $fiatAccount->status === 'APPROVED' ? 'text-bg-success' : 'text-bg-warning' }}">
                                    {{ $fiatAccount->status }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $fiatAccount->created_at->diffForHumans() }}</td>
                            <td class="text-end">
                                <a href="{{ route('borrower.fiat-accounts.show', $fiatAccount) }}" class="btn btn-sm btn-outline-primary rounded-3">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                No fiat accounts yet.
                                <a href="{{ route('borrower.fiat-accounts.create') }}" class="fw-semibold">Create one</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
