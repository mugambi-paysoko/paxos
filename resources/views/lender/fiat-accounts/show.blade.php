@extends('layouts.app')

@section('title', 'Fiat Account Details')

@section('content')
<div class="py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Fiat Account Details</h1>
        <div class="flex gap-4">
            <form action="{{ route('lender.fiat-accounts.refresh', $fiatAccount) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Refresh Status
                </button>
            </form>
            <a href="{{ route('lender.fiat-accounts.index') }}" class="text-indigo-600 hover:text-indigo-500">
                ← Back to Fiat Accounts
            </a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:p-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Paxos Fiat Account ID</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $fiatAccount->paxos_fiat_account_id }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $fiatAccount->status === 'APPROVED' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $fiatAccount->status }}
                        </span>
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Identity</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $fiatAccount->identity->first_name }} {{ $fiatAccount->identity->last_name }}
                    </dd>
                </div>

                @if($fiatAccount->paxos_identity_id)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Paxos Identity ID</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $fiatAccount->paxos_identity_id }}</dd>
                </div>
                @endif

                @if($fiatAccount->paxos_account_id)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Paxos Account ID</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $fiatAccount->paxos_account_id }}</dd>
                </div>
                @endif

                @if($fiatAccount->paxos_created_at)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Created at (Paxos)</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $fiatAccount->paxos_created_at->format('Y-m-d H:i:s') }}</dd>
                </div>
                @endif

                <div>
                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $fiatAccount->created_at->format('Y-m-d H:i:s') }}</dd>
                </div>
            </dl>
        </div>

        @if($fiatAccount->fiat_account_owner)
        <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Account Owner</h3>
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                @if(isset($fiatAccount->fiat_account_owner['person_details']))
                    <div>
                        <dt class="text-sm font-medium text-gray-500">First Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $fiatAccount->fiat_account_owner['person_details']['first_name'] ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $fiatAccount->fiat_account_owner['person_details']['last_name'] ?? 'N/A' }}</dd>
                    </div>
                @endif
                @if(isset($fiatAccount->fiat_account_owner['institution_details']))
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Institution Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $fiatAccount->fiat_account_owner['institution_details']['name'] ?? 'N/A' }}</dd>
                    </div>
                @endif
            </dl>
        </div>
        @endif

        @if($fiatAccount->fiat_network_instructions)
        <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Network Instructions</h3>
            <div class="bg-gray-50 rounded-lg p-4">
                <pre class="text-sm text-gray-800 overflow-x-auto">{{ json_encode($fiatAccount->fiat_network_instructions, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
