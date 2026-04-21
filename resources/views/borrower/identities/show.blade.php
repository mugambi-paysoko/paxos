@extends('layouts.borrower')

@section('title', 'Identity details')

@section('page_actions')
    @if ($identity->id_verification_status !== 'APPROVED')
        <form action="{{ route('borrower.identities.approve', $identity) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success rounded-3">
                <i class="ti ti-check me-1"></i> Approve (sandbox)
            </button>
        </form>
    @endif
    <a href="{{ route('borrower.identities.index') }}" class="btn btn-outline-secondary rounded-3">Back</a>
@endsection

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="fs-2 text-muted mb-1">Identity owner</p>
                    <p class="mb-0 fw-semibold">{{ $identity->first_name }} {{ $identity->last_name }}</p>
                    <p class="mb-0 mt-1 fs-3 text-muted text-break">{{ $identity->email }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="fs-2 text-muted mb-1">ID verification</p>
                    <span class="badge rounded-pill {{ $identity->id_verification_status === 'APPROVED' ? 'text-bg-success' : ($identity->id_verification_status === 'REJECTED' ? 'text-bg-danger' : 'text-bg-warning') }}">{{ $identity->id_verification_status }}</span>
                    <p class="mb-0 mt-3 fs-2 text-muted">Sanctions</p>
                    <span class="badge rounded-pill {{ $identity->sanctions_verification_status === 'APPROVED' ? 'text-bg-success' : ($identity->sanctions_verification_status === 'REJECTED' ? 'text-bg-danger' : 'text-bg-warning') }}">{{ $identity->sanctions_verification_status }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="fs-2 text-muted mb-1">Paxos identity ID</p>
                    <p class="mb-0 fw-semibold font-monospace small text-break">{{ $identity->paxos_identity_id ?? 'N/A' }}</p>
                    <p class="mb-0 mt-3 fs-2 text-muted">Reference ID</p>
                    <p class="mb-0 font-monospace small text-break">{{ $identity->ref_id }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-4 p-md-5">
            <h5 class="fw-semibold mb-3">Personal and address details</h5>
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
                            <td class="text-muted">First name</td>
                            <td class="fw-semibold">{{ $identity->first_name }}</td>
                            <td class="text-muted">Last name</td>
                            <td class="fw-semibold">{{ $identity->last_name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Date of birth</td>
                            <td>{{ $identity->date_of_birth ? $identity->date_of_birth->format('Y-m-d') : 'N/A' }}</td>
                            <td class="text-muted">Nationality</td>
                            <td>{{ $identity->nationality }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Phone</td>
                            <td>{{ $identity->phone_number ?? 'N/A' }}</td>
                            <td class="text-muted">Country</td>
                            <td>{{ $identity->address_country }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Street</td>
                            <td>{{ $identity->address1 }}</td>
                            <td class="text-muted">City</td>
                            <td>{{ $identity->city }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Province / state</td>
                            <td>{{ $identity->province ?? 'N/A' }}</td>
                            <td class="text-muted">Zip</td>
                            <td>{{ $identity->zip_code ?? 'N/A' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($identity->accounts && $identity->accounts->count() > 0)
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body p-4 p-md-5">
                <h5 class="fw-semibold mb-3">Accounts</h5>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Profile</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($identity->accounts as $account)
                                <tr>
                                    <td class="fw-semibold text-primary">{{ $account->type }}</td>
                                    <td>{{ $account->description ?? 'No description' }}</td>
                                    <td class="font-monospace small">{{ $account->profile?->paxos_profile_id ?? 'None' }}</td>
                                    <td class="text-muted">{{ $account->created_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 p-md-5">
            <h5 class="fw-semibold mb-3">Uploaded documents</h5>
            @if ($identity->documents && $identity->documents->count() > 0)
                <div class="table-responsive rounded-3 border">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-uppercase fs-2 text-muted fw-semibold">Type</th>
                                <th class="text-uppercase fs-2 text-muted fw-semibold">File</th>
                                <th class="text-uppercase fs-2 text-muted fw-semibold">Status</th>
                                <th class="text-uppercase fs-2 text-muted fw-semibold">Uploaded</th>
                                <th class="text-uppercase fs-2 text-muted fw-semibold">Paxos ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($identity->documents as $document)
                                <tr>
                                    <td>{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</td>
                                    <td>
                                        {{ $document->file_name }}
                                        <span class="text-muted fs-2">({{ number_format($document->file_size / 1024, 2) }} KB)</span>
                                    </td>
                                    <td>
                                        @if ($document->upload_status === 'uploaded')
                                            <span class="badge rounded-pill text-bg-success">Uploaded</span>
                                        @elseif ($document->upload_status === 'failed')
                                            <span class="badge rounded-pill text-bg-danger">Failed</span>
                                            @if ($document->error_message)
                                                <div class="text-danger small mt-1">{{ \Illuminate\Support\Str::limit($document->error_message, 50) }}</div>
                                            @endif
                                        @else
                                            <span class="badge rounded-pill text-bg-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $document->uploaded_at ? $document->uploaded_at->format('Y-m-d H:i:s') : '—' }}</td>
                                    <td class="font-monospace small text-muted">{{ $document->paxos_document_id ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mb-0">No documents uploaded for this identity yet.</p>
            @endif
        </div>
    </div>

    @if ($identity->id_verification_status === 'PENDING' || $identity->sanctions_verification_status === 'PENDING')
        <x-status-poll
            mode="identity"
            reload-on-change
            :url="route('borrower.status.identity', $identity)"
            :snapshot="['id' => $identity->id_verification_status, 's' => $identity->sanctions_verification_status]"
            :interval="15000"
        />
    @endif
@endsection
