@extends('layouts.app')

@section('title', 'Create Crypto Deposit Address')

@section('content')
<div class="py-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Create Crypto Deposit Address</h1>

    <div class="bg-white shadow rounded-lg p-6">
        <div class="mb-4 p-4 bg-blue-50 rounded-lg">
            <p class="text-sm text-blue-800">
                <strong>Profile:</strong> {{ $profile->paxos_profile_id }}
            </p>
            <p class="text-xs text-blue-700 mt-2">
                Deposits to this address credit this Paxos profile. For PYUSD on Stellar, Paxos handles trustlines; see
                <a href="https://docs.paxos.com" class="underline" target="_blank" rel="noopener">Paxos docs</a>.
            </p>
        </div>

        <form action="{{ route('lender.profiles.deposit-addresses.store', $profile) }}" method="POST">
            @csrf

            <div class="space-y-6">
                <div>
                    <label for="crypto_network" class="block text-sm font-medium text-gray-700">Blockchain network *</label>
                    <select name="crypto_network" id="crypto_network" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select network</option>
                        @foreach(\App\Models\DepositAddress::allowedCryptoNetworks() as $network)
                            <option value="{{ $network }}" {{ old('crypto_network') == $network ? 'selected' : '' }}>{{ $network }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="conversion_target_asset" class="block text-sm font-medium text-gray-700">Conversion target (optional)</label>
                    <select name="conversion_target_asset" id="conversion_target_asset" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Default (omit)</option>
                        <option value="NO_CONVERSION" {{ old('conversion_target_asset') == 'NO_CONVERSION' ? 'selected' : '' }}>NO_CONVERSION</option>
                        <option value="USD" {{ old('conversion_target_asset') == 'USD' ? 'selected' : '' }}>USD</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">For Paxos-minted USD stablecoin deposit crediting per API.</p>
                </div>

                <div>
                    <label for="ref_id" class="block text-sm font-medium text-gray-700">Reference ID (optional)</label>
                    <input type="text" name="ref_id" id="ref_id" value="{{ old('ref_id') }}" placeholder="Leave empty to auto-generate UUID" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500">Must be unique per Paxos; reusing a ref_id returns 409.</p>
                </div>

                <div>
                    <label for="metadata_label" class="block text-sm font-medium text-gray-700">Label (optional metadata)</label>
                    <input type="text" name="metadata_label" id="metadata_label" value="{{ old('metadata_label') }}" maxlength="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500">Stored as metadata on the deposit address in Paxos.</p>
                </div>
            </div>

            <div class="mt-8 flex justify-between">
                <a href="{{ route('lender.profiles.show', $profile) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Cancel</a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">Create deposit address</button>
            </div>
        </form>
    </div>
</div>
@endsection
