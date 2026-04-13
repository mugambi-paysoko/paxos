@extends('layouts.app')

@section('title', 'Create Account')

@section('content')
<div class="py-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Create Account</h1>

    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route('lender.accounts.store') }}" method="POST">
            @csrf

            <div class="space-y-6">
                <div>
                    <label for="identity_id" class="block text-sm font-medium text-gray-700">Identity *</label>
                    <select name="identity_id" id="identity_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select an identity</option>
                        @foreach($identities as $identity)
                            <option value="{{ $identity->id }}" {{ old('identity_id') == $identity->id ? 'selected' : '' }}>
                                {{ $identity->first_name }} {{ $identity->last_name }} ({{ $identity->id_verification_status }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Only approved identities are shown</p>
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Account Type *</label>
                    <select name="type" id="type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="BROKERAGE" {{ old('type') == 'BROKERAGE' ? 'selected' : '' }}>Brokerage</option>
                        <option value="CUSTODY" {{ old('type') == 'CUSTODY' ? 'selected' : '' }}>Custody</option>
                        <option value="OTHER" {{ old('type') == 'OTHER' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="create_profile" value="1" {{ old('create_profile') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600">Create profile automatically</span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <a href="{{ route('lender.accounts.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-4">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                    Create Account
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
