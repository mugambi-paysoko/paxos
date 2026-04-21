@extends('layouts.app')

@section('title', 'Create Fiat Withdrawal')

@section('content')
<div class="py-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Create Fiat Withdrawal</h1>

    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route('lender.fiat-withdrawals.store') }}" method="POST">
            @csrf

            <div class="space-y-6">
                <div>
                    <label for="profile_id" class="block text-sm font-medium text-gray-700">Profile *</label>
                    <select name="profile_id" id="profile_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Select profile</option>
                        @foreach($profiles as $profile)
                            <option value="{{ $profile->id }}" {{ old('profile_id') == $profile->id ? 'selected' : '' }}>{{ $profile->paxos_profile_id }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="fiat_account_id" class="block text-sm font-medium text-gray-700">Destination Fiat Account *</label>
                    <select name="fiat_account_id" id="fiat_account_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Select fiat account</option>
                        @foreach($fiatAccounts as $fiatAccount)
                            <option value="{{ $fiatAccount->id }}" {{ old('fiat_account_id') == $fiatAccount->id ? 'selected' : '' }}>
                                {{ $fiatAccount->paxos_fiat_account_id }} ({{ $fiatAccount->identity->first_name }} {{ $fiatAccount->identity->last_name }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Only APPROVED fiat accounts are shown.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700">Amount (USD)</label>
                        <input type="number" step="0.01" min="0.01" name="amount" id="amount" value="{{ old('amount') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <p class="mt-1 text-sm text-gray-500">Specify either amount or total.</p>
                    </div>
                    <div>
                        <label for="total" class="block text-sm font-medium text-gray-700">Total incl. fees (USD)</label>
                        <input type="number" step="0.01" min="0.01" name="total" id="total" value="{{ old('total') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <p class="mt-1 text-sm text-gray-500">Specify either total or amount.</p>
                    </div>
                </div>

                <input type="hidden" name="asset" value="USD">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="ref_id" class="block text-sm font-medium text-gray-700">Reference ID (optional)</label>
                        <input type="text" name="ref_id" id="ref_id" value="{{ old('ref_id') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label for="metadata_label" class="block text-sm font-medium text-gray-700">Metadata Label (optional)</label>
                        <input type="text" name="metadata_label" id="metadata_label" maxlength="100" value="{{ old('metadata_label') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>

                <div>
                    <label for="memo" class="block text-sm font-medium text-gray-700">Memo (optional)</label>
                    <input type="text" name="memo" id="memo" maxlength="255" value="{{ old('memo') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('lender.fiat-withdrawals.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Cancel</a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">Create Fiat Withdrawal</button>
            </div>
        </form>
    </div>
</div>
@endsection
