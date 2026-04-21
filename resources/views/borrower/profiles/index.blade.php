@extends('layouts.borrower')

@section('title', 'Profiles')

@section('content')
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Profile</th>
                        <th>Identity</th>
                        <th>Created</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($profiles as $profile)
                        <tr>
                            <td class="fw-semibold text-primary">{{ $profile->account->type }} account</td>
                            <td>{{ $profile->account->identity->first_name }} {{ $profile->account->identity->last_name }}</td>
                            <td class="text-muted">{{ $profile->created_at->diffForHumans() }}</td>
                            <td class="text-end">
                                <a href="{{ route('borrower.profiles.show', $profile) }}" class="btn btn-sm btn-outline-primary rounded-3">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">
                                No profiles found. Profiles are created automatically when creating accounts with the &ldquo;Create profile&rdquo; option enabled.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
