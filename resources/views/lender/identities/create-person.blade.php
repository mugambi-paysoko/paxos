@extends('layouts.app')

@section('title', 'Create Person Identity')

@section('content')
<div class="py-6">
    <div class="mb-4">
        <div class="flex items-center text-sm text-gray-600 mb-2">
            <span class="bg-indigo-600 text-white rounded-full w-6 h-6 flex items-center justify-center mr-2">1</span>
            <span>Choose Type</span>
            <span class="mx-2">→</span>
            <span class="bg-indigo-600 text-white rounded-full w-6 h-6 flex items-center justify-center mr-2">2</span>
            <span class="font-semibold text-gray-900">Person Details</span>
        </div>
    </div>

    <h1 class="text-3xl font-bold text-gray-900 mb-6">Create Person Identity</h1>

    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route('lender.identities.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

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
                <h3 class="text-lg font-medium text-gray-900 mb-4">Documents (Optional)</h3>
                <p class="text-sm text-gray-600 mb-4">You can upload documents now or later. If documents are required, you'll be notified.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="proof_of_identity" class="block text-sm font-medium text-gray-700">Proof of Identity</label>
                        <p class="text-xs text-gray-500 mb-2">Government-issued photo ID (PDF, JPG, PNG, max 10MB)</p>
                        <input type="file" name="proof_of_identity" id="proof_of_identity" accept=".pdf,.jpg,.jpeg,.png" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        @error('proof_of_identity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="proof_of_residency" class="block text-sm font-medium text-gray-700">Proof of Residency</label>
                        <p class="text-xs text-gray-500 mb-2">Utility bill, bank statement, etc. (PDF, JPG, PNG, max 10MB)</p>
                        <input type="file" name="proof_of_residency" id="proof_of_residency" accept=".pdf,.jpg,.jpeg,.png" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        @error('proof_of_residency')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="proof_of_ssn" class="block text-sm font-medium text-gray-700">Proof of SSN (Optional)</label>
                        <p class="text-xs text-gray-500 mb-2">SSN verification document (PDF, JPG, PNG, max 10MB)</p>
                        <input type="file" name="proof_of_ssn" id="proof_of_ssn" accept=".pdf,.jpg,.jpeg,.png" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        @error('proof_of_ssn')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <a href="{{ route('lender.identities.create') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-4">
                    Back
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                    Create Identity
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
