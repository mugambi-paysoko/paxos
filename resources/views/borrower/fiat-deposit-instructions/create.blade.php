@extends('layouts.borrower')

@section('title', 'Create fiat deposit instruction')

@section('page_actions')
    <a href="{{ route('borrower.profiles.show', $profile) }}" class="btn btn-outline-secondary rounded-3">Cancel</a>
@endsection

@section('content')
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 p-md-5">
            <div class="alert alert-info border-0 rounded-3 mb-4">
                <span class="fw-semibold">Profile:</span> <span class="font-monospace">{{ $profile->paxos_profile_id }}</span>
            </div>
            <form action="{{ route('borrower.profiles.fiat-deposit-instructions.store', $profile) }}" method="POST">
                @csrf
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="fiat_network" class="form-label">Fiat network <span class="text-danger">*</span></label>
                        <select name="fiat_network" id="fiat_network" required class="form-select rounded-3">
                            <option value="">Select a network</option>
                            <option value="WIRE" @selected(old('fiat_network') == 'WIRE')>WIRE</option>
                            <option value="CUBIX" @selected(old('fiat_network') == 'CUBIX')>CUBIX</option>
                            <option value="DBS_ACT" @selected(old('fiat_network') == 'DBS_ACT')>DBS ACT</option>
                            <option value="SCB" @selected(old('fiat_network') == 'SCB')>SCB</option>
                        </select>
                    </div>
                    <div class="col-md-6" id="routing_number_type_section" style="display: none;">
                        <label for="routing_number_type" class="form-label">Routing number type (optional)</label>
                        <select name="routing_number_type" id="routing_number_type" class="form-select rounded-3">
                            <option value="">Select type</option>
                            <option value="ABA" @selected(old('routing_number_type') == 'ABA')>ABA</option>
                            <option value="SWIFT" @selected(old('routing_number_type') == 'SWIFT')>SWIFT</option>
                        </select>
                        <div class="form-text">Optional for WIRE.</div>
                    </div>
                    <div class="col-12">
                        <label for="ref_id" class="form-label">Reference ID (optional)</label>
                        <input type="text" name="ref_id" id="ref_id" value="{{ old('ref_id') }}" placeholder="Leave empty to auto-generate" class="form-control rounded-3">
                    </div>
                </div>
                <div class="d-flex flex-wrap justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('borrower.profiles.show', $profile) }}" class="btn btn-outline-secondary rounded-3">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-3">Create instruction</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('fiat_network').addEventListener('change', function () {
            document.getElementById('routing_number_type_section').style.display = this.value === 'WIRE' ? 'block' : 'none';
        });
        document.getElementById('fiat_network').dispatchEvent(new Event('change'));
    </script>
@endpush
