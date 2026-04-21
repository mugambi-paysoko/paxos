@extends('layouts.borrower')

@section('title', 'Create crypto withdrawal')

@section('page_actions')
    <a href="{{ route('borrower.profiles.show', $profile) }}" class="btn btn-outline-secondary rounded-3">Cancel</a>
@endsection

@section('content')
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 p-md-5">
            <form action="{{ route('borrower.profiles.crypto-withdrawals.store', $profile) }}" method="POST">
                @csrf
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Asset <span class="text-danger">*</span></label>
                        <input type="text" name="asset" required value="{{ old('asset') }}" class="form-control rounded-3" placeholder="BTC, ETH, USDP…">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Crypto network <span class="text-danger">*</span></label>
                        <select name="crypto_network" required class="form-select rounded-3">
                            @foreach (\App\Models\CryptoWithdrawal::allowedNetworks() as $network)
                                <option value="{{ $network }}" @selected(old('crypto_network') === $network)>{{ $network }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Destination address <span class="text-danger">*</span></label>
                        <input type="text" name="destination_address" required value="{{ old('destination_address') }}" class="form-control font-monospace rounded-3">
                        <div class="form-text">For Stellar PYUSD withdrawals, ensure the destination has a trustline configured.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.00000001" min="0.00000001" name="amount" value="{{ old('amount') }}" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Total (incl. fees)</label>
                        <input type="number" step="0.00000001" min="0.00000001" name="total" value="{{ old('total') }}" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reference ID (optional)</label>
                        <input type="text" name="ref_id" value="{{ old('ref_id') }}" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Memo (optional)</label>
                        <input type="text" name="memo" value="{{ old('memo') }}" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Metadata label (optional)</label>
                        <input type="text" name="metadata_label" maxlength="100" value="{{ old('metadata_label') }}" class="form-control rounded-3">
                    </div>
                </div>
                <div class="d-flex flex-wrap justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('borrower.profiles.show', $profile) }}" class="btn btn-outline-secondary rounded-3">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-3">Create withdrawal</button>
                </div>
            </form>
        </div>
    </div>
@endsection
