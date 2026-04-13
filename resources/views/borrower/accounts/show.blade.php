@extends('layouts.app')

@section('title', 'Account Details')

@section('content')
<div class="py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Account Details</h1>
        <a href="{{ route('borrower.accounts.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
            Back
        </a>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Information</h3>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Account Type</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $account->type }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $account->description ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Paxos Account ID</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $account->paxos_account_id ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Reference ID</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $account->ref_id }}</dd>
                    </div>
                </dl>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Identity Information</h3>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $account->identity->first_name }} {{ $account->identity->last_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $account->identity->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Verification Status</dt>
                        <dd class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $account->identity->id_verification_status === 'APPROVED' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $account->identity->id_verification_status }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        @if($account->profile)
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Profile</h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Paxos Profile ID</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $account->profile->paxos_profile_id ?? 'N/A' }}</dd>
                </div>
                <div>
                    <a href="{{ route('borrower.profiles.show', $account->profile) }}" class="text-indigo-600 hover:text-indigo-500">
                        View Profile Details →
                    </a>
                </div>
            </dl>
        </div>
        @endif
    </div>
</div>
@endsection
