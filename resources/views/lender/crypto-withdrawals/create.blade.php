@extends('layouts.app')

@section('title', 'Create Crypto Withdrawal')

@section('content')
<div class="py-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Create Crypto Withdrawal</h1>
    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route('lender.profiles.crypto-withdrawals.store', $profile) }}" method="POST">
            @csrf
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Asset *</label>
                        <input type="text" name="asset" required value="{{ old('asset') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="BTC, ETH, USDP...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Crypto Network *</label>
                        <select name="crypto_network" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @foreach(\App\Models\CryptoWithdrawal::allowedNetworks() as $network)
                            <option value="{{ $network }}" {{ old('crypto_network') === $network ? 'selected' : '' }}>{{ $network }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Destination Address *</label>
                    <input type="text" name="destination_address" required value="{{ old('destination_address') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm font-mono">
                    <p class="mt-1 text-sm text-gray-500">For STELLAR PYUSD withdrawals, ensure destination has trustline set up.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Amount</label>
                        <input type="number" step="0.00000001" min="0.00000001" name="amount" value="{{ old('amount') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total (incl. fees)</label>
                        <input type="number" step="0.00000001" min="0.00000001" name="total" value="{{ old('total') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Reference ID (optional)</label>
                        <input type="text" name="ref_id" value="{{ old('ref_id') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Memo (optional)</label>
                        <input type="text" name="memo" value="{{ old('memo') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Metadata Label (optional)</label>
                    <input type="text" name="metadata_label" maxlength="100" value="{{ old('metadata_label') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('lender.profiles.show', $profile) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Cancel</a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">Create Crypto Withdrawal</button>
            </div>
        </form>
    </div>
</div>
@endsection
