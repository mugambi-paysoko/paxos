@extends('layouts.app')

@section('title', 'Fiat deposit instructions')

@section('content')
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Network</th>
                        <th>Profile</th>
                        <th>Memo ID</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($instructions as $instruction)
                        <tr>
                            <td class="fw-semibold text-primary">{{ $instruction->fiat_network }}</td>
                            <td class="font-monospace small">{{ $instruction->paxos_profile_id }}</td>
                            <td class="font-monospace small">{{ $instruction->memo_id ?? 'N/A' }}</td>
                            <td>
                                <span class="badge rounded-pill {{ $instruction->status === 'VALID' ? 'text-bg-success' : 'text-bg-warning' }}">
                                    {{ $instruction->status ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $instruction->created_at->diffForHumans() }}</td>
                            <td class="text-end">
                                <a href="{{ route('lender.fiat-deposit-instructions.show', $instruction) }}" class="btn btn-sm btn-outline-primary rounded-3">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">No fiat deposit instructions found. Create one from a profile page.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
