@extends('layouts.borrower')

@section('title', 'Create fiat withdrawal')

@section('page_actions')
    <a href="{{ route('borrower.fiat-withdrawals.index') }}" class="btn btn-outline-secondary rounded-3">Cancel</a>
@endsection

@section('content')
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 p-md-5">
            <form action="{{ route('borrower.fiat-withdrawals.store') }}" method="POST">
                @csrf
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="profile_id" class="form-label">Profile <span class="text-danger">*</span></label>
                        <select name="profile_id" id="profile_id" required class="form-select rounded-3">
                            <option value="">Select profile</option>
                            @foreach ($profiles as $profile)
                                <option value="{{ $profile->id }}" @selected(old('profile_id') == $profile->id)>{{ $profile->paxos_profile_id }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="fiat_account_id" class="form-label">Destination fiat account <span class="text-danger">*</span></label>
                        <select name="fiat_account_id" id="fiat_account_id" required class="form-select rounded-3">
                            <option value="">Select fiat account</option>
                            @foreach ($fiatAccounts as $fiatAccount)
                                <option value="{{ $fiatAccount->id }}" @selected(old('fiat_account_id') == $fiatAccount->id)>
                                    {{ $fiatAccount->paxos_fiat_account_id }} ({{ $fiatAccount->identity->first_name }} {{ $fiatAccount->identity->last_name }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Only APPROVED fiat accounts are listed.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="amount" class="form-label">Amount (USD)</label>
                        <input type="number" step="0.01" min="0.01" name="amount" id="amount" value="{{ old('amount') }}" class="form-control rounded-3">
                        <div class="form-text">Specify either amount or total.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="total" class="form-label">Total incl. fees (USD)</label>
                        <input type="number" step="0.01" min="0.01" name="total" id="total" value="{{ old('total') }}" class="form-control rounded-3">
                    </div>
                </div>
                <input type="hidden" name="asset" value="USD">
                <div class="row g-4 mt-1">
                    <div class="col-md-6">
                        <label for="ref_id" class="form-label">Reference ID (optional)</label>
                        <input type="text" name="ref_id" id="ref_id" value="{{ old('ref_id') }}" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label for="metadata_label" class="form-label">Metadata label (optional)</label>
                        <input type="text" name="metadata_label" id="metadata_label" maxlength="100" value="{{ old('metadata_label') }}" class="form-control rounded-3">
                    </div>
                    <div class="col-12">
                        <label for="memo" class="form-label">Memo (optional)</label>
                        <input type="text" name="memo" id="memo" maxlength="255" value="{{ old('memo') }}" class="form-control rounded-3">
                    </div>
                </div>
                <div class="d-flex flex-wrap justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('borrower.fiat-withdrawals.index') }}" class="btn btn-outline-secondary rounded-3">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-3">Create withdrawal</button>
                </div>
            </form>
        </div>
    </div>
@endsection
