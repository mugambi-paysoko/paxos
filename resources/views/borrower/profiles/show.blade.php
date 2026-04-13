@extends('layouts.app')

@section('title', 'Profile Details')

@section('content')
<div class="py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Profile Details</h1>
        <div class="flex gap-4">
            <a href="{{ route('borrower.profiles.fiat-deposit-instructions.create', $profile) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                Create Fiat Deposit Instruction
            </a>
            <a href="{{ route('borrower.profiles.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                Back
            </a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Profile Information</h3>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Paxos Profile ID</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $profile->paxos_profile_id ?? 'N/A' }}</dd>
                    </div>
                </dl>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Information</h3>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Account Type</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $profile->account->type }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Account Description</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $profile->account->description ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <a href="{{ route('borrower.accounts.show', $profile->account) }}" class="text-indigo-600 hover:text-indigo-500">
                            View Account Details →
                        </a>
                    </div>
                </dl>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Identity Information</h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $profile->account->identity->first_name }} {{ $profile->account->identity->last_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $profile->account->identity->email }}</dd>
                </div>
            </dl>
        </div>

        @if($profile->fiatDepositInstructions && $profile->fiatDepositInstructions->count() > 0)
        <div class="mt-6 pt-6 border-t border-gray-200">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Fiat Deposit Instructions</h3>
            </div>
            <ul class="divide-y divide-gray-200">
                @foreach($profile->fiatDepositInstructions as $instruction)
                <li class="py-3">
                    <a href="{{ route('borrower.fiat-deposit-instructions.show', $instruction) }}" class="block hover:bg-gray-50 -mx-4 px-4 py-2 rounded">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-indigo-600">
                                    {{ $instruction->fiat_network }} Deposit Instruction
                                </p>
                                @if($instruction->memo_id)
                                <p class="text-xs text-gray-500 mt-1">Memo ID: <span class="font-mono">{{ $instruction->memo_id }}</span></p>
                                @endif
                                @if($instruction->ref_id)
                                <p class="text-xs text-gray-500 mt-1">Ref ID: <span class="font-mono">{{ $instruction->ref_id }}</span></p>
                                @endif
                            </div>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $instruction->status === 'VALID' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $instruction->status ?? 'N/A' }}
                            </span>
                        </div>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="mt-6 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Transfers</h3>
            
            @if(isset($transfersError))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                {{ $transfersError }}
            </div>
            @endif

            @if(isset($transfers) && count($transfers) > 0)
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    @foreach($transfers as $transfer)
                    <li>
                        <div class="block">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <p class="text-sm font-medium text-indigo-600 truncate">
                                            {{ $transfer['asset'] ?? 'USD' }} {{ number_format($transfer['amount'] ?? 0, 2) }} 
                                            {{ isset($transfer['type']) && (str_contains($transfer['type'], 'DEPOSIT') || $transfer['type'] === 'DEPOSIT') ? 'Deposit' : 'Transfer' }}
                                        </p>
                                    </div>
                                    <div class="ml-2 flex-shrink-0 flex">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ ($transfer['status'] ?? 'PENDING') === 'COMPLETED' ? 'bg-green-100 text-green-800' : (($transfer['status'] ?? 'PENDING') === 'PENDING' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ $transfer['status'] ?? 'PENDING' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-2 sm:flex sm:justify-between">
                                    <div class="sm:flex">
                                        <p class="flex items-center text-sm text-gray-500">
                                            Type: {{ $transfer['type'] ?? 'N/A' }}
                                        </p>
                                        @if(isset($transfer['memo']) || isset($transfer['memo_id']))
                                        <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                                            Memo: <span class="font-mono ml-1">{{ $transfer['memo'] ?? $transfer['memo_id'] ?? 'N/A' }}</span>
                                        </p>
                                        @endif
                                        @if(isset($transfer['id']))
                                        <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                                            ID: <span class="font-mono ml-1 text-xs">{{ substr($transfer['id'], 0, 8) }}...</span>
                                        </p>
                                        @endif
                                    </div>
                                    <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                        @if(isset($transfer['created_at']))
                                        <p>
                                            Created {{ \Carbon\Carbon::parse($transfer['created_at'])->diffForHumans() }}
                                        </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
            @else
            <div class="bg-gray-50 rounded-lg p-8 text-center">
                <p class="text-gray-500">No transfers found for this profile.</p>
                <p class="text-sm text-gray-400 mt-2">Transfers will appear here after you initiate a deposit.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
