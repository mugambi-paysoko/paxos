@extends('layouts.app')

@section('title', 'Paxos Onboarding')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto">
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        Welcome! To get started with Paxos, we need to set up your identity, account, and profile. 
                        This will only take a few minutes.
                    </p>
                </div>
            </div>
        </div>

        <h1 class="text-3xl font-bold text-gray-900 mb-6">Paxos Onboarding</h1>

        <div class="bg-white shadow rounded-lg p-6">
            <form action="{{ route('lender.onboarding.store') }}" method="POST">
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

                <!-- Identity Section -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4 pb-2 border-b">Identity Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700">First Name *</label>
                            <input type="text" name="first_name" id="first_name" required value="{{ old('first_name', $user->name ? explode(' ', $user->name)[0] : '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name *</label>
                            <input type="text" name="last_name" id="last_name" required value="{{ old('last_name', $user->name ? (count(explode(' ', $user->name)) > 1 ? explode(' ', $user->name)[1] : '') : '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Date of Birth *</label>
                            <input type="date" name="date_of_birth" id="date_of_birth" required value="{{ old('date_of_birth') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="nationality" class="block text-sm font-medium text-gray-700">Nationality *</label>
                            <input type="text" name="nationality" id="nationality" required value="{{ old('nationality') }}" placeholder="e.g., USA" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                            <input type="email" name="email" id="email" required value="{{ old('email', $user->email) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
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
                    </div>

                    <div class="mt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Address Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="address_country" class="block text-sm font-medium text-gray-700">Country *</label>
                                <input type="text" name="address_country" id="address_country" required value="{{ old('address_country') }}" placeholder="e.g., USA" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
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
                    </div>
                </div>

                <!-- Account Section -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4 pb-2 border-b">Account Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="account_type" class="block text-sm font-medium text-gray-700">Account Type</label>
                            <select name="account_type" id="account_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="BROKERAGE" {{ old('account_type', 'BROKERAGE') == 'BROKERAGE' ? 'selected' : '' }}>Brokerage</option>
                                <option value="CUSTODY" {{ old('account_type') == 'CUSTODY' ? 'selected' : '' }}>Custody</option>
                                <option value="OTHER" {{ old('account_type') == 'OTHER' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <div>
                            <label for="account_description" class="block text-sm font-medium text-gray-700">Description</label>
                            <input type="text" name="account_description" id="account_description" value="{{ old('account_description') }}" placeholder="Optional description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="mt-4 bg-gray-50 p-4 rounded-md">
                        <p class="text-sm text-gray-600">
                            <strong>Note:</strong> A profile will be automatically created along with your account. 
                            This profile is required for funding operations in Paxos.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded">
                        Complete Onboarding
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
