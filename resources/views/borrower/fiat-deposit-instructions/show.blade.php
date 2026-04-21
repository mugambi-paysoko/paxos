@extends('layouts.borrower')

@section('title', 'Fiat deposit instruction')

@section('page_actions')
    @if ($fiatDepositInstruction->memo_id && $fiatDepositInstruction->fiat_network_instructions)
        <button type="button" class="btn btn-success rounded-3" data-bs-toggle="modal" data-bs-target="#depositModal">
            <i class="ti ti-cash me-1"></i> Initiate deposit
        </button>
    @endif
    <a href="{{ route('borrower.profiles.show', $fiatDepositInstruction->profile) }}" class="btn btn-outline-secondary rounded-3">
        <i class="ti ti-arrow-left me-1"></i> Profile
    </a>
@endsection

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="fs-2 text-muted mb-1">Instruction ID</p>
                    <p class="mb-0 fw-semibold font-monospace small text-break">{{ $fiatDepositInstruction->paxos_deposit_instruction_id }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="fs-2 text-muted mb-1">Status</p>
                    <span class="badge rounded-pill {{ ($fiatDepositInstruction->status ?? '') === 'VALID' ? 'text-bg-success' : 'text-bg-warning' }}">
                        {{ $fiatDepositInstruction->status ?? 'N/A' }}
                    </span>
                    <p class="mb-0 mt-3 fs-2 text-muted">Fiat network</p>
                    <p class="mb-0 fw-medium">{{ $fiatDepositInstruction->fiat_network }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="fs-2 text-muted mb-1">Memo ID</p>
                    <p class="mb-0 fw-semibold font-monospace text-primary">{{ $fiatDepositInstruction->memo_id ?? 'N/A' }}</p>
                    <p class="mb-0 mt-3 fs-2 text-muted">Reference ID</p>
                    <p class="mb-0 font-monospace small">{{ $fiatDepositInstruction->ref_id }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-4 p-md-5">
            <h5 class="fw-semibold mb-3">Instruction details</h5>
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
                            <td class="text-muted">Profile ID</td>
                            <td class="font-monospace small">{{ $fiatDepositInstruction->paxos_profile_id }}</td>
                            <td class="text-muted">Routing number type</td>
                            <td>{{ $fiatDepositInstruction->routing_number_type ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Paxos identity ID</td>
                            <td class="font-monospace small">{{ $fiatDepositInstruction->paxos_identity_id ?? 'N/A' }}</td>
                            <td class="text-muted">Paxos account ID</td>
                            <td class="font-monospace small">{{ $fiatDepositInstruction->paxos_account_id ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created at (Paxos)</td>
                            <td>{{ $fiatDepositInstruction->paxos_created_at ? $fiatDepositInstruction->paxos_created_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                            <td class="text-muted">Created</td>
                            <td>{{ $fiatDepositInstruction->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($fiatDepositInstruction->profile)
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body p-4 p-md-5">
                <h5 class="fw-semibold mb-3">Profile information</h5>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light"><tr><th>Field</th><th>Value</th></tr></thead>
                        <tbody>
                            <tr>
                                <td class="text-muted">Profile ID</td>
                                <td class="font-monospace">{{ $fiatDepositInstruction->profile->paxos_profile_id }}</td>
                            </tr>
                            @if ($fiatDepositInstruction->profile->account)
                                <tr>
                                    <td class="text-muted">Account type</td>
                                    <td>{{ $fiatDepositInstruction->profile->account->type }}</td>
                                </tr>
                                @if ($fiatDepositInstruction->profile->account->identity)
                                    <tr>
                                        <td class="text-muted">Identity</td>
                                        <td>{{ $fiatDepositInstruction->profile->account->identity->first_name }} {{ $fiatDepositInstruction->profile->account->identity->last_name }}</td>
                                    </tr>
                                @endif
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if ($fiatDepositInstruction->fiat_account_owner)
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body p-4 p-md-5">
                <h5 class="fw-semibold mb-3">Account owner</h5>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light"><tr><th>Type</th><th>Name</th></tr></thead>
                        <tbody>
                            @if (isset($fiatDepositInstruction->fiat_account_owner['person_details']))
                                <tr>
                                    <td class="text-muted">Person</td>
                                    <td>{{ $fiatDepositInstruction->fiat_account_owner['person_details']['first_name'] ?? 'N/A' }} {{ $fiatDepositInstruction->fiat_account_owner['person_details']['last_name'] ?? '' }}</td>
                                </tr>
                            @endif
                            @if (isset($fiatDepositInstruction->fiat_account_owner['institution_details']))
                                <tr>
                                    <td class="text-muted">Institution</td>
                                    <td>{{ $fiatDepositInstruction->fiat_account_owner['institution_details']['name'] ?? 'N/A' }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if ($fiatDepositInstruction->fiat_network_instructions)
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body p-4 p-md-5">
                <h5 class="fw-semibold mb-3">Network instructions</h5>
                <pre class="p-4 rounded-3 bg-body-secondary mb-0 small overflow-auto">{{ json_encode($fiatDepositInstruction->fiat_network_instructions, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    @endif

    @if ($fiatDepositInstruction->metadata)
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4 p-md-5">
                <h5 class="fw-semibold mb-3">Metadata</h5>
                <pre class="p-4 rounded-3 bg-body-secondary mb-0 small overflow-auto">{{ json_encode($fiatDepositInstruction->metadata, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    @endif

    @if ($fiatDepositInstruction->memo_id && $fiatDepositInstruction->fiat_network_instructions)
        <div class="modal fade" id="depositModal" tabindex="-1" aria-labelledby="depositModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-semibold" id="depositModalLabel">Initiate sandbox fiat deposit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('borrower.fiat-deposit-instructions.initiate-deposit', $fiatDepositInstruction) }}" method="POST">
                        @csrf
                        <div class="modal-body pt-2">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" name="amount" id="amount" step="0.01" min="0.01" value="{{ old('amount', '1000.00') }}" required class="form-control rounded-3">
                            </div>
                            <div class="mb-3">
                                <label for="asset" class="form-label">Asset</label>
                                <select name="asset" id="asset" class="form-select rounded-3">
                                    <option value="USD" selected>USD</option>
                                </select>
                            </div>
                            <div class="alert alert-light border rounded-3 mb-0">
                                <p class="mb-1 small"><strong>Memo ID:</strong> <span class="font-monospace">{{ $fiatDepositInstruction->memo_id }}</span></p>
                                <p class="mb-0 small"><strong>Network:</strong> {{ $fiatDepositInstruction->fiat_network }}</p>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success rounded-3">Initiate deposit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
