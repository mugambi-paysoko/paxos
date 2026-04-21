@extends('layouts.borrower')

@section('title', 'Create crypto deposit address')

@section('page_actions')
    <a href="{{ route('borrower.profiles.show', $profile) }}" class="btn btn-outline-secondary rounded-3">Cancel</a>
@endsection

@section('content')
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 p-md-5">
            <div class="alert alert-primary border-0 rounded-3 mb-4">
                <p class="mb-1 fw-semibold">Profile: <span class="font-monospace">{{ $profile->paxos_profile_id }}</span></p>
                <p class="mb-0 fs-3">Deposits to this address credit this Paxos profile. For PYUSD on Stellar, see
                    <a href="https://docs.paxos.com" class="alert-link" target="_blank" rel="noopener">Paxos docs</a>.
                </p>
            </div>
            <form action="{{ route('borrower.profiles.deposit-addresses.store', $profile) }}" method="POST">
                @csrf
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="crypto_network" class="form-label">Blockchain network <span class="text-danger">*</span></label>
                        <select name="crypto_network" id="crypto_network" required class="form-select rounded-3">
                            <option value="">Select network</option>
                            @foreach (\App\Models\DepositAddress::allowedCryptoNetworks() as $network)
                                <option value="{{ $network }}" @selected(old('crypto_network') == $network)>{{ $network }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="conversion_target_asset" class="form-label">Conversion target (optional)</label>
                        <select name="conversion_target_asset" id="conversion_target_asset" class="form-select rounded-3">
                            <option value="">Default (omit)</option>
                            <option value="NO_CONVERSION" @selected(old('conversion_target_asset') == 'NO_CONVERSION')>NO_CONVERSION</option>
                            <option value="USD" @selected(old('conversion_target_asset') == 'USD')>USD</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="ref_id" class="form-label">Reference ID (optional)</label>
                        <input type="text" name="ref_id" id="ref_id" value="{{ old('ref_id') }}" class="form-control rounded-3" placeholder="Auto UUID if empty">
                        <div class="form-text">Must be unique per Paxos.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="metadata_label" class="form-label">Label (optional)</label>
                        <input type="text" name="metadata_label" id="metadata_label" value="{{ old('metadata_label') }}" maxlength="100" class="form-control rounded-3">
                    </div>
                </div>
                <div class="d-flex flex-wrap justify-content-between gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('borrower.profiles.show', $profile) }}" class="btn btn-outline-secondary rounded-3">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-3">Create address</button>
                </div>
            </form>
        </div>
    </div>
@endsection
