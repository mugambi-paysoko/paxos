@extends('layouts.app')

@section('title', 'Create Fiat Account')

@section('content')
<div class="py-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Create Fiat Account</h1>

    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route('lender.fiat-accounts.store') }}" method="POST">
            @csrf

            @if ($errors->any())
                <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">There were some errors with your submission:</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="space-y-6">
                <div>
                    <label for="identity_id" class="block text-sm font-medium text-gray-700">Identity *</label>
                    <select name="identity_id" id="identity_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select an identity</option>
                        @foreach($identities as $identity)
                            <option value="{{ $identity->id }}" {{ old('identity_id') == $identity->id ? 'selected' : '' }}>
                                {{ $identity->first_name }} {{ $identity->last_name }} ({{ $identity->paxos_identity_id }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Select the identity to associate with this fiat account</p>
                </div>

                <div class="border-t pt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Account Owner Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700">First Name *</label>
                            <input type="text" name="first_name" id="first_name" required value="{{ old('first_name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name *</label>
                            <input type="text" name="last_name" id="last_name" required value="{{ old('last_name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>
                </div>

                <div class="border-t pt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Bank Account Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="account_number" class="block text-sm font-medium text-gray-700">Account Number *</label>
                            <input type="text" name="account_number" id="account_number" required value="{{ old('account_number') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="routing_number" class="block text-sm font-medium text-gray-700">Routing Number *</label>
                            <input type="text" name="routing_number" id="routing_number" required value="{{ old('routing_number') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="routing_number_type" class="block text-sm font-medium text-gray-700">Routing Number Type *</label>
                            <select name="routing_number_type" id="routing_number_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="ABA" {{ old('routing_number_type') == 'ABA' ? 'selected' : '' }}>ABA</option>
                                <option value="SWIFT" {{ old('routing_number_type') == 'SWIFT' ? 'selected' : '' }}>SWIFT</option>
                                <option value="OTHER" {{ old('routing_number_type') == 'OTHER' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <div>
                            <label for="bank_name" class="block text-sm font-medium text-gray-700">Bank Name *</label>
                            <input type="text" name="bank_name" id="bank_name" required value="{{ old('bank_name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>
                </div>

                <div class="border-t pt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Bank Address</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="bank_country" class="block text-sm font-medium text-gray-700">Country *</label>
                            <input type="text" name="bank_country" id="bank_country" required value="{{ old('bank_country', 'USA') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="bank_address1" class="block text-sm font-medium text-gray-700">Address Line 1</label>
                            <input type="text" name="bank_address1" id="bank_address1" value="{{ old('bank_address1') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="bank_city" class="block text-sm font-medium text-gray-700">City</label>
                            <input type="text" name="bank_city" id="bank_city" value="{{ old('bank_city') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="bank_province" class="block text-sm font-medium text-gray-700">Province/State</label>
                            <input type="text" name="bank_province" id="bank_province" value="{{ old('bank_province') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="bank_zip_code" class="block text-sm font-medium text-gray-700">Zip Code</label>
                            <input type="text" name="bank_zip_code" id="bank_zip_code" value="{{ old('bank_zip_code') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>
                </div>

                <div class="border-t pt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Account Owner Address</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="owner_address1" class="block text-sm font-medium text-gray-700">Address Line 1 *</label>
                            <input type="text" name="owner_address1" id="owner_address1" required value="{{ old('owner_address1') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="owner_city" class="block text-sm font-medium text-gray-700">City *</label>
                            <input type="text" name="owner_city" id="owner_city" required value="{{ old('owner_city') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="owner_country" class="block text-sm font-medium text-gray-700">Country *</label>
                            <input type="text" name="owner_country" id="owner_country" required value="{{ old('owner_country', 'USA') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="owner_province" class="block text-sm font-medium text-gray-700">Province/State</label>
                            <input type="text" name="owner_province" id="owner_province" value="{{ old('owner_province') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="owner_zip_code" class="block text-sm font-medium text-gray-700">Zip Code</label>
                            <input type="text" name="owner_zip_code" id="owner_zip_code" value="{{ old('owner_zip_code') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <a href="{{ route('lender.fiat-accounts.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-4">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                    Create Fiat Account
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
