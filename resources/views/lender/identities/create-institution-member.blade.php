@extends('layouts.app')

@section('title', 'Add Institution Member')

@section('content')
<div class="py-6">
    <div class="mb-4">
        <div class="flex items-center text-sm text-gray-600 mb-2">
            <span class="bg-indigo-600 text-white rounded-full w-6 h-6 flex items-center justify-center mr-2">1</span>
            <span>Choose Type</span>
            <span class="mx-2">→</span>
            <span class="bg-indigo-600 text-white rounded-full w-6 h-6 flex items-center justify-center mr-2">2</span>
            <span class="font-semibold text-gray-900">Add Members</span>
            <span class="mx-2">→</span>
            <span class="bg-gray-300 text-gray-600 rounded-full w-6 h-6 flex items-center justify-center mr-2">3</span>
            <span>Institution Details</span>
        </div>
    </div>

    <h1 class="text-3xl font-bold text-gray-900 mb-6">Add Institution Member</h1>

    @if(count($members) > 0)
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <h3 class="font-semibold text-blue-900 mb-2">Members Added ({{ count($members) }})</h3>
        <ul class="list-disc list-inside text-blue-800">
            @foreach($members as $member)
            <li>{{ $member['name'] }} - Roles: {{ implode(', ', $member['roles']) }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route('lender.identities.store-member') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Member Roles *</label>
                <p class="text-xs text-gray-500 mb-3">Institution Type: <strong>{{ $institutionType ?? 'Not selected' }}</strong></p>
                <div class="space-y-2" id="rolesContainer">
                    @if(in_array($institutionType ?? '', ['CORPORATION', 'LLC', 'PARTNERSHIP', 'OTHER']))
                        <label class="flex items-center">
                            <input type="checkbox" name="roles[]" value="BENEFICIAL_OWNER" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Beneficial Owner (owns 25% or more)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="roles[]" value="AUTHORIZED_USER" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Authorized User</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="roles[]" value="MANAGEMENT_CONTROL_PERSON" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Management Control Person (CEO, CFO, COO, etc.)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="roles[]" value="ACCOUNT_OPENER" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Account Opener</span>
                        </label>
                    @elseif(($institutionType ?? '') == 'TRUST')
                        <label class="flex items-center">
                            <input type="checkbox" name="roles[]" value="TRUSTEE" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Trustee (manages assets for beneficiaries)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="roles[]" value="GRANTOR" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Grantor (creates the trust)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="roles[]" value="BENEFICIARY" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Beneficiary (receives benefits)</span>
                        </label>
                    @else
                        <p class="text-sm text-red-600">Please select an institution type first.</p>
                    @endif
                </div>
                @error('roles')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name *</label>
                    <input type="text" name="first_name" id="first_name" required value="{{ old('first_name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name *</label>
                    <input type="text" name="last_name" id="last_name" required value="{{ old('last_name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Date of Birth *</label>
                    <input type="date" name="date_of_birth" id="date_of_birth" required value="{{ old('date_of_birth') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="nationality" class="block text-sm font-medium text-gray-700">Nationality *</label>
                    <input type="text" name="nationality" id="nationality" required value="{{ old('nationality') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                    <input type="email" name="email" id="email" required value="{{ old('email') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="phone_number" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="cip_id" class="block text-sm font-medium text-gray-700">CIP ID (SSN)</label>
                    <input type="text" name="cip_id" id="cip_id" value="{{ old('cip_id') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="cip_id_type" class="block text-sm font-medium text-gray-700">CIP ID Type</label>
                    <select name="cip_id_type" id="cip_id_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="SSN" {{ old('cip_id_type') == 'SSN' ? 'selected' : '' }}>SSN</option>
                        <option value="PASSPORT" {{ old('cip_id_type') == 'PASSPORT' ? 'selected' : '' }}>Passport</option>
                    </select>
                </div>

                <div>
                    <label for="cip_id_country" class="block text-sm font-medium text-gray-700">CIP ID Country</label>
                    <input type="text" name="cip_id_country" id="cip_id_country" value="{{ old('cip_id_country', 'USA') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="address_country" class="block text-sm font-medium text-gray-700">Country *</label>
                    <input type="text" name="address_country" id="address_country" required value="{{ old('address_country') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="address1" class="block text-sm font-medium text-gray-700">Address Line 1 *</label>
                    <input type="text" name="address1" id="address1" required value="{{ old('address1') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700">City *</label>
                    <input type="text" name="city" id="city" required value="{{ old('city') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="province" class="block text-sm font-medium text-gray-700">Province/State</label>
                    <input type="text" name="province" id="province" value="{{ old('province') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div>
                    <label for="zip_code" class="block text-sm font-medium text-gray-700">Zip Code</label>
                    <input type="text" name="zip_code" id="zip_code" value="{{ old('zip_code') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
            </div>

            <!-- Document Upload Section -->
            <div class="mt-8 border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Valid ID (Required for Beneficial Owners, Authorized Users, and Management Control Persons)</h3>
                <p class="text-sm text-gray-600 mb-4">Upload a scanned color passport, driver's license, or national ID for this member.</p>
                
                <div>
                    <label for="proof_of_identity" class="block text-sm font-medium text-gray-700">Valid ID Document *</label>
                    <p class="text-xs text-gray-500 mb-2">Scanned color passport, driver's license, or national ID (PDF, JPG, PNG, max 10MB)</p>
                    <input type="file" name="proof_of_identity" id="proof_of_identity" accept=".pdf,.jpg,.jpeg,.png" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    @error('proof_of_identity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <a href="{{ route('lender.identities.create') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    Cancel
                </a>
                <div>
                    <button type="submit" name="action" value="add_another" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded mr-2">
                        Add Another Member
                    </button>
                    <button type="submit" name="action" value="continue" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                        Continue to Institution Details
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
