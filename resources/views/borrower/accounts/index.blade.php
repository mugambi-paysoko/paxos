@extends('layouts.borrower')

@section('title', 'Accounts')

@section('content')
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Type</th>
                        <th>Identity</th>
                        <th>Description</th>
                        <th>Profile</th>
                        <th>Created</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($accounts as $account)
                        <tr>
                            <td class="fw-semibold text-primary">{{ $account->type }}</td>
                            <td>{{ $account->identity->first_name }} {{ $account->identity->last_name }}</td>
                            <td class="text-muted">{{ $account->description ?? 'No description' }}</td>
                            <td>
                                @if ($account->profile)
                                    <span class="badge rounded-pill text-bg-success">Has profile</span>
                                @else
                                    <span class="badge rounded-pill text-bg-secondary">None</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $account->created_at->diffForHumans() }}</td>
                            <td class="text-end">
                                <a href="{{ route('borrower.accounts.show', $account) }}" class="btn btn-sm btn-outline-primary rounded-3">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                No accounts found. Accounts are created when you create an identity with the &ldquo;Create account&rdquo; option enabled.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
