@extends('layouts.borrower')

@section('title', 'Create fiat account')

@section('page_actions')
    <a href="{{ route('borrower.fiat-accounts.index') }}" class="btn btn-outline-secondary rounded-3">Cancel</a>
@endsection

@section('content')
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-4 p-md-5">
            <h5 class="fw-semibold mb-2">Create a fiat settlement account</h5>
            <p class="text-muted mb-0">Choose an identity and network, then provide account owner and bank details. Required fields adapt automatically between WIRE and CUBIX.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 p-md-5">
            <form action="{{ route('borrower.fiat-accounts.store') }}" method="POST">
                @csrf

                <h5 class="fw-semibold mb-4">Basics</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="identity_id" class="form-label">Identity <span class="text-danger">*</span></label>
                        <select name="identity_id" id="identity_id" required class="form-select rounded-3">
                            <option value="">Select an identity</option>
                            @foreach ($identities as $identity)
                                <option value="{{ $identity->id }}" @selected(old('identity_id') == $identity->id)>
                                    {{ $identity->first_name }} {{ $identity->last_name }} ({{ $identity->paxos_identity_id }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Identity to associate with this fiat account.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="fiat_network" class="form-label">Fiat network <span class="text-danger">*</span></label>
                        <select name="fiat_network" id="fiat_network" required class="form-select rounded-3">
                            <option value="WIRE" @selected(old('fiat_network', 'WIRE') == 'WIRE')>WIRE</option>
                            <option value="CUBIX" @selected(old('fiat_network') == 'CUBIX')>CUBIX</option>
                        </select>
                        <div class="form-text">WIRE uses ABA/SWIFT routing. CUBIX uses a CUBIX account ID.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="ref_id" class="form-label">Reference ID (optional)</label>
                        <input type="text" name="ref_id" id="ref_id" value="{{ old('ref_id') }}" class="form-control rounded-3">
                    </div>
                </div>

                <hr class="my-5 opacity-25">

                <h5 class="fw-semibold mb-4">Account owner</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label">First name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" id="first_name" required value="{{ old('first_name') }}" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6">
                        <label for="last_name" class="form-label">Last name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" id="last_name" required value="{{ old('last_name') }}" class="form-control rounded-3">
                    </div>
                </div>

                <div id="wireFields" class="mt-5">
                    <hr class="my-4 opacity-25">
                    <h5 class="fw-semibold mb-4">Bank account (WIRE)</h5>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="account_number" class="form-label">Account number <span class="text-danger">*</span></label>
                            <input type="text" name="account_number" id="account_number" required value="{{ old('account_number') }}" class="form-control rounded-3">
                        </div>
                        <div class="col-md-6">
                            <label for="routing_number" class="form-label">Routing number <span class="text-danger">*</span></label>
                            <input type="text" name="routing_number" id="routing_number" required value="{{ old('routing_number') }}" class="form-control rounded-3">
                        </div>
                        <div class="col-md-6">
                            <label for="routing_number_type" class="form-label">Routing number type <span class="text-danger">*</span></label>
                            <select name="routing_number_type" id="routing_number_type" required class="form-select rounded-3">
                                <option value="ABA" @selected(old('routing_number_type') == 'ABA')>ABA</option>
                                <option value="SWIFT" @selected(old('routing_number_type') == 'SWIFT')>SWIFT</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="bank_name" class="form-label">Bank name <span class="text-danger">*</span></label>
                            <input type="text" name="bank_name" id="bank_name" required value="{{ old('bank_name') }}" class="form-control rounded-3">
                        </div>
                    </div>

                    <hr class="my-5 opacity-25">
                    <h5 class="fw-semibold mb-4">Bank address</h5>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="bank_country" class="form-label">Country <span class="text-danger">*</span></label>
                            <input type="text" name="bank_country" id="bank_country" required value="{{ old('bank_country', 'USA') }}" class="form-control rounded-3">
                        </div>
                        <div class="col-md-6">
                            <label for="bank_address1" class="form-label">Address line 1</label>
                            <input type="text" name="bank_address1" id="bank_address1" value="{{ old('bank_address1') }}" class="form-control rounded-3">
                        </div>
                        <div class="col-md-6">
                            <label for="bank_city" class="form-label">City</label>
                            <input type="text" name="bank_city" id="bank_city" value="{{ old('bank_city') }}" class="form-control rounded-3">
                        </div>
                        <div class="col-md-6">
                            <label for="bank_province" class="form-label">Province / state</label>
                            <input type="text" name="bank_province" id="bank_province" value="{{ old('bank_province') }}" class="form-control rounded-3">
                        </div>
                        <div class="col-md-6">
                            <label for="bank_zip_code" class="form-label">Zip code</label>
                            <input type="text" name="bank_zip_code" id="bank_zip_code" value="{{ old('bank_zip_code') }}" class="form-control rounded-3">
                        </div>
                    </div>

                    <hr class="my-5 opacity-25">
                    <h5 class="fw-semibold mb-4">Account owner address</h5>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="owner_address1" class="form-label">Address line 1 <span class="text-danger">*</span></label>
                            <input type="text" name="owner_address1" id="owner_address1" required value="{{ old('owner_address1') }}" class="form-control rounded-3">
                        </div>
                        <div class="col-md-6">
                            <label for="owner_city" class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" name="owner_city" id="owner_city" required value="{{ old('owner_city') }}" class="form-control rounded-3">
                        </div>
                        <div class="col-md-6">
                            <label for="owner_country" class="form-label">Country <span class="text-danger">*</span></label>
                            <input type="text" name="owner_country" id="owner_country" required value="{{ old('owner_country', 'USA') }}" class="form-control rounded-3">
                        </div>
                        <div class="col-md-6">
                            <label for="owner_province" class="form-label">Province / state</label>
                            <input type="text" name="owner_province" id="owner_province" value="{{ old('owner_province') }}" class="form-control rounded-3">
                        </div>
                        <div class="col-md-6">
                            <label for="owner_zip_code" class="form-label">Zip code</label>
                            <input type="text" name="owner_zip_code" id="owner_zip_code" value="{{ old('owner_zip_code') }}" class="form-control rounded-3">
                        </div>
                    </div>
                </div>

                <div id="cubixFields" class="mt-5 d-none">
                    <hr class="my-4 opacity-25">
                    <h5 class="fw-semibold mb-4">CUBIX account</h5>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="cubix_account_id" class="form-label">CUBIX account ID <span class="text-danger">*</span></label>
                            <input type="text" name="cubix_account_id" id="cubix_account_id" value="{{ old('cubix_account_id') }}" class="form-control rounded-3">
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-end gap-2 mt-5 pt-4 border-top">
                    <a href="{{ route('borrower.fiat-accounts.index') }}" class="btn btn-outline-secondary rounded-3">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-3">Create fiat account</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const fiatNetworkSelect = document.getElementById('fiat_network');
        const wireFields = document.getElementById('wireFields');
        const cubixFields = document.getElementById('cubixFields');
        const wireRequiredIds = [
            'account_number',
            'routing_number',
            'routing_number_type',
            'bank_name',
            'bank_country',
            'owner_address1',
            'owner_city',
            'owner_country',
        ];
        const cubixRequiredIds = ['cubix_account_id'];

        function updateNetworkSections() {
            const isWire = fiatNetworkSelect.value === 'WIRE';
            wireFields.classList.toggle('d-none', !isWire);
            cubixFields.classList.toggle('d-none', isWire);

            wireRequiredIds.forEach((id) => {
                const el = document.getElementById(id);
                if (el) {
                    el.required = isWire;
                }
            });

            cubixRequiredIds.forEach((id) => {
                const el = document.getElementById(id);
                if (el) {
                    el.required = !isWire;
                }
            });
        }

        fiatNetworkSelect.addEventListener('change', updateNetworkSections);
        updateNetworkSections();
    </script>
@endpush
