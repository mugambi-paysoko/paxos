@extends('layouts.app')

@section('title', 'Create Institution Identity')

@section('content')
<div class="py-6">
    <div class="mb-4">
        <div class="flex items-center text-sm text-gray-600 mb-2">
            <span class="bg-indigo-600 text-white rounded-full w-6 h-6 flex items-center justify-center mr-2">1</span>
            <span>Choose Type</span>
            <span class="mx-2">→</span>
            <span class="bg-indigo-600 text-white rounded-full w-6 h-6 flex items-center justify-center mr-2">2</span>
            <span>Add Members</span>
            <span class="mx-2">→</span>
            <span class="bg-indigo-600 text-white rounded-full w-6 h-6 flex items-center justify-center mr-2">3</span>
            <span class="font-semibold text-gray-900">Institution Details</span>
        </div>
    </div>

    <h1 class="text-3xl font-bold text-gray-900 mb-6">Create Institution Identity</h1>

    @if(count($members) > 0)
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <h3 class="font-semibold text-blue-900 mb-2">Institution Members ({{ count($members) }})</h3>
        <ul class="list-disc list-inside text-blue-800">
            @foreach($members as $member)
            <li>{{ $member['name'] }} - Roles: {{ implode(', ', $member['roles']) }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route('lender.identities.store-institution') }}" method="POST" enctype="multipart/form-data">
            @csrf

            @foreach($members as $index => $member)
                <input type="hidden" name="wizard_members[{{ $index }}][identity_id]" value="{{ $member['identity_id'] }}">
                <input type="hidden" name="wizard_members[{{ $index }}][local_identity_id]" value="{{ $member['local_identity_id'] }}">
                @foreach($member['roles'] as $role)
                    <input type="hidden" name="wizard_members[{{ $index }}][roles][]" value="{{ $role }}">
                @endforeach
            @endforeach

            <h3 class="text-lg font-semibold text-gray-800 mb-4">Institution Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Institution Name *</label>
                    <input type="text" name="name" id="name" required value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                    <input type="email" name="email" id="email" required value="{{ old('email') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="institution_type" class="block text-sm font-medium text-gray-700">Institution Type</label>
                    <input type="text" value="{{ $institutionType ?? 'Not selected' }}" disabled class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm">
                    <input type="hidden" name="institution_type" value="{{ $institutionType ?? '' }}">
                    <p class="mt-1 text-xs text-gray-500">Selected when adding members</p>
                </div>

                <div>
                    <label for="institution_sub_type" class="block text-sm font-medium text-gray-700">Institution Sub Type</label>
                    <input type="text" name="institution_sub_type" id="institution_sub_type" value="{{ old('institution_sub_type') }}" placeholder="e.g., HEDGE_FUND" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="cip_id" class="block text-sm font-medium text-gray-700">CIP ID (EIN) *</label>
                    <input type="text" name="cip_id" id="cip_id" required value="{{ old('cip_id') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="cip_id_type" class="block text-sm font-medium text-gray-700">CIP ID Type *</label>
                    <select name="cip_id_type" id="cip_id_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="EIN" {{ old('cip_id_type') == 'EIN' ? 'selected' : '' }}>EIN</option>
                        <option value="SSN" {{ old('cip_id_type') == 'SSN' ? 'selected' : '' }}>SSN</option>
                        <option value="OTHER" {{ old('cip_id_type') == 'OTHER' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div>
                    <label for="cip_id_country" class="block text-sm font-medium text-gray-700">CIP ID Country *</label>
                    <input type="text" name="cip_id_country" id="cip_id_country" required value="{{ old('cip_id_country', 'USA') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="govt_registration_date" class="block text-sm font-medium text-gray-700">Government Registration Date</label>
                    <input type="date" name="govt_registration_date" id="govt_registration_date" value="{{ old('govt_registration_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
            </div>

            <h3 class="text-lg font-semibold text-gray-800 mb-4">Business Address</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="business_address_country" class="block text-sm font-medium text-gray-700">Country *</label>
                    <input type="text" name="business_address_country" id="business_address_country" required value="{{ old('business_address_country') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="business_address1" class="block text-sm font-medium text-gray-700">Address Line 1 *</label>
                    <input type="text" name="business_address1" id="business_address1" required value="{{ old('business_address1') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="business_city" class="block text-sm font-medium text-gray-700">City *</label>
                    <input type="text" name="business_city" id="business_city" required value="{{ old('business_city') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="business_province" class="block text-sm font-medium text-gray-700">Province/State</label>
                    <input type="text" name="business_province" id="business_province" value="{{ old('business_province') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="business_zip_code" class="block text-sm font-medium text-gray-700">Zip Code</label>
                    <input type="text" name="business_zip_code" id="business_zip_code" value="{{ old('business_zip_code') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
            </div>

            <h3 class="text-lg font-semibold text-gray-800 mb-4">Regulation & Trading Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="regulation_status" class="block text-sm font-medium text-gray-700">Regulation Status</label>
                    <select name="regulation_status" id="regulation_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select...</option>
                        <option value="US_REGULATED" {{ old('regulation_status') == 'US_REGULATED' ? 'selected' : '' }}>US Regulated</option>
                        <option value="INTL_REGULATED" {{ old('regulation_status') == 'INTL_REGULATED' ? 'selected' : '' }}>Internationally Regulated</option>
                        <option value="NON_REGULATED" {{ old('regulation_status') == 'NON_REGULATED' ? 'selected' : '' }}>Non-Regulated</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Select if your institution is financially regulated</p>
                </div>

                <div>
                    <label for="trading_type" class="block text-sm font-medium text-gray-700">Trading Type</label>
                    <select name="trading_type" id="trading_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select...</option>
                        <option value="PRIVATE" {{ old('trading_type') == 'PRIVATE' ? 'selected' : '' }}>Private (Not publicly traded)</option>
                        <option value="PUBLIC" {{ old('trading_type') == 'PUBLIC' ? 'selected' : '' }}>Public (Publicly traded)</option>
                        <option value="PUBLICLY_TRADED_SUBSIDIARY" {{ old('trading_type') == 'PUBLICLY_TRADED_SUBSIDIARY' ? 'selected' : '' }}>Publicly Traded Subsidiary</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Select if your institution is publicly traded</p>
                </div>
            </div>

            <!-- Fields that appear based on regulation_status and trading_type -->
            <div id="conditionalFields" class="mb-6" style="display: none;">
                <!-- Public trading fields (listed_exchange, ticker_symbol) -->
                <div id="publicTradingFields" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6" style="display: none;">
                    <div>
                        <label for="listed_exchange" class="block text-sm font-medium text-gray-700">Listed Exchange *</label>
                        <input type="text" name="listed_exchange" id="listed_exchange" value="{{ old('listed_exchange') }}" placeholder="e.g., NASDAQ" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="ticker_symbol" class="block text-sm font-medium text-gray-700">Ticker Symbol *</label>
                        <input type="text" name="ticker_symbol" id="ticker_symbol" value="{{ old('ticker_symbol') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                </div>

                <!-- Regulated institution fields (regulator_name, regulator_jurisdiction, regulator_register_number) -->
                <div id="regulatedFields" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6" style="display: none;">
                    <div>
                        <label for="regulator_name" class="block text-sm font-medium text-gray-700">Regulator Name *</label>
                        <input type="text" name="regulator_name" id="regulator_name" value="{{ old('regulator_name') }}" placeholder="e.g., SEC" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="regulator_jurisdiction" class="block text-sm font-medium text-gray-700">Regulator Jurisdiction *</label>
                        <input type="text" name="regulator_jurisdiction" id="regulator_jurisdiction" value="{{ old('regulator_jurisdiction') }}" placeholder="e.g., USA" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="regulator_register_number" class="block text-sm font-medium text-gray-700">Regulator Register Number *</label>
                        <input type="text" name="regulator_register_number" id="regulator_register_number" value="{{ old('regulator_register_number') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                </div>

                <!-- Subsidiary field -->
                <div id="subsidiaryField" class="mb-6" style="display: none;">
                    <div>
                        <label for="parent_institution_name" class="block text-sm font-medium text-gray-700">Parent Institution Name *</label>
                        <input type="text" name="parent_institution_name" id="parent_institution_name" value="{{ old('parent_institution_name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label for="business_description" class="block text-sm font-medium text-gray-700">Business Description</label>
                <textarea name="business_description" id="business_description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('business_description') }}</textarea>
            </div>

            <h3 class="text-lg font-semibold text-gray-800 mb-4">Account Creation (Optional)</h3>
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="create_account" value="1" {{ old('create_account') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Create account and profile for this institution</span>
                </label>

                <div id="accountTypeField" class="mt-4" style="display: none;">
                    <label for="account_type" class="block text-sm font-medium text-gray-700">Account Type</label>
                    <select name="account_type" id="account_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="BROKERAGE" {{ old('account_type', 'BROKERAGE') == 'BROKERAGE' ? 'selected' : '' }}>Brokerage</option>
                        <option value="CUSTODY" {{ old('account_type') == 'CUSTODY' ? 'selected' : '' }}>Custody</option>
                        <option value="OTHER" {{ old('account_type') == 'OTHER' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
            </div>

            <!-- Document Upload Section -->
            <div class="mt-8 border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Documents (Optional)</h3>
                <p class="text-sm text-gray-600 mb-4">You can upload documents now or later. If documents are required, you'll be notified. <strong>Note:</strong> Institution members' IDs should be uploaded when creating each member.</p>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-blue-800"><strong>Required Documents for Institutions:</strong></p>
                    <ul class="text-sm text-blue-700 mt-2 list-disc list-inside space-y-1">
                        <li>Organizational Documents (Certificate of Incorporation, Articles of Incorporation, etc.)</li>
                        <li>Proof of Business Address (utility bill, bank statement - not older than 6 months)</li>
                        <li>Proof of Funds (bank statement showing ownership under institution's name)</li>
                        <li>Tax Identification Number/Registration Number (evidence from IRS or relevant body)</li>
                        <li>Certificate of Good Standing (required for institutions established longer than 1 year)</li>
                        <li>Valid ID for all Beneficial Owners, Authorized Users, and Management Control Persons (upload when creating members)</li>
                    </ul>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="proof_of_business" class="block text-sm font-medium text-gray-700">Organizational Documents *</label>
                        <p class="text-xs text-gray-500 mb-2">Certificate of Incorporation, Articles of Incorporation, Certificate of Formation, Articles of Organization, or government registry filings (PDF, JPG, PNG, max 10MB)</p>
                        <input type="file" name="proof_of_business" id="proof_of_business" accept=".pdf,.jpg,.jpeg,.png" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        @error('proof_of_business')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="proof_of_residency" class="block text-sm font-medium text-gray-700">Proof of Business Address *</label>
                        <p class="text-xs text-gray-500 mb-2">Utility bill, cell phone bill, or bank statement (not older than 6 months) (PDF, JPG, PNG, max 10MB)</p>
                        <input type="file" name="proof_of_residency" id="proof_of_residency" accept=".pdf,.jpg,.jpeg,.png" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        @error('proof_of_residency')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="proof_of_funds" class="block text-sm font-medium text-gray-700">Proof of Funds</label>
                        <p class="text-xs text-gray-500 mb-2">Bank statement showing ownership under institution's name (PDF, JPG, PNG, max 10MB)</p>
                        <input type="file" name="proof_of_funds" id="proof_of_funds" accept=".pdf,.jpg,.jpeg,.png" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        @error('proof_of_funds')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tax_id_document" class="block text-sm font-medium text-gray-700">Tax ID / Registration Number</label>
                        <p class="text-xs text-gray-500 mb-2">Evidence from IRS or relevant governing body (PDF, JPG, PNG, max 10MB)</p>
                        <input type="file" name="tax_id_document" id="tax_id_document" accept=".pdf,.jpg,.jpeg,.png" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        @error('tax_id_document')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="certificate_of_good_standing" class="block text-sm font-medium text-gray-700">Certificate of Good Standing</label>
                        <p class="text-xs text-gray-500 mb-2">Required for institutions established longer than 1 year (PDF, JPG, PNG, max 10MB)</p>
                        <input type="file" name="certificate_of_good_standing" id="certificate_of_good_standing" accept=".pdf,.jpg,.jpeg,.png" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        @error('certificate_of_good_standing')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <a href="{{ route('lender.identities.create', ['step' => 2, 'identity_type' => 'INSTITUTION']) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    Back
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                    Create Institution Identity
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelector('input[name="create_account"]').addEventListener('change', function() {
    document.getElementById('accountTypeField').style.display = this.checked ? 'block' : 'none';
});

// Show/hide fields based on regulation_status and trading_type
function updateConditionalFields() {
    const regulationStatus = document.getElementById('regulation_status').value;
    const tradingType = document.getElementById('trading_type').value;
    const conditionalFields = document.getElementById('conditionalFields');
    const publicTradingFields = document.getElementById('publicTradingFields');
    const regulatedFields = document.getElementById('regulatedFields');
    const subsidiaryField = document.getElementById('subsidiaryField');

    // Reset visibility
    conditionalFields.style.display = 'none';
    publicTradingFields.style.display = 'none';
    regulatedFields.style.display = 'none';
    subsidiaryField.style.display = 'none';

    // Show fields based on combination
    if (regulationStatus && tradingType) {
        conditionalFields.style.display = 'block';

        // Public trading fields (for PUBLIC or PUBLICLY_TRADED_SUBSIDIARY)
        if (tradingType === 'PUBLIC' || tradingType === 'PUBLICLY_TRADED_SUBSIDIARY') {
            publicTradingFields.style.display = 'grid';
        }

        // Regulated fields (for US_REGULATED or INTL_REGULATED)
        if (regulationStatus === 'US_REGULATED' || regulationStatus === 'INTL_REGULATED') {
            regulatedFields.style.display = 'grid';
        }

        // Subsidiary field
        if (tradingType === 'PUBLICLY_TRADED_SUBSIDIARY') {
            subsidiaryField.style.display = 'block';
        }

        // NON_REGULATED + PUBLIC only needs public trading fields
        if (regulationStatus === 'NON_REGULATED' && tradingType === 'PUBLIC') {
            publicTradingFields.style.display = 'grid';
        }
    }
}

document.getElementById('regulation_status').addEventListener('change', updateConditionalFields);
document.getElementById('trading_type').addEventListener('change', updateConditionalFields);

// Initialize on page load
updateConditionalFields();
</script>
@endsection
