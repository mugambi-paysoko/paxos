@extends('layouts.app')

@section('title', 'Identities')

@section('page_actions')
    <a href="{{ route('lender.identities.create') }}" class="btn btn-primary rounded-3">
        <i class="ti ti-plus me-1"></i> Create identity
    </a>
@endsection

@section('content')
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Nationality</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($identities as $identity)
                        <tr>
                            <td class="fw-semibold text-primary">{{ $identity->first_name }} {{ $identity->last_name }}</td>
                            <td>{{ $identity->email }}</td>
                            <td>{{ $identity->nationality }}</td>
                            <td>
                                <span class="badge rounded-pill {{ $identity->id_verification_status === 'APPROVED' ? 'text-bg-success' : 'text-bg-warning' }}">
                                    {{ $identity->id_verification_status }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $identity->created_at->diffForHumans() }}</td>
                            <td class="text-end">
                                <a href="{{ route('lender.identities.show', $identity) }}" class="btn btn-sm btn-outline-primary rounded-3">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                No identities found.
                                <a href="{{ route('lender.identities.create') }}" class="fw-semibold">Create one</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
