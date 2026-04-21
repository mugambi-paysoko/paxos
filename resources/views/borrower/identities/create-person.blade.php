@extends('layouts.borrower')

@section('title', 'Create personal identity')

@section('page_actions')
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-3">Cancel</a>
@endsection

@section('content')
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 p-md-5">
            <form action="{{ route('borrower.identities.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <h5 class="fw-semibold mb-4">Personal details</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label">First name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" id="first_name" required value="{{ old('first_name') }}" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label for="last_name" class="form-label">Last name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" id="last_name" required value="{{ old('last_name') }}" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label for="date_of_birth" class="form-label">Date of birth <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_birth" id="date_of_birth" required value="{{ old('date_of_birth') }}" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label for="nationality" class="form-label">Nationality <span class="text-danger">*</span></label>
                        <input type="text" name="nationality" id="nationality" required value="{{ old('nationality') }}" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" required value="{{ old('email') }}" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label for="phone_number" class="form-label">Phone number</label>
                        <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number') }}" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label for="cip_id" class="form-label">CIP ID (SSN)</label>
                        <input type="text" name="cip_id" id="cip_id" value="{{ old('cip_id') }}" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label for="cip_id_type" class="form-label">CIP ID type</label>
                        <select name="cip_id_type" id="cip_id_type" class="form-select rounded-3">
                            <option value="SSN" @selected(old('cip_id_type') == 'SSN')>SSN</option>
                            <option value="PASSPORT" @selected(old('cip_id_type') == 'PASSPORT')>Passport</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="cip_id_country" class="form-label">CIP ID country</label>
                        <input type="text" name="cip_id_country" id="cip_id_country" value="{{ old('cip_id_country', 'USA') }}" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label for="address_country" class="form-label">Country <span class="text-danger">*</span></label>
                        <input type="text" name="address_country" id="address_country" required value="{{ old('address_country') }}" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label for="address1" class="form-label">Address line 1 <span class="text-danger">*</span></label>
                        <input type="text" name="address1" id="address1" required value="{{ old('address1') }}" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                        <input type="text" name="city" id="city" required value="{{ old('city') }}" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label for="province" class="form-label">Province / state</label>
                        <input type="text" name="province" id="province" value="{{ old('province') }}" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label for="zip_code" class="form-label">Zip code</label>
                        <input type="text" name="zip_code" id="zip_code" value="{{ old('zip_code') }}" class="form-control rounded-3">
                    </div>
                </div>

                <hr class="my-5 opacity-25">

                <h5 class="fw-semibold mb-2">Documents (optional)</h5>
                <p class="text-muted fs-3 mb-4">Upload now or later. You will be notified if documents are required.</p>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="proof_of_identity" class="form-label">Proof of identity</label>
                        <div class="form-text mb-2">Government ID — PDF, JPG, PNG (max 10MB)</div>
                        <input type="file" name="proof_of_identity" id="proof_of_identity" accept=".pdf,.jpg,.jpeg,.png" class="form-control rounded-3">
                        @error('proof_of_identity')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="proof_of_residency" class="form-label">Proof of residency</label>
                        <div class="form-text mb-2">Utility bill, bank statement, etc.</div>
                        <input type="file" name="proof_of_residency" id="proof_of_residency" accept=".pdf,.jpg,.jpeg,.png" class="form-control rounded-3">
                        @error('proof_of_residency')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="proof_of_ssn" class="form-label">Proof of SSN (optional)</label>
                        <input type="file" name="proof_of_ssn" id="proof_of_ssn" accept=".pdf,.jpg,.jpeg,.png" class="form-control rounded-3">
                        @error('proof_of_ssn')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr class="my-5 opacity-25">

                <h5 class="fw-semibold mb-2">Account &amp; profile (optional)</h5>
                <p class="text-muted fs-3 mb-4">Create an account and profile now, or after your identity is approved.</p>
                <div class="form-check mb-3">
                    <input type="checkbox" name="create_account" value="1" class="form-check-input" id="create_account_checkbox" @checked(old('create_account'))>
                    <label class="form-check-label" for="create_account_checkbox">Create account automatically</label>
                </div>
                <div id="account_fields" class="d-none border-start border-2 ps-4 ms-1">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="account_type" class="form-label">Account type</label>
                            <select name="account_type" id="account_type" class="form-select rounded-3">
                                <option value="BROKERAGE" @selected(old('account_type', 'BROKERAGE') == 'BROKERAGE')>Brokerage</option>
                                <option value="CUSTODY" @selected(old('account_type') == 'CUSTODY')>Custody</option>
                                <option value="OTHER" @selected(old('account_type') == 'OTHER')>Other</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="account_description" class="form-label">Account description</label>
                            <textarea name="account_description" id="account_description" rows="2" class="form-control rounded-3">{{ old('account_description') }}</textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="create_profile" value="1" class="form-check-input" id="create_profile" @checked(old('create_profile', true))>
                                <label class="form-check-label" for="create_profile">Create profile automatically</label>
                            </div>
                            <div class="form-text">A profile is required for balances and transactions.</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-end gap-2 mt-5 pt-4 border-top">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-3">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-3">Create identity</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkbox = document.getElementById('create_account_checkbox');
            const fields = document.getElementById('account_fields');

            function sync() {
                if (checkbox.checked) {
                    fields.classList.remove('d-none');
                } else {
                    fields.classList.add('d-none');
                }
            }

            checkbox.addEventListener('change', sync);
            sync();
        });
    </script>
@endpush
