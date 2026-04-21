@extends('layouts.borrower')

@section('title', 'Crypto deposit addresses')

@section('content')
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Network</th>
                        <th>Address</th>
                        <th>Conversion target</th>
                        <th>Profile</th>
                        <th>Reference</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($depositAddresses as $depositAddress)
                        <tr>
                            <td class="fw-semibold text-primary">{{ $depositAddress->crypto_network }}</td>
                            <td class="font-monospace small text-break">{{ $depositAddress->address }}</td>
                            <td>{{ $depositAddress->conversion_target_asset ?? 'None' }}</td>
                            <td class="font-monospace small">{{ $depositAddress->paxos_profile_id ?? $depositAddress->profile?->paxos_profile_id ?? 'N/A' }}</td>
                            <td class="font-monospace small">{{ $depositAddress->ref_id }}</td>
                            <td class="text-muted">{{ $depositAddress->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">No deposit addresses yet. Create one from a profile page.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
